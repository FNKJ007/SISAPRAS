<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\CekHarianUnitPemadamController;
use App\Http\Controllers\CekHarianAlatController;
use App\Http\Controllers\CekHarianAlatRescueController;



// ===== Auth (Login/Logout) =====
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index'])->name('home.index');

// ===== Pemeliharaan > Pengajuan =====
Route::get('/pemeliharaan/pengajuan', [PengajuanController::class, 'index'])
    ->name('pemeliharaan.pengajuan');

Route::post('/pemeliharaan/pengajuan', [PengajuanController::class, 'store'])
    ->name('pemeliharaan.pengajuan.store');

// ===== Unit Pemadam > Cek Harian Unit =====
Route::get('/unit-pemadam/cek-harian-unit', [CekHarianUnitPemadamController::class, 'index'])
    ->name('unit-pemadam.cek-harian-unit');

Route::post('/unit-pemadam/cek-harian-unit', [CekHarianUnitPemadamController::class, 'store'])
    ->name('unit-pemadam.cek-harian-unit.store');

Route::get('/alat-pemadam/cek-harian-alat', [CekHarianAlatController::class, 'index'])
    ->name('alat-pemadam.cek-harian-alat');

Route::post('/alat-pemadam/cek-harian-alat', [CekHarianAlatController::class, 'store'])
    ->name('alat-pemadam.cek-harian-alat.store');

Route::get('/alat-rescue/cek-harian-alat', [CekHarianAlatRescueController::class, 'index'])
    ->name('alat-rescue.cek-harian-alat');

Route::post('/alat-rescue/cek-harian-alat', [CekHarianAlatRescueController::class, 'store'])
    ->name('alat-rescue.cek-harian-alat.store');
