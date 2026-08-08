<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CekHarianAlat;
use App\Models\CekHarianUnit;
use App\Models\Pengajuan;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Dashboard Utama Admin
     */
    public function dashboard()
    {
        $kpi = [
            'total_pengajuan'  => Pengajuan::count(),
            'menunggu'         => Pengajuan::where('status', 'menunggu')->count(),
            'disetujui'        => Pengajuan::where('status', 'disetujui')->count(),
            'ditolak'          => Pengajuan::where('status', 'ditolak')->count(),
        ];

        $pengajuanTerbaru = Pengajuan::latest()->take(5)->get();

        return view('admin.dashboard', compact('kpi', 'pengajuanTerbaru'));
    }

    /**
     * Halaman Verifikasi Pengajuan Pemeliharaan Unit
     */
    public function pemeliharaanPengajuan(Request $request)
    {
        $statusFilter = $request->query('status', 'semua');
        $searchQuery  = $request->query('search', '');

        $query = Pengajuan::latest();

        // Filter berdasarkan status
        if ($statusFilter !== 'semua' && in_array($statusFilter, ['menunggu', 'disetujui', 'ditolak'])) {
            $query->where('status', $statusFilter);
        }

        // Pencarian berdasarkan nomor_lambung, nama_pemegang, atau pos
        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_lambung', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'LIKE', "%{$searchQuery}%");
            });
        }

        $pengajuanList = $query->paginate(10)->withQueryString();

        // Ringkasan KPI
        $kpi = [
            'total'     => Pengajuan::count(),
            'menunggu'  => Pengajuan::where('status', 'menunggu')->count(),
            'disetujui' => Pengajuan::where('status', 'disetujui')->count(),
            'ditolak'   => Pengajuan::where('status', 'ditolak')->count(),
        ];

        return view('admin.pemeliharaan.pengajuan', compact('pengajuanList', 'kpi', 'statusFilter', 'searchQuery'));
    }

    /**
     * Memverifikasi pengajuan (Setujui / Tolak) oleh Admin + Verifikasi per item
     */
    public function verifikasiPengajuan(Request $request, $id)
    {
        $request->validate([
            'status'                => 'required|in:disetujui,ditolak,menunggu',
            'tanggal_keberangkatan' => 'nullable|date',
            'catatan_admin'         => 'nullable|string|max:500',
            'item_verifikasis'      => 'nullable|array',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);

        $itemVerifikasis = $request->input('item_verifikasis', []);
        
        // Simpan keputusan per item
        $pengajuan->item_verifikasis = $itemVerifikasis;

        // Tentukan status keseluruhan berdasarkan verifikasi item jika ada
        if (!empty($itemVerifikasis)) {
            $hasDisetujui = in_array('disetujui', $itemVerifikasis, true);
            $hasDitolak   = in_array('ditolak', $itemVerifikasis, true);

            if ($hasDisetujui && !$hasDitolak) {
                $pengajuan->status = 'disetujui';
            } elseif ($hasDitolak && !$hasDisetujui) {
                $pengajuan->status = 'ditolak';
            } else {
                // Ada item yang disetujui dan ada yang ditolak -> Tetap disetujui (sebagian disetujui untuk perbaikan)
                $pengajuan->status = $request->status ?? 'disetujui';
            }
        } else {
            $pengajuan->status = $request->status;
        }

        $pengajuan->catatan_admin = $request->catatan_admin;

        if ($pengajuan->status === 'disetujui' && $request->filled('tanggal_keberangkatan')) {
            $pengajuan->tanggal_keberangkatan = $request->tanggal_keberangkatan;
        } elseif ($pengajuan->status !== 'disetujui') {
            $pengajuan->tanggal_keberangkatan = null;
        }

        $pengajuan->save();

        $statusText = match ($pengajuan->status) {
            'disetujui' => 'disetujui' . ($pengajuan->tanggal_keberangkatan ? ' (Jadwal: ' . $pengajuan->tanggal_keberangkatan->format('d/m/Y') . ')' : ''),
            'ditolak'   => 'ditolak',
            default     => 'diperbarui',
        };

        return redirect()
            ->route('admin.pemeliharaan.pengajuan')
            ->with('success', "Pengajuan unit {$pengajuan->nomor_lambung} berhasil {$statusText}.");
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

    public function unitPemadamPengecekan(Request $request)
    {
        $tab = $request->query('tab', 'unit'); // 'unit' atau 'alat'

        // ===== Hasil Cek Harian Unit Kendaraan =====
        $cekUnitList = CekHarianUnit::latest()
            ->paginate(10, ['*'], 'unit_page')
            ->withQueryString();

        // ===== Hasil Cek Harian Alat Pemadam =====
        $cekAlatList = CekHarianAlat::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })
            ->latest()
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Ringkasan KPI =====
        $kpi = [
            'total_cek_unit'   => CekHarianUnit::count(),
            'unit_ada_rusak'   => CekHarianUnit::where('jumlah_rusak', '>', 0)->count(),
            'total_cek_alat'   => CekHarianAlat::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->count(),
            'alat_rusak_total' => (int) CekHarianAlat::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->sum('total_rusak'),
        ];

        return view('admin.unit-pemadam.pengecekan', compact('cekUnitList', 'cekAlatList', 'kpi', 'tab'));
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
        $cekAlatList = CekHarianAlat::where('kategori', 'command_center')
            ->latest()
            ->get();

        $kpi = [
            'total_cek_cc'   => $cekAlatList->count(),
            'total_baik_cc'  => (int) $cekAlatList->sum('total_baik'),
            'total_rusak_cc' => (int) $cekAlatList->sum('total_rusak'),
        ];

        return view('admin.command-center.pengecekan', compact('cekAlatList', 'kpi'));
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

    public function aparMonitoring()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Monitoring',
            'breadcrumb' => ['APAR & Kejadian', 'Monitoring'],
        ]);
    }

    public function aparLaporanKejadian()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Kejadian',
            'breadcrumb' => ['APAR & Kejadian', 'Laporan Kejadian'],
        ]);
    }

    /* ==================== LAPORAN ==================== */
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

    public function laporanPemeliharaan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Pemeliharaan',
            'breadcrumb' => ['Laporan', 'Pemeliharaan'],
        ]);
    }

    public function laporanBulanan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Laporan Bulanan',
            'breadcrumb' => ['Laporan', 'Bulanan'],
        ]);
    }

    /* ==================== PENGATURAN ==================== */
    public function pengaturan()
    {
        return view('admin.pengaturan');
    }

    /**
     * Switch Mode (Dev Utility - Admin melihat halaman sebagai User)
     */
    public function switchToUser(Request $request)
    {
        session([
            'admin_viewing_as_user' => true,
            'admin_preview_as_user' => true,
        ]);
        return redirect()->route('home')->with('info', 'Anda sekarang melihat tampilan sebagai User.');
    }

    public function switchBackToAdmin(Request $request)
    {
        session()->forget(['admin_viewing_as_user', 'admin_preview_as_user']);
        return redirect()->route('admin.dashboard')->with('success', 'Kembali ke mode Admin.');
    }
}
