<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal SMTP mijoz (sof PHP, tashqi kutubxonasiz).
 * Lokal sinov uchun Mailpit/MailHog (127.0.0.1:1025) bilan ishlaydi; STARTTLS/SSL va AUTH LOGIN ni qo‘llaydi.
 */
final class Mailer
{
    /** @var resource|null */
    private $socket = null;

    /**
     * @throws \RuntimeException yuborib bo‘lmasa
     */
    public static function send(string $to, string $subject, string $body, ?string $replyTo = null): void
    {
        (new self())->deliver($to, $subject, $body, $replyTo);
    }

    private function deliver(string $to, string $subject, string $body, ?string $replyTo): void
    {
        $c = config('mail');
        foreach ([$to, $c['from_email'], $replyTo] as $addr) {
            if ($addr !== null && filter_var($addr, FILTER_VALIDATE_EMAIL) === false) {
                throw new \RuntimeException('Noto‘g‘ri pochta manzili.');
            }
        }
        $encryption = (string) ($c['encryption'] ?? '');
        $host = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $c['host'] . ':' . (int) $c['port'];
        $timeout = (int) ($c['timeout'] ?? 5);

        $socket = @stream_socket_client($host, $errno, $errstr, $timeout);
        if ($socket === false) {
            throw new \RuntimeException("SMTP serverga ulanib bo‘lmadi: {$errstr} ({$errno})");
        }
        $this->socket = $socket;
        stream_set_timeout($socket, $timeout);

        try {
            $this->expect([220]);
            $this->command('EHLO linguauz.local', [250]);
            if ($encryption === 'tls') {
                $this->command('STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('TLS shifrlashni yoqib bo‘lmadi.');
                }
                $this->command('EHLO linguauz.local', [250]);
            }
            if (!empty($c['username'])) {
                $this->command('AUTH LOGIN', [334]);
                $this->command(base64_encode((string) $c['username']), [334]);
                $this->command(base64_encode((string) $c['password']), [235], true);
            }
            $this->command('MAIL FROM:<' . $c['from_email'] . '>', [250]);
            $this->command('RCPT TO:<' . $to . '>', [250, 251]);
            $this->command('DATA', [354]);

            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . self::encode((string) $c['from_name']) . ' <' . $c['from_email'] . '>',
                'To: <' . $to . '>',
                'Subject: ' . self::encode($subject),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@linguauz.local>',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];
            if ($replyTo !== null) {
                $headers[] = 'Reply-To: <' . $replyTo . '>';
            }
            $data = implode("\r\n", $headers) . "\r\n\r\n" . rtrim(chunk_split(base64_encode($body), 76, "\r\n")) . "\r\n.";
            $this->command($data, [250]);
            $this->command('QUIT', [221]);
        } finally {
            fclose($socket);
            $this->socket = null;
        }
    }

    private static function encode(string $text): string
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    private function command(string $line, array $expected, bool $secret = false): string
    {
        fwrite($this->socket, $line . "\r\n");
        return $this->expect($expected, $secret ? 'AUTH' : strtok($line, ' :'));
    }

    private function expect(array $codes, string $context = 'CONNECT'): string
    {
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new \RuntimeException("SMTP {$context} bosqichida kutilmagan javob: " . trim(substr($response, 0, 120)));
        }
        return $response;
    }
}
