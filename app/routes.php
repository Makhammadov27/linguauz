<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\CollectionController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\ExerciseController;
use App\Controllers\FileController;
use App\Controllers\HomeController;
use App\Controllers\ReviewController;
use App\Controllers\WordController;
use App\Core\Router;

$r = new Router();

// Ommaviy sahifalar
$r->get('/', [HomeController::class, 'index']);
$r->get('/about', [HomeController::class, 'about']);
$r->get('/contact', [ContactController::class, 'show']);
$r->post('/contact', [ContactController::class, 'send']);

// Autentifikatsiya
$r->get('/login', [AuthController::class, 'loginForm']);
$r->post('/login', [AuthController::class, 'login']);
$r->get('/register', [AuthController::class, 'registerForm']);
$r->post('/register', [AuthController::class, 'register']);
$r->post('/logout', [AuthController::class, 'logout']);

// Shaxsiy kabinet
$r->get('/dashboard', [DashboardController::class, 'index']);

// 1-bo‘lim: So‘zlar (to‘liq CRUD, qidiruv, filtr, saralash, sahifalash, CSV)
$r->get('/words', [WordController::class, 'index']);
$r->get('/words/export', [WordController::class, 'export']);
$r->get('/words/create', [WordController::class, 'create']);
$r->post('/words', [WordController::class, 'store']);
$r->get('/words/{id}', [WordController::class, 'show']);
$r->get('/words/{id}/edit', [WordController::class, 'edit']);
$r->post('/words/{id}', [WordController::class, 'update']);
$r->post('/words/{id}/delete', [WordController::class, 'destroy']);

// 2-bo‘lim: To‘plamlar (to‘liq CRUD + arxivlash + fayllar)
$r->get('/collections', [CollectionController::class, 'index']);
$r->get('/collections/create', [CollectionController::class, 'create']);
$r->post('/collections', [CollectionController::class, 'store']);
$r->get('/collections/{id}', [CollectionController::class, 'show']);
$r->get('/collections/{id}/edit', [CollectionController::class, 'edit']);
$r->post('/collections/{id}', [CollectionController::class, 'update']);
$r->post('/collections/{id}/delete', [CollectionController::class, 'destroy']);
$r->post('/collections/{id}/restore', [CollectionController::class, 'restore']);
$r->post('/collections/{id}/start', [CollectionController::class, 'start']);
$r->post('/collections/{id}/files', [FileController::class, 'upload']);

// 3-bo‘lim: Mashqlar
$r->get('/exercises', [ExerciseController::class, 'index']);
$r->get('/exercises/create', [ExerciseController::class, 'create']);
$r->post('/exercises', [ExerciseController::class, 'store']);
$r->get('/exercises/{id}', [ExerciseController::class, 'show']);
$r->get('/exercises/{id}/edit', [ExerciseController::class, 'edit']);
$r->post('/exercises/{id}', [ExerciseController::class, 'update']);
$r->post('/exercises/{id}/delete', [ExerciseController::class, 'destroy']);
$r->get('/exercises/{id}/play', [ExerciseController::class, 'play']);
$r->post('/exercises/{id}/submit', [ExerciseController::class, 'submit']);
$r->get('/attempts/{id}', [ExerciseController::class, 'attempt']);

// 4-bo‘lim: Takrorlash rejasi (asosiy funksional jarayon)
$r->get('/review', [ReviewController::class, 'plan']);
$r->get('/review/export', [ReviewController::class, 'export']);
$r->get('/review/session', [ReviewController::class, 'session']);
$r->post('/review/answer', [ReviewController::class, 'answer']);
$r->post('/review/{id}/remove', [ReviewController::class, 'remove']);

// Fayllar
$r->get('/files', [FileController::class, 'index']);
$r->get('/files/{id}', [FileController::class, 'download']);
$r->post('/files/{id}/replace', [FileController::class, 'replace']);
$r->post('/files/{id}/delete', [FileController::class, 'destroy']);

// Boshqaruv paneli (faqat administrator)
$r->get('/admin', [AdminController::class, 'index']);
$r->get('/admin/users', [AdminController::class, 'users']);
$r->get('/admin/users/create', [AdminController::class, 'createUser']);
$r->post('/admin/users', [AdminController::class, 'storeUser']);
$r->get('/admin/users/{id}/edit', [AdminController::class, 'editUser']);
$r->post('/admin/users/{id}', [AdminController::class, 'updateUser']);
$r->post('/admin/users/{id}/toggle', [AdminController::class, 'toggleUser']);
$r->get('/admin/languages', [AdminController::class, 'languages']);
$r->post('/admin/languages', [AdminController::class, 'storeLanguage']);
$r->post('/admin/languages/{id}', [AdminController::class, 'updateLanguage']);
$r->post('/admin/languages/{id}/delete', [AdminController::class, 'deleteLanguage']);
$r->get('/admin/messages', [AdminController::class, 'messages']);
$r->post('/admin/messages/{id}/resend', [AdminController::class, 'resendMessage']);
$r->post('/admin/messages/{id}/delete', [AdminController::class, 'deleteMessage']);

return $r;
