<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PengajuanController;

// Route khusus untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Route yang hanya bisa diakses setelah login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard'); // Ganti dengan controller/view dashboard kamu
    })->name('dashboard');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home.index');

// ===== Pemeliharaan > Pengajuan =====
Route::get('/pemeliharaan/pengajuan', [PengajuanController::class, 'index'])
    ->name('pemeliharaan.pengajuan');

Route::post('/pemeliharaan/pengajuan', [PengajuanController::class, 'store'])
    ->name('pemeliharaan.pengajuan.store');
