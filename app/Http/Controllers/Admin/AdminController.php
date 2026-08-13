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
        $totalUnit          = \App\Models\Unit::count();
        $totalPemeliharaan  = \App\Models\Pengajuan::count();
        $totalPemeriksaan   = \App\Models\CekHarianUnit::count() + \App\Models\CekHarianAlat::count();

        // Data Grafik Bulanan Pengecekan Tahun Ini (12 Bulan)
        $currentYear  = (int) date('Y');
        $chartPemadam = [];
        $chartRescue  = [];
        $chartCC      = [];

        for ($m = 1; $m <= 12; $m++) {
            $pemadamCount = \App\Models\CekHarianUnit::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where(function($q){ $q->where('kategori', 'pemadam')->orWhereNull('kategori'); })->count()
                + \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where(function($q){ $q->where('kategori', 'pemadam')->orWhereNull('kategori'); })->count();
            
            $rescueCount = \App\Models\CekHarianUnit::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where('kategori', 'rescue')->count()
                + \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where('kategori', 'rescue')->count();
            
            $ccCount = \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->where('kategori', 'command_center')->count();

            $chartPemadam[] = $pemadamCount;
            $chartRescue[]  = $rescueCount;
            $chartCC[]      = $ccCount;
        }

        // Stream Aktivitas Terbaru
        $recentPengajuans = \App\Models\Pengajuan::latest()->take(4)->get()->map(function ($p) {
            return (object) [
                'icon'       => 'wrench',
                'color'      => '#C0201F',
                'bg'         => 'rgba(192,32,31,.10)',
                'text'       => 'Pengajuan pemeliharaan unit ' . strtoupper($p->nomor_lambung ?? $p->pos),
                'created_at' => $p->created_at,
            ];
        });

        $recentCekUnits = \App\Models\CekHarianUnit::latest()->take(4)->get()->map(function ($cu) {
            return (object) [
                'icon'       => 'truck',
                'color'      => '#1B2A6B',
                'bg'         => 'rgba(27,42,107,.10)',
                'text'       => 'Cek harian unit ' . ($cu->unit_nama ?? $cu->pos) . ' (' . ucfirst($cu->kategori ?? 'pemadam') . ')',
                'created_at' => $cu->created_at,
            ];
        });

        $recentCekAlats = \App\Models\CekHarianAlat::latest()->take(4)->get()->map(function ($ca) {
            $catLabel = $ca->kategori === 'command_center' ? 'Command Center' : ucfirst($ca->kategori ?? 'pemadam');
            return (object) [
                'icon'       => $ca->kategori === 'command_center' ? 'radio-tower' : 'clipboard-check',
                'color'      => '#D97706',
                'bg'         => 'rgba(217,119,6,.10)',
                'text'       => 'Cek harian alat ' . $catLabel . ' (' . ($ca->pos ?? 'Utama') . ')',
                'created_at' => $ca->created_at,
            ];
        });

        $activities = $recentPengajuans->concat($recentCekUnits)->concat($recentCekAlats)
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        return view('admin.dashboard', compact(
            'totalUnit',
            'totalPemeliharaan',
            'totalPemeriksaan',
            'chartPemadam',
            'chartRescue',
            'chartCC',
            'activities'
        ));
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

    public function pemeliharaanDataUnit()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Data Unit',
            'breadcrumb' => ['Pemeliharaan', 'Data Unit'],
        ]);
    }

    public function pemeliharaanDataPeralatan()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Data Peralatan',
            'breadcrumb' => ['Pemeliharaan', 'Data Peralatan'],
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
        $tab         = $request->query('tab', 'unit'); // 'unit' atau 'alat'
        $searchQuery = $request->query('search', '');

        // ===== Hasil Cek Harian Unit Kendaraan =====
        $unitQuery = CekHarianUnit::where(function ($q) {
            $q->where('kategori', 'pemadam')->orWhereNull('kategori');
        });

        if (!empty($searchQuery)) {
            $unitQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'LIKE', "%{$searchQuery}%");
            });
        }

        $cekUnitList = $unitQuery->latest()
            ->paginate(10, ['*'], 'unit_page')
            ->withQueryString();

        // ===== Hasil Cek Harian Alat Pemadam =====
        $alatQuery = CekHarianAlat::where(function ($q) {
            $q->where('kategori', 'pemadam')->orWhereNull('kategori');
        });

        if (!empty($searchQuery)) {
            $alatQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'LIKE', "%{$searchQuery}%");
            });
        }

        $cekAlatList = $alatQuery->latest()
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Ringkasan KPI =====
        $kpi = [
            'total_cek_unit'   => CekHarianUnit::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->count(),
            'unit_ada_rusak'   => CekHarianUnit::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->where('jumlah_rusak', '>', 0)->count(),
            'total_cek_alat'   => CekHarianAlat::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->count(),
            'alat_rusak_total' => (int) CekHarianAlat::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->sum('total_rusak'),
        ];

        return view('admin.unit-pemadam.pengecekan', compact('cekUnitList', 'cekAlatList', 'kpi', 'tab', 'searchQuery'));
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

    /**
     * Halaman Pengecekan Rescue: menampilkan hasil input Cek Harian Unit
     * Kendaraan Rescue dan Cek Harian Alat Rescue yang diisi oleh petugas.
     */
    public function unitRescuePengecekan(Request $request)
    {
        $tab         = $request->query('tab', 'unit'); // 'unit' atau 'alat'
        $searchQuery = $request->query('search', '');

        // ===== Hasil Cek Harian Unit Kendaraan Rescue =====
        $unitQuery = CekHarianUnit::where('kategori', 'rescue');

        if (!empty($searchQuery)) {
            $unitQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'LIKE', "%{$searchQuery}%");
            });
        }

        $cekUnitList = $unitQuery->latest()
            ->paginate(10, ['*'], 'unit_page')
            ->withQueryString();

        // ===== Hasil Cek Harian Alat Rescue =====
        $alatQuery = CekHarianAlat::where('kategori', 'rescue');

        if (!empty($searchQuery)) {
            $alatQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'LIKE', "%{$searchQuery}%");
            });
        }

        $cekAlatList = $alatQuery->latest()
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Ringkasan KPI =====
        $kpi = [
            'total_cek_unit'   => CekHarianUnit::where('kategori', 'rescue')->count(),
            'unit_ada_rusak'   => CekHarianUnit::where('kategori', 'rescue')->where('jumlah_rusak', '>', 0)->count(),
            'total_cek_alat'   => CekHarianAlat::where('kategori', 'rescue')->count(),
            'alat_rusak_total' => (int) CekHarianAlat::where('kategori', 'rescue')->sum('total_rusak'),
        ];

        return view('admin.unit-rescue.pengecekan', compact('cekUnitList', 'cekAlatList', 'kpi', 'tab', 'searchQuery'));
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

    public function commandCenterPengecekan(Request $request)
    {
        $searchQuery = $request->query('search', '');

        $query = CekHarianAlat::where('kategori', 'command_center');

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'LIKE', "%{$searchQuery}%");
            });
        }

        $cekAlatList = $query->latest()->get();

        $kpi = [
            'total_cek_cc'   => $cekAlatList->count(),
            'total_baik_cc'  => (int) $cekAlatList->sum('total_baik'),
            'total_rusak_cc' => (int) $cekAlatList->sum('total_rusak'),
        ];

        return view('admin.command-center.pengecekan', compact('cekAlatList', 'kpi', 'searchQuery'));
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
