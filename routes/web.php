<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CekAlatCcController;
use App\Http\Controllers\CekHarianAlatController;
use App\Http\Controllers\CekHarianAlatRescueController;
use App\Http\Controllers\CekHarianUnitPemadamController;
use App\Http\Controllers\CekHarianUnitRescueController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PengajuanController;
use Illuminate\Support\Facades\Route;

// Redirect Halaman Utama ( / )
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('home');
});

// ===== Auth (Login/Logout) =====
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================================================================
//  USER ROUTES — Wajib Login (middleware: auth)
// =====================================================================
Route::middleware(['auth', 'user'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/home/index', [HomeController::class, 'index'])->name('home.index');

    // ===== Pemeliharaan > Pengajuan =====
    Route::get('/pemeliharaan/pengajuan', [PengajuanController::class, 'index'])
        ->name('pemeliharaan.pengajuan');
    Route::post('/pemeliharaan/pengajuan', [PengajuanController::class, 'store'])
        ->name('pemeliharaan.pengajuan.store');

    // ===== Unit Pemadam > Cek Harian Unit & Alat =====
    Route::get('/unit-pemadam/cek-harian-unit', [CekHarianUnitPemadamController::class, 'index'])
        ->name('unit-pemadam.cek-harian-unit');
    Route::post('/unit-pemadam/cek-harian-unit', [CekHarianUnitPemadamController::class, 'store'])
        ->name('unit-pemadam.cek-harian-unit.store');

    Route::get('/alat-pemadam/cek-harian-alat', [CekHarianAlatController::class, 'index'])
        ->name('alat-pemadam.cek-harian-alat');
    Route::post('/alat-pemadam/cek-harian-alat', [CekHarianAlatController::class, 'store'])
        ->name('alat-pemadam.cek-harian-alat.store');

    // ===== Unit Rescue > Cek Harian Unit & Alat =====
    Route::get('/unit-rescue/cek-harian-unit', [CekHarianUnitRescueController::class, 'index'])
        ->name('unit-rescue.cek-harian-unit-rescue');
    Route::post('/unit-rescue/cek-harian-unit', [CekHarianUnitRescueController::class, 'store'])
        ->name('unit-rescue.cek-harian-unit-rescue.store');

    Route::get('/alat-rescue/cek-harian-alat', [CekHarianAlatRescueController::class, 'index'])
        ->name('alat-rescue.cek-harian-alat');
    Route::post('/alat-rescue/cek-harian-alat', [CekHarianAlatRescueController::class, 'store'])
        ->name('alat-rescue.cek-harian-alat.store');

    // ===== Command Center > Cek Alat CC =====
    Route::get('/alat-cc/cek-alat-cc', [CekAlatCcController::class, 'index'])
        ->name('alat-cc.cek-alat-cc');
    Route::post('/alat-cc/cek-alat-cc', [CekAlatCcController::class, 'store'])
        ->name('alat-cc.cek-alat-cc.store');
});

// =====================================================================
//  ADMIN PANEL — Wajib Login & Admin (middleware: auth, admin)
// =====================================================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Pemeliharaan
    Route::prefix('pemeliharaan')->name('pemeliharaan.')->group(function () {
        Route::get('/pengajuan',    [AdminController::class, 'pemeliharaanPengajuan'])->name('pengajuan');
        Route::get('/pemeriksaan',  [AdminController::class, 'pemeliharaanPemeriksaan'])->name('pemeriksaan');
        Route::get('/pemeliharaan', [AdminController::class, 'pemeliharaanPemeliharaan'])->name('pemeliharaan');
        Route::get('/invoice',      [AdminController::class, 'pemeliharaanInvoice'])->name('invoice');
        Route::get('/kartu-kendali',[AdminController::class, 'pemeliharaanKartuKendali'])->name('kartu-kendali');
    });

    // Unit Pemadam
    Route::prefix('unit-pemadam')->name('unit-pemadam.')->group(function () {
        Route::get('/data-unit',   [AdminController::class, 'unitPemadamDataUnit'])->name('data-unit');
        Route::get('/pengecekan',  [AdminController::class, 'unitPemadamPengecekan'])->name('pengecekan');
        Route::get('/riwayat',     [AdminController::class, 'unitPemadamRiwayat'])->name('riwayat');
    });

    // Unit Rescue
    Route::prefix('unit-rescue')->name('unit-rescue.')->group(function () {
        Route::get('/data-unit',   [AdminController::class, 'unitRescueDataUnit'])->name('data-unit');
        Route::get('/pengecekan',  [AdminController::class, 'unitRescuePengecekan'])->name('pengecekan');
        Route::get('/riwayat',     [AdminController::class, 'unitRescueRiwayat'])->name('riwayat');
    });

    // Command Center
    Route::prefix('command-center')->name('command-center.')->group(function () {
        Route::get('/data-peralatan', [AdminController::class, 'commandCenterDataPeralatan'])->name('data-peralatan');
        Route::get('/pengecekan',     [AdminController::class, 'commandCenterPengecekan'])->name('pengecekan');
        Route::get('/riwayat',        [AdminController::class, 'commandCenterRiwayat'])->name('riwayat');
    });

    // APAR & Kejadian
    Route::prefix('apar')->name('apar.')->group(function () {
        Route::get('/data-apar',        [AdminController::class, 'aparDataApar'])->name('data-apar');
        Route::get('/laporan-kejadian', [AdminController::class, 'aparLaporanKejadian'])->name('laporan-kejadian');
        Route::get('/monitoring',       [AdminController::class, 'aparMonitoring'])->name('monitoring');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/pemeliharaan',   [AdminController::class, 'laporanPemeliharaan'])->name('pemeliharaan');
        Route::get('/pemadam',        [AdminController::class, 'laporanPemadam'])->name('pemadam');
        Route::get('/rescue',         [AdminController::class, 'laporanRescue'])->name('rescue');
        Route::get('/command-center', [AdminController::class, 'laporanCommandCenter'])->name('command-center');
        Route::get('/bulanan',        [AdminController::class, 'laporanBulanan'])->name('bulanan');
    });

    // Pengaturan
    Route::get('/pengaturan', [AdminController::class, 'pengaturan'])->name('pengaturan');

    // Lihat Halaman User (switch mode)
    Route::post('/switch-to-user', [AdminController::class, 'switchToUser'])->name('switch-to-user');
    Route::post('/switch-back-to-admin', [AdminController::class, 'switchBackToAdmin'])->name('switch-back-to-admin');

    // Redirect /admin → /admin/dashboard
    Route::redirect('/', '/admin/dashboard');
});
