<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Policy;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\Collection;
use App\Models\File;

final class FileController extends Controller
{
    /** Fayllarni nomi yoki tavsifi bo‘yicha qidirish */
    public function index(): void
    {
        $user = Auth::user();
        $q = mb_substr($this->query('q'), 0, 100);
        $category = $this->query('category');
        $this->view('files/index', [
            'title'    => 'O‘quv materiallari',
            'description' => 'To‘plamlarga biriktirilgan rasmlar, PDF qo‘llanmalar va video darslar.',
            'q'        => $q,
            'category' => $category,
            'result'   => File::search($q, $category, $user, $this->page()),
            'user'     => $user,
        ]);
    }

    public function upload(int $collectionId): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $c = Collection::find($collectionId);
        if (!$c || !Policy::canViewCollection($c, $user)) {
            abort(404);
        }
        if (!Policy::canEditCollection($c, $user)) {
            abort(403, 'Faqat to‘plam egasi fayl biriktira oladi.');
        }
        $description = mb_substr($this->input('description'), 0, 255);
        $meta = Uploader::validate($_FILES['file'] ?? null);
        if (!$meta['ok']) {
            Session::flash('error', 'Fayl qabul qilinmadi: ' . $meta['error']);
            $this->redirect('/collections/' . $collectionId);
        }
        $stored = Uploader::store($_FILES['file']['tmp_name'], $meta['ext']);
        try {
            File::create($collectionId, (int) $user['id'], $meta, $stored, $description);
        } catch (\Throwable $e) {
            Uploader::remove($stored); // bazaga yozilmasa diskda ortiqcha fayl qolmaydi
            throw $e;
        }
        Session::flash('success', '«' . $meta['original'] . '» fayli yuklandi.');
        $this->redirect('/collections/' . $collectionId);
    }

    /** Faylni almashtirish (yangi fayl ixtiyoriy) va tavsifni o‘zgartirish */
    public function replace(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $file = $this->editable($id, $user);
        $description = mb_substr($this->input('description'), 0, 255);

        $upload = $_FILES['file'] ?? null;
        if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            File::updateDescription($id, $description);
            Session::flash('success', 'Fayl tavsifi yangilandi.');
            $this->redirect('/collections/' . $file['collection_id']);
        }
        $meta = Uploader::validate($upload);
        if (!$meta['ok']) {
            Session::flash('error', 'Fayl almashtirilmadi: ' . $meta['error']);
            $this->redirect('/collections/' . $file['collection_id']);
        }
        $stored = Uploader::store($upload['tmp_name'], $meta['ext']);
        try {
            Database::transaction(static function () use ($id, $user, $meta, $stored, $description): void {
                File::replace($id, (int) $user['id'], $meta, $stored, $description);
            });
        } catch (\Throwable $e) {
            Uploader::remove($stored);
            throw $e;
        }
        Uploader::remove($file['stored_name']); // baza yangilangach eski fayl diskdan o‘chiriladi
        Session::flash('success', 'Fayl almashtirildi: «' . $meta['original'] . '».');
        $this->redirect('/collections/' . $file['collection_id']);
    }

    public function destroy(int $id): void
    {
        $user = $this->requireAuth();
        $this->verifyCsrf();
        $file = $this->editable($id, $user);
        File::delete($id);
        Uploader::remove($file['stored_name']);
        Session::flash('success', '«' . $file['original_name'] . '» fayli olib tashlandi.');
        $this->redirect('/collections/' . $file['collection_id']);
    }

    /** Himoyalangan faylni berish: avval huquq tekshiriladi. Video uchun Range so‘rovlari qo‘llab-quvvatlanadi. */
    public function download(int $id): void
    {
        $user = Auth::user();
        $file = File::find($id);
        if (!$file || !Policy::canViewCollection($file, $user)) {
            abort(404);
        }
        $path = Uploader::path($file['stored_name']);
        if (!is_file($path)) {
            abort(404, 'Fayl diskda topilmadi.');
        }
        $size = (int) filesize($path);
        $start = 0;
        $end = $size - 1;

        header('Content-Type: ' . $file['mime']);
        header('X-Content-Type-Options: nosniff');
        header('Accept-Ranges: bytes');
        header('Cache-Control: private, max-age=3600');
        $disposition = $this->query('download') === '1' ? 'attachment' : 'inline';
        $ascii = preg_replace('/[^A-Za-z0-9._-]/', '_', $file['original_name']);
        header("Content-Disposition: $disposition; filename=\"$ascii\"; filename*=UTF-8''" . rawurlencode($file['original_name']));

        if (isset($_SERVER['HTTP_RANGE']) && preg_match('/^bytes=(\d*)-(\d*)$/', (string) $_SERVER['HTTP_RANGE'], $m)) {
            if ($m[1] === '' && $m[2] !== '') {
                $start = max(0, $size - (int) $m[2]);
            } else {
                $start = (int) $m[1];
                $end = $m[2] !== '' ? min((int) $m[2], $size - 1) : $size - 1;
            }
            if ($start > $end || $start >= $size) {
                http_response_code(416);
                header("Content-Range: bytes */$size");
                exit;
            }
            http_response_code(206);
            header("Content-Range: bytes $start-$end/$size");
        }
        $length = $end - $start + 1;
        header('Content-Length: ' . $length);

        $fp = fopen($path, 'rb');
        fseek($fp, $start);
        $remaining = $length;
        while ($remaining > 0 && !feof($fp)) {
            $chunk = fread($fp, min(8192, $remaining));
            if ($chunk === false) {
                break;
            }
            echo $chunk;
            $remaining -= strlen($chunk);
        }
        fclose($fp);
        exit;
    }

    private function editable(int $id, array $user): array
    {
        $file = File::find($id);
        if (!$file || !Policy::canViewCollection($file, $user)) {
            abort(404);
        }
        if (!Policy::canEditCollection($file, $user)) {
            abort(403, 'Faylni faqat to‘plam egasi yoki administrator o‘zgartira oladi.');
        }
        return $file;
    }
}
