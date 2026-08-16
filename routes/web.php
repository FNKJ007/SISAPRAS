<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PeralatanManagementController;
use App\Http\Controllers\Admin\PosManagementController;
use App\Http\Controllers\Admin\UnitManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CekAlatCcController;
use App\Http\Controllers\CekHarianAlatController;
use App\Http\Controllers\CekHarianAlatRescueController;
use App\Http\Controllers\CekHarianUnitPemadamController;
use App\Http\Controllers\CekHarianUnitRescueController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MonitoringKejadianController;
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

    // Akses Halaman User sebagai Admin (set session flag)
    Route::get('/view-as-user/{target}', function ($target) {
        session(['admin_viewing_as_user' => true]);
        $routes = [
            'pengajuan' => 'pemeliharaan.pengajuan',
            'home'      => 'home',
        ];
        return redirect()->route($routes[$target] ?? 'home');
    })->name('view-as-user');

    // Pemeliharaan
    Route::prefix('pemeliharaan')->name('pemeliharaan.')->group(function () {
        Route::get('/pengajuan',                    [AdminController::class, 'pemeliharaanPengajuan'])->name('pengajuan');
        Route::post('/pengajuan/{id}/verifikasi',   [AdminController::class, 'verifikasiPengajuan'])->name('pengajuan.verifikasi');
        Route::get('/pemeriksaan',                  [AdminController::class, 'pemeliharaanPemeriksaan'])->name('pemeriksaan');
        Route::get('/pemeliharaan',                 [AdminController::class, 'pemeliharaanPemeliharaan'])->name('pemeliharaan');
        Route::get('/surat-permohonan',             [AdminController::class, 'pemeliharaanPemeliharaan'])->name('surat-permohonan');
        Route::get('/monitoring-aktual',            [AdminController::class, 'pemeliharaanMonitoringAktual'])->name('monitoring-aktual');
        Route::post('/monitoring-aktual/{id}/progres', [AdminController::class, 'updateProgresPengerjaan'])->name('monitoring-aktual.progres');
        Route::get('/cetak-dokumen/{id}/{type}',    [AdminController::class, 'cetakDokumen'])->name('cetak-dokumen');
        Route::resource('invoice', InvoiceController::class);
        Route::get('/kartu-kendali-aktual',                [AdminController::class, 'pemeliharaanKartuKendali'])->name('kartu-kendali-aktual');
        Route::get('/kartu-kendali-pembayaran',                [AdminController::class, 'pemeliharaanKartuKendaliPembayaran'])->name('kartu-kendali-pembayaran');

        // Data Unit CRUD Routes
        Route::get('/data-unit',                    [UnitManagementController::class, 'index'])->name('data-unit');
        Route::get('/data-unit/{id}/riwayat-servis', [UnitManagementController::class, 'riwayatServis'])->name('data-unit.riwayat-servis');
        Route::get('/data-unit/{id}/cetak-buku-servis', [UnitManagementController::class, 'cetakBukuServis'])->name('data-unit.cetak-buku-servis');
        Route::post('/data-unit',                   [UnitManagementController::class, 'store'])->name('data-unit.store');
        Route::put('/data-unit/{id}',               [UnitManagementController::class, 'update'])->name('data-unit.update');
        Route::delete('/data-unit/{id}',            [UnitManagementController::class, 'destroy'])->name('data-unit.destroy');
        Route::post('/data-unit/remove-history-option', [UnitManagementController::class, 'removeHistoryOption'])->name('data-unit.remove-history-option');

        // Data Peralatan CRUD Routes
        Route::get('/data-peralatan',              [PeralatanManagementController::class, 'index'])->name('data-peralatan');
        Route::post('/data-peralatan',             [PeralatanManagementController::class, 'store'])->name('data-peralatan.store');
        Route::put('/data-peralatan/{id}',         [PeralatanManagementController::class, 'update'])->name('data-peralatan.update');
        Route::delete('/data-peralatan/{id}',      [PeralatanManagementController::class, 'destroy'])->name('data-peralatan.destroy');
        Route::post('/data-peralatan/remove-history-option', [PeralatanManagementController::class, 'removeHistoryOption'])->name('data-peralatan.remove-history-option');

        // Data Pos CRUD Routes
        Route::get('/data-pos',                    [PosManagementController::class, 'index'])->name('data-pos');
        Route::post('/data-pos',                   [PosManagementController::class, 'store'])->name('data-pos.store');
        Route::put('/data-pos/{id}',               [PosManagementController::class, 'update'])->name('data-pos.update');
        Route::delete('/data-pos/{id}',            [PosManagementController::class, 'destroy'])->name('data-pos.destroy');
    });

    // Unit Pemadam
    Route::prefix('unit-pemadam')->name('unit-pemadam.')->group(function () {
        Route::get('/pengecekan',  [AdminController::class, 'unitPemadamPengecekan'])->name('pengecekan');
        Route::get('/riwayat',     [AdminController::class, 'unitPemadamRiwayat'])->name('riwayat');
    });

    // Unit Rescue
    Route::prefix('unit-rescue')->name('unit-rescue.')->group(function () {
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
        // Monitoring Kejadian CRUD Routes
        Route::get('/monitoring-kejadian',          [MonitoringKejadianController::class, 'index'])->name('monitoring-kejadian');
        Route::post('/monitoring-kejadian',         [MonitoringKejadianController::class, 'store'])->name('monitoring-kejadian.store');
        Route::put('/monitoring-kejadian/{id}',     [MonitoringKejadianController::class, 'update'])->name('monitoring-kejadian.update');
        Route::delete('/monitoring-kejadian/{id}',  [MonitoringKejadianController::class, 'destroy'])->name('monitoring-kejadian.destroy');
    });

    // Laporan
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/pemeliharaan',   [AdminController::class, 'laporanPemeliharaan'])->name('pemeliharaan');
        Route::get('/pemadam',        [AdminController::class, 'laporanPemadam'])->name('pemadam');
        Route::get('/rescue',         [AdminController::class, 'laporanRescue'])->name('rescue');
        Route::get('/command-center', [AdminController::class, 'laporanCommandCenter'])->name('command-center');
        Route::get('/bulanan',        [AdminController::class, 'laporanBulanan'])->name('bulanan');
    });

    // Pengaturan & Manajemen Akun
    Route::get('/pengaturan', [AdminController::class, 'pengaturan'])->name('pengaturan');
    Route::post('/pengaturan/users', [UserManagementController::class, 'store'])->name('pengaturan.users.store');
    Route::put('/pengaturan/users/{id}', [UserManagementController::class, 'update'])->name('pengaturan.users.update');
    Route::post('/pengaturan/users/{id}/reset-password', [UserManagementController::class, 'resetPassword'])->name('pengaturan.users.reset-password');
    Route::patch('/pengaturan/users/{id}/status', [UserManagementController::class, 'toggleStatus'])->name('pengaturan.users.toggle-status');
    Route::delete('/pengaturan/users/{id}', [UserManagementController::class, 'destroy'])->name('pengaturan.users.destroy');
    Route::post('/pengaturan/remove-history-option', [UserManagementController::class, 'removeHistoryOption'])->name('pengaturan.remove-history-option');

    // Lihat Halaman User (switch mode)
    Route::post('/switch-to-user', [AdminController::class, 'switchToUser'])->name('switch-to-user');
    Route::post('/switch-back-to-admin', [AdminController::class, 'switchBackToAdmin'])->name('switch-back-to-admin');

    // Redirect /admin → /admin/dashboard
    Route::redirect('/', '/admin/dashboard');
});
