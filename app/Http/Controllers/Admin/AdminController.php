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
    public function dashboard(Request $request)
    {
        $currentYear = (int) $request->query('tahun', date('Y'));

        // 1. KPI Stats Summary
        // Asset Snapshot (Aset terdaftar hingga akhir tahun yang dipilih)
        $totalUnit        = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)->count();
        $unitPemadam      = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)->where('kategori', 'LIKE', 'pemadam')->count();
        $unitRescue       = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)->where('kategori', 'LIKE', 'rescue')->count();
        $unitAktif        = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)->where('status', 'aktif')->count();
        $unitPerbaikan    = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)->where('status', 'perbaikan')->count();

        $totalPeralatan   = \App\Models\Peralatan::whereYear('created_at', '<=', $currentYear)->sum('jumlah_total');
        $jenisPeralatan   = \App\Models\Peralatan::whereYear('created_at', '<=', $currentYear)->count();
        $peralatanBaik    = \App\Models\Peralatan::whereYear('created_at', '<=', $currentYear)->where('status', 'baik')->count();

        // Transaksi & Aktivitas pada tahun yang dipilih ($currentYear)
        $totalPengajuan   = \App\Models\Pengajuan::whereYear('created_at', $currentYear)->count();
        $totalPemeriksaan = \App\Models\CekHarianUnit::whereYear('created_at', $currentYear)->count() 
                            + \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)->count();

        $totalInvoiceBiaya = \App\Models\Invoice::whereYear('tanggal_invoice', $currentYear)->sum('total_biaya');
        $totalInvoiceCount = \App\Models\Invoice::whereYear('tanggal_invoice', $currentYear)->count();

        // 2. Monthly Chart Datasets (Jan - Dec)
        $chartInspeksiUnit = [];
        $chartInspeksiAlat = [];
        $chartPemeliharaan = [];
        $chartBiaya        = [];

        for ($m = 1; $m <= 12; $m++) {
            $unitCheck = \App\Models\CekHarianUnit::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->count();
            $alatCheck = \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->count();
            $pengajuan = \App\Models\Pengajuan::whereYear('created_at', $currentYear)->whereMonth('created_at', $m)->count();
            $biaya     = \App\Models\Invoice::whereYear('tanggal_invoice', $currentYear)->whereMonth('tanggal_invoice', $m)->sum('total_biaya');

            $chartInspeksiUnit[] = $unitCheck;
            $chartInspeksiAlat[] = $alatCheck;
            $chartPemeliharaan[] = $pengajuan;
            $chartBiaya[]        = (float) $biaya;
        }

        // 3. Pos Penempatan Distribution (Berdasarkan Data Pos Resmi)
        $posDistribution = \App\Models\Pos::where('status', 'aktif')
            ->get()
            ->map(function ($pos) use ($currentYear) {
                $total = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)
                    ->where('pos', 'LIKE', $pos->nama)
                    ->count();
                return (object) [
                    'pos'   => $pos->nama,
                    'total' => $total,
                ];
            })
            ->filter(fn($item) => $item->total > 0)
            ->sortByDesc('total')
            ->values();

        // 4. Stream Aktivitas Terbaru (Filtered by $currentYear)
        $recentPengajuans = \App\Models\Pengajuan::whereYear('created_at', $currentYear)->latest()->take(4)->get()->map(function ($p) {
            return (object) [
                'icon'       => 'wrench',
                'color'      => '#C0201F',
                'bg'         => 'rgba(192,32,31,.10)',
                'text'       => 'Pengajuan pemeliharaan unit ' . strtoupper($p->nomor_lambung ?? $p->pos),
                'created_at' => $p->created_at,
            ];
        });

        $recentCekUnits = \App\Models\CekHarianUnit::whereYear('created_at', $currentYear)->latest()->take(4)->get()->map(function ($cu) {
            return (object) [
                'icon'       => 'truck',
                'color'      => '#1B2A6B',
                'bg'         => 'rgba(27,42,107,.10)',
                'text'       => 'Cek harian unit ' . ($cu->unit_nama ?? $cu->pos) . ' (' . ucfirst($cu->kategori ?? 'pemadam') . ')',
                'created_at' => $cu->created_at,
            ];
        });

        $recentCekAlats = \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)->latest()->take(4)->get()->map(function ($ca) {
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
            'unitPemadam',
            'unitRescue',
            'unitAktif',
            'unitPerbaikan',
            'totalPeralatan',
            'jenisPeralatan',
            'peralatanBaik',
            'totalPengajuan',
            'totalPemeriksaan',
            'totalInvoiceBiaya',
            'totalInvoiceCount',
            'currentYear',
            'chartInspeksiUnit',
            'chartInspeksiAlat',
            'chartPemeliharaan',
            'chartBiaya',
            'posDistribution',
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

        $rawVerifs = $request->input('item_verifikasis', []);
        $itemVerifikasis = [];
        foreach ($rawVerifs as $itemName => $itemStatus) {
            $cleanName = ucwords(strtolower(trim($itemName)));
            $itemVerifikasis[$cleanName] = $itemStatus;
        }

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
            
            // Otomatis isi tanggal_mulai_pengerjaan dengan tanggal keberangkatan jika belum diisi
            if (empty($pengajuan->tanggal_mulai_pengerjaan)) {
                $pengajuan->tanggal_mulai_pengerjaan = $request->tanggal_keberangkatan;
            }

            // Jika tanggal keberangkatan diset tanggal hari ini atau telah lewat, otomatis ubah status ke 'proses'
            $today = now()->format('Y-m-d');
            if ($request->tanggal_keberangkatan <= $today && $pengajuan->status_pengerjaan === 'belum_mulai') {
                $pengajuan->status_pengerjaan = 'proses';
                if ((int)$pengajuan->progress_persen === 0) {
                    $pengajuan->progress_persen = 10;
                }
            }
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

    public function pemeliharaanPemeliharaan(Request $request)
    {
        $search = $request->query('search', '');

        // Hanya tampilkan pengajuan yang sudah diverifikasi (status = disetujui)
        $query = Pengajuan::where('status', 'disetujui')->latest();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lambung', 'LIKE', "%{$search}%")
                  ->orWhere('pos', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pemegang', 'LIKE', "%{$search}%")
                  ->orWhere('item_perbaikan', 'LIKE', "%{$search}%");
            });
        }

        $records = $query->paginate(15)->withQueryString();

        return view('admin.pemeliharaan.pemeliharaan', compact('records', 'search'));
    }

    /**
     * Cetak / Export Dokumen Pemeliharaan (Permohonan Bidang, Permohonan Bengkel, Surat Pesanan)
     */
    public function cetakDokumen($id, $type)
    {
        $pengajuan = Pengajuan::find($id);

        if (!$pengajuan) {
            $pengajuan = (object) [
                'id'                   => $id,
                'created_at'           => now(),
                'kode_verifikasi'      => 'HAR-' . date('Ymd') . '-' . sprintf('%04d', $id),
                'bidang'               => 'Pemadam',
                'pos'                  => 'Soreang (MAKO)',
                'regu'                 => 'Regu Pemadam 1',
                'jenis_kendaraan'      => 'Pancar',
                'nomor_lambung'        => 'P-04 / D 9429 V',
                'item_perbaikan'       => "Pengantian oli mesin\nService rem depan/belakang\nPerbaikan pompa pancar",
                'item_list'            => ['Pengantian oli mesin', 'Service rem depan/belakang', 'Perbaikan pompa pancar'],
                'nama_pemegang'        => 'Ahmad Sobari',
                'nip_pemegang'         => '19850312 201001 1 004',
                'nama_komandan_regu'   => 'Budi Santoso',
                'nip_komandan_regu'    => '19790815 200501 1 002',
                'nama_kepala_bidang'   => 'Drs. H. Mulyadi, M.Si',
                'nip_kepala_bidang'    => '19681120 199303 1 005',
                'status'               => 'disetujui',
                'tanggal_keberangkatan'=> now()->format('Y-m-d'),
                'catatan_admin'        => 'Unit diizinkan untuk perbaikan ke bengkel rekanan.',
            ];
        }

        $typeNames = [
            'permohonanbidang'  => 'Surat Permohonan Bidang',
            'permohonanbengkel' => 'Surat Permohonan Bengkel',
            'Suratpesanan'      => 'Surat Pesanan Pekerjaan Pemeliharaan',
        ];

        $title = $typeNames[$type] ?? 'Dokumen Pemeliharaan';

        return view('admin.pemeliharaan.cetak-dokumen', compact('pengajuan', 'type', 'title'));
    }

    public function pemeliharaanMonitoringAktual(Request $request)
    {
        $statusFilter = $request->query('status_pengerjaan', 'semua');
        $searchQuery  = $request->query('search', '');

        // Auto-sync: pengajuan yang disetujui & punya tanggal keberangkatan
        $today = now()->format('Y-m-d');
        $approvedList = Pengajuan::where('status', 'disetujui')->whereNotNull('tanggal_keberangkatan')->get();
        foreach ($approvedList as $item) {
            $changed = false;
            $keberangkatan = $item->tanggal_keberangkatan ? $item->tanggal_keberangkatan->format('Y-m-d') : null;

            // 1. Auto-fill tanggal_mulai_pengerjaan dari tanggal_keberangkatan jika masih kosong
            if ($keberangkatan && empty($item->tanggal_mulai_pengerjaan)) {
                $item->tanggal_mulai_pengerjaan = $keberangkatan;
                $changed = true;
            }

            // 2. Auto-update status_pengerjaan ke 'proses' jika jadwal keberangkatan <= hari ini dan masih 'belum_mulai'
            if ($keberangkatan && $keberangkatan <= $today && $item->status_pengerjaan === 'belum_mulai') {
                $item->status_pengerjaan = 'proses';
                if ((int)$item->progress_persen === 0) {
                    $item->progress_persen = 10;
                }
                $changed = true;
            }

            if ($changed) {
                $item->save();
            }
        }

        // Hanya unit yang sudah disetujui yang masuk pipeline pengerjaan aktual
        $query = Pengajuan::where('status', 'disetujui')->latest();

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['belum_mulai', 'proses', 'selesai'])) {
            $query->where('status_pengerjaan', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_lambung', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'LIKE', "%{$searchQuery}%");
            });
        }

        $records = $query->paginate(10)->withQueryString();

        $kpi = [
            'total'       => Pengajuan::where('status', 'disetujui')->count(),
            'belum_mulai' => Pengajuan::where('status', 'disetujui')->where('status_pengerjaan', 'belum_mulai')->count(),
            'proses'      => Pengajuan::where('status', 'disetujui')->where('status_pengerjaan', 'proses')->count(),
            'selesai'     => Pengajuan::where('status', 'disetujui')->where('status_pengerjaan', 'selesai')->count(),
        ];

        return view('admin.pemeliharaan.monitoring-aktual', compact('records', 'kpi', 'statusFilter', 'searchQuery'));
    }

    /**
     * Update progres pengerjaan aktual (dipakai oleh modal Update Progres).
     */
    public function updateProgresPengerjaan(Request $request, $id)
    {
        $validated = $request->validate([
            'status_pengerjaan'          => 'required|in:belum_mulai,proses,selesai',
            'tanggal_mulai_pengerjaan'   => 'nullable|date',
            'tanggal_selesai_pengerjaan' => 'nullable|date',
            'progress_persen'            => 'required|integer|min:0|max:100',
            'progress_catatan'           => 'nullable|string|max:1000',
        ]);

        $pengajuan = Pengajuan::findOrFail($id);

        // Konsistensi otomatis: selesai -> 100%, belum mulai -> 0%
        if ($validated['status_pengerjaan'] === 'selesai') {
            $validated['progress_persen'] = 100;
            $validated['tanggal_selesai_pengerjaan'] = $validated['tanggal_selesai_pengerjaan'] ?? now()->format('Y-m-d');
        } elseif ($validated['status_pengerjaan'] === 'belum_mulai') {
            $validated['progress_persen'] = 0;
        }

        $pengajuan->update($validated);

        return redirect()
            ->route('admin.pemeliharaan.monitoring-aktual')
            ->with('success', "Progres pengerjaan unit '{$pengajuan->nomor_lambung}' berhasil diperbarui.");
    }

    /**
     * Kartu Kendali Pembayaran Pemeliharaan — ledger berjalan berbasis
     * data Monitoring Invoice (poin 1.e), menampilkan saldo kumulatif
     * per kode rekening / tahun anggaran (poin 1.g).
     */
    public function pemeliharaanKartuKendaliPembayaran(Request $request)
    {
        $tahunList = \App\Models\Invoice::whereNotNull('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->filter()
            ->values();

        $rekeningList = \App\Models\Invoice::whereNotNull('kode_rekening')
            ->where('kode_rekening', '!=', '')
            ->distinct()
            ->orderBy('kode_rekening')
            ->pluck('kode_rekening');

        $tahunFilter    = $request->query('tahun', $tahunList->first() ?? date('Y'));
        $rekeningFilter = $request->query('kode_rekening', 'semua');
        $statusFilter   = $request->query('status', 'semua');
        $searchQuery    = $request->query('search', '');

        $query = \App\Models\Invoice::with('unit')
            ->where(function ($q) use ($tahunFilter) {
                $q->where('tahun_anggaran', $tahunFilter)
                  ->orWhereYear('tanggal_invoice', $tahunFilter);
            })
            ->orderBy('tanggal_invoice', 'asc')
            ->orderBy('id', 'asc');

        if ($rekeningFilter !== 'semua') {
            $query->where('kode_rekening', $rekeningFilter);
        }

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['draft', 'diajukan', 'disetujui', 'lunas'])) {
            $query->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_invoice', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('no_pol', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('no_lambung', 'LIKE', "%{$searchQuery}%");
            });
        }

        $invoiceList = $query->get();

        // Hitung saldo kumulatif berjalan (running total) — inti dari kartu kendali
        $saldoBerjalan = 0;
        $kartuKendaliRows = $invoiceList->map(function ($invoice) use (&$saldoBerjalan) {
            $saldoBerjalan += (float) $invoice->total_biaya;
            $invoice->saldo_kumulatif = $saldoBerjalan;
            return $invoice;
        });

        $kpi = [
            'total_invoice'  => $invoiceList->count(),
            'total_nilai'    => $invoiceList->sum('total_biaya'),
            'total_lunas'    => $invoiceList->where('status', 'lunas')->sum('total_biaya'),
            'total_belum'    => $invoiceList->whereIn('status', ['draft', 'diajukan', 'disetujui'])->sum('total_biaya'),
            'jumlah_lunas'   => $invoiceList->where('status', 'lunas')->count(),
            'jumlah_belum'   => $invoiceList->whereIn('status', ['draft', 'diajukan', 'disetujui'])->count(),
        ];

        return view('admin.pemeliharaan.kartu-kendali-pembayaran', [
            'kartuKendaliRows' => $kartuKendaliRows,
            'kpi'              => $kpi,
            'tahunList'        => $tahunList,
            'rekeningList'     => $rekeningList,
            'tahunFilter'      => $tahunFilter,
            'rekeningFilter'   => $rekeningFilter,
            'statusFilter'     => $statusFilter,
            'searchQuery'      => $searchQuery,
        ]);
    }

    /**
     * Kartu Kendali Aktual Pemeliharaan — ledger realisasi fisik pekerjaan
     * berbasis data Monitoring Aktual (poin 1.f), menampilkan progres
     * pengerjaan tiap unit per tahun (poin 1.h).
     */
    public function pemeliharaanKartuKendaliAktual(Request $request)
    {
        $tahunList = Pengajuan::where('status', 'disetujui')
            ->whereNotNull('tanggal_keberangkatan')
            ->pluck('tanggal_keberangkatan')
            ->map(fn ($tgl) => \Carbon\Carbon::parse($tgl)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $tahunFilter  = $request->query('tahun', $tahunList->first() ?? date('Y'));
        $statusFilter = $request->query('status_pengerjaan', 'semua');
        $posFilter    = $request->query('pos', 'semua');
        $searchQuery  = $request->query('search', '');

        $query = Pengajuan::where('status', 'disetujui')
            ->where(function ($q) use ($tahunFilter) {
                $q->whereYear('tanggal_mulai_pengerjaan', $tahunFilter)
                  ->orWhereYear('tanggal_keberangkatan', $tahunFilter);
            })
            ->orderBy('tanggal_mulai_pengerjaan', 'asc')
            ->orderBy('id', 'asc');

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['belum_mulai', 'proses', 'selesai'])) {
            $query->where('status_pengerjaan', $statusFilter);
        }

        if ($posFilter !== 'semua') {
            $query->where('pos', $posFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_lambung', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'LIKE', "%{$searchQuery}%");
            });
        }

        $kartuKendaliRows = $query->get();

        $posList = Pengajuan::where('status', 'disetujui')
            ->whereNotNull('pos')
            ->distinct()
            ->orderBy('pos')
            ->pluck('pos');

        $kpi = [
            'total'       => $kartuKendaliRows->count(),
            'belum_mulai' => $kartuKendaliRows->where('status_pengerjaan', 'belum_mulai')->count(),
            'proses'      => $kartuKendaliRows->where('status_pengerjaan', 'proses')->count(),
            'selesai'     => $kartuKendaliRows->where('status_pengerjaan', 'selesai')->count(),
        ];

        return view('admin.pemeliharaan.kartu-kendali-aktual', [
            'kartuKendaliRows' => $kartuKendaliRows,
            'kpi'              => $kpi,
            'tahunList'        => $tahunList,
            'tahunFilter'      => $tahunFilter,
            'statusFilter'     => $statusFilter,
            'posFilter'        => $posFilter,
            'posList'          => $posList,
            'searchQuery'      => $searchQuery,
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

    /* ==================== PENGATURAN & MANAJEMEN AKUN ==================== */
    public function pengaturan(Request $request)
    {
        $roleFilter   = $request->query('role', 'semua');
        $posFilter    = $request->query('pos', 'semua');
        $reguFilter   = $request->query('regu', 'semua');
        $bidangFilter = $request->query('bidang', 'semua');
        $statusFilter = $request->query('status', 'semua');
        $searchQuery  = $request->query('search', '');

        $query = \App\Models\User::orderBy('id', 'asc');

        if ($roleFilter !== 'semua') {
            $query->where('role', $roleFilter);
        }

        if ($posFilter !== 'semua') {
            $query->where('pos', 'LIKE', $posFilter);
        }

        if ($reguFilter !== 'semua') {
            $query->where('regu', 'LIKE', $reguFilter);
        }

        if ($bidangFilter !== 'semua') {
            $query->where('bidang', 'LIKE', $bidangFilter);
        }

        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nip', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('jabatan', 'LIKE', "%{$searchQuery}%");
            });
        }

        $userList = $query->paginate(12)->withQueryString();

        $posList = \App\Models\Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        $existingBidangList = \App\Models\User::whereNotNull('bidang')
            ->where('bidang', '!=', '')
            ->pluck('bidang')
            ->map(fn($v) => ucwords(strtolower(trim($v))))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingBidangList)) {
            $existingBidangList = ['Pemadam', 'Rescue', 'Command Center', 'Sekretariat', 'Sarana Prasarana'];
        }

        // Hitung statistik per Bidang secara dinamis
        $bidangCounts = [];
        foreach ($existingBidangList as $b) {
            $bidangCounts[$b] = \App\Models\User::where('bidang', 'LIKE', $b)->count();
        }

        $kpi = [
            'total'   => \App\Models\User::count(),
            'admin'   => \App\Models\User::where('role', 'admin')->count(),
            'aktif'   => \App\Models\User::where('status', 'aktif')->count(),
            'bidang'  => $bidangCounts,
        ];

        $existingReguList = \App\Models\User::whereNotNull('regu')
            ->where('regu', '!=', '')
            ->pluck('regu')
            ->map(fn($v) => ucwords(strtolower(trim($v))))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingReguList)) {
            $existingReguList = ['Regu 1', 'Regu 2', 'Regu 3', 'Regu 4'];
        }

        $existingJabatanList = \App\Models\User::whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->pluck('jabatan')
            ->map(fn($v) => trim($v))
            ->unique()
            ->values()
            ->toArray();

        if (empty($existingJabatanList)) {
            $existingJabatanList = [
                'Komandan Regu (Danru)',
                'Kepala Seksi (Kasi)',
                'Kepala Bidang (Kabid)',
                'Anggota / Petugas',
                'Pengemudi / Driver',
            ];
        }

        return view('admin.pengaturan', compact(
            'userList',
            'kpi',
            'roleFilter',
            'posFilter',
            'reguFilter',
            'bidangFilter',
            'statusFilter',
            'searchQuery',
            'posList',
            'existingBidangList',
            'existingReguList',
            'existingJabatanList'
        ));
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
