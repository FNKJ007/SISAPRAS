<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /* ==================== DASHBOARD ==================== */
    public function dashboard()
    {
        // Ganti dengan query Model asli saat model sudah tersedia
        // Contoh:
        // $totalUnit         = \App\Models\Unit::count();
        // $totalPemeliharaan = \App\Models\Pengajuan::whereMonth('created_at', now()->month)->count();
        // $totalPemeriksaan  = \App\Models\Pemeriksaan::whereMonth('created_at', now()->month)->count();

        return view('admin.dashboard', [
            'totalUnit'         => 12,
            'totalPemeliharaan' => 5,
            'totalPemeriksaan'  => 3,
        ]);
    }

    /* ==================== PEMELIHARAAN ==================== */
    public function pemeliharaanPengajuan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pengajuan',
            'breadcrumb' => ['Pemeliharaan', 'Pengajuan'],
        ]);
    }

    public function pemeliharaanPemeriksaan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pemeriksaan',
            'breadcrumb' => ['Pemeliharaan', 'Pemeriksaan'],
        ]);
    }

    public function pemeliharaanPemeliharaan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pemeliharaan',
            'breadcrumb' => ['Pemeliharaan', 'Pemeliharaan'],
        ]);
    }

    public function pemeliharaanInvoice()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Invoice',
            'breadcrumb' => ['Pemeliharaan', 'Invoice'],
        ]);
    }

    public function pemeliharaanKartuKendali()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Kartu Kendali',
            'breadcrumb' => ['Pemeliharaan', 'Kartu Kendali'],
        ]);
    }

    /* ==================== UNIT PEMADAM ==================== */
    public function unitPemadamDataUnit()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Data Unit',
            'breadcrumb' => ['Unit Pemadam', 'Data Unit'],
        ]);
    }

    public function unitPemadamPengecekan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pengecekan',
            'breadcrumb' => ['Unit Pemadam', 'Pengecekan'],
        ]);
    }

    public function unitPemadamRiwayat()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Riwayat',
            'breadcrumb' => ['Unit Pemadam', 'Riwayat'],
        ]);
    }

    /* ==================== UNIT RESCUE ==================== */
    public function unitRescueDataUnit()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Data Unit',
            'breadcrumb' => ['Unit Rescue', 'Data Unit'],
        ]);
    }

    public function unitRescuePengecekan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pengecekan',
            'breadcrumb' => ['Unit Rescue', 'Pengecekan'],
        ]);
    }

    public function unitRescueRiwayat()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Riwayat',
            'breadcrumb' => ['Unit Rescue', 'Riwayat'],
        ]);
    }

    /* ==================== COMMAND CENTER ==================== */
    public function commandCenterDataPeralatan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Data Peralatan',
            'breadcrumb' => ['Command Center', 'Data Peralatan'],
        ]);
    }

    public function commandCenterPengecekan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pengecekan',
            'breadcrumb' => ['Command Center', 'Pengecekan'],
        ]);
    }

    public function commandCenterRiwayat()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Riwayat',
            'breadcrumb' => ['Command Center', 'Riwayat'],
        ]);
    }

    /* ==================== APAR & KEJADIAN ==================== */
    public function aparDataApar()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Data APAR',
            'breadcrumb' => ['APAR & Kejadian', 'Data APAR'],
        ]);
    }

    public function aparLaporanKejadian()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Kejadian',
            'breadcrumb' => ['APAR & Kejadian', 'Laporan Kejadian'],
        ]);
    }

    public function aparMonitoring()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Monitoring Kejadian',
            'breadcrumb' => ['APAR & Kejadian', 'Monitoring Kejadian'],
        ]);
    }

    /* ==================== LAPORAN ==================== */
    public function laporanPemeliharaan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Pemeliharaan',
            'breadcrumb' => ['Laporan', 'Pemeliharaan'],
        ]);
    }

    public function laporanPemadam()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Pemadam',
            'breadcrumb' => ['Laporan', 'Pemadam'],
        ]);
    }

    public function laporanRescue()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Rescue',
            'breadcrumb' => ['Laporan', 'Rescue'],
        ]);
    }

    public function laporanCommandCenter()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Command Center',
            'breadcrumb' => ['Laporan', 'Command Center'],
        ]);
    }

    public function laporanBulanan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Bulanan',
            'breadcrumb' => ['Laporan', 'Laporan Bulanan'],
        ]);
    }

    /* ==================== PENGATURAN ==================== */
    public function pengaturan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Pengaturan',
            'breadcrumb' => ['Pengaturan'],
        ]);
    }
}
