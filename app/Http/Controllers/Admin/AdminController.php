<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CekHarianAlat;
use App\Models\CekHarianUnit;
use App\Models\Pengajuan;
use App\Models\PengaturanDokumen;
use App\Services\CacheService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Dashboard Utama Admin
     */
    public function dashboard(Request $request)
    {
        \App\Models\Unit::syncStatusAll();

        $currentYear = (int) $request->query('tahun', date('Y'));

        // Cache statistical computations and charts for 30 seconds to bypass remote cloud latency
        $stats = \Illuminate\Support\Facades\Cache::remember("admin_dashboard_stats_{$currentYear}", 30, function () use ($currentYear) {
            // 1. KPI Stats Summary (Consolidated in 1 query)
            $unitKpi = \App\Models\Unit::whereYear('created_at', '<=', $currentYear)
                ->selectRaw("
                    count(*) as total,
                    count(case when lower(kategori) like '%pemadam%' then 1 end) as pemadam,
                    count(case when lower(kategori) like '%rescue%' then 1 end) as rescue,
                    count(case when status = 'aktif' then 1 end) as aktif,
                    count(case when status = 'perbaikan' then 1 end) as perbaikan
                ")->first();

            $totalPeralatan   = \App\Models\Peralatan::whereYear('created_at', '<=', $currentYear)->count();

            // 2. Monthly Chart Datasets (Jan - Dec)
            $monthlyInspeksiUnit = \App\Models\CekHarianUnit::whereYear('created_at', $currentYear)
                ->selectRaw("EXTRACT(MONTH FROM created_at)::int as m, count(*) as c")
                ->groupBy('m')
                ->pluck('c', 'm')
                ->toArray();

            $monthlyInspeksiAlat = \App\Models\CekHarianAlat::whereYear('created_at', $currentYear)
                ->selectRaw("EXTRACT(MONTH FROM created_at)::int as m, count(*) as c")
                ->groupBy('m')
                ->pluck('c', 'm')
                ->toArray();

            $monthlyPengajuan = \App\Models\Pengajuan::whereYear('created_at', $currentYear)
                ->selectRaw("EXTRACT(MONTH FROM created_at)::int as m, count(*) as c")
                ->groupBy('m')
                ->pluck('c', 'm')
                ->toArray();

            $monthlyBiayaStats = \App\Models\Invoice::where('kategori_monitoring', 'aktual')
                ->whereYear('tanggal_invoice', $currentYear)
                ->selectRaw("EXTRACT(MONTH FROM tanggal_invoice)::int as m, sum(total_biaya) as s, count(*) as c")
                ->groupBy('m')
                ->get();

            $monthlyBiaya = $monthlyBiayaStats->pluck('s', 'm')->toArray();
            $totalInvoiceCount = $monthlyBiayaStats->sum('c');
            $totalInvoiceBiaya = (float) array_sum($monthlyBiaya);

            $chartInspeksiUnit = [];
            $chartInspeksiAlat = [];
            $chartPemeliharaan = [];
            $chartBiaya        = [];

            for ($m = 1; $m <= 12; $m++) {
                $chartInspeksiUnit[] = (int) ($monthlyInspeksiUnit[$m] ?? 0);
                $chartInspeksiAlat[] = (int) ($monthlyInspeksiAlat[$m] ?? 0);
                $chartPemeliharaan[] = (int) ($monthlyPengajuan[$m] ?? 0);
                $chartBiaya[]        = (float) ($monthlyBiaya[$m] ?? 0);
            }

            $totalPengajuan   = array_sum($chartPemeliharaan);
            $totalPemeriksaan = array_sum($chartInspeksiUnit) + array_sum($chartInspeksiAlat);

            // 3. Pos Penempatan Distribution (Pure Array)
            $posDistribution = \App\Models\Unit::whereNotNull('pos')
                ->where('pos', '!=', '')
                ->selectRaw("pos, count(*) as total")
                ->groupBy('pos')
                ->orderByDesc('total')
                ->get()
                ->map(fn($item) => [
                    'pos'   => (string) $item->pos,
                    'total' => (int) $item->total,
                ])
                ->values()
                ->toArray();

            // 4. Stream Aktivitas Terbaru (Pure Array)
            $recentPengajuans = \App\Models\Pengajuan::latest('id')
                ->take(4)
                ->get(['id', 'nomor_lambung', 'pos', 'created_at'])
                ->map(function ($p) {
                    return [
                        'icon'       => 'wrench',
                        'color'      => '#C0201F',
                        'bg'         => 'rgba(192,32,31,.10)',
                        'text'       => 'Pengajuan pemeliharaan unit ' . strtoupper($p->nomor_lambung ?? $p->pos ?? 'Armada'),
                        'created_at' => $p->created_at ? $p->created_at->diffForHumans() : 'Baru saja',
                        'raw_time'   => $p->created_at ? $p->created_at->timestamp : 0,
                    ];
                })
                ->toArray();

            $recentCekUnits = \App\Models\CekHarianUnit::latest('id')
                ->take(4)
                ->get(['id', 'unit_nama', 'pos', 'kategori', 'created_at'])
                ->map(function ($cu) {
                    return [
                        'icon'       => 'truck',
                        'color'      => '#1B2A6B',
                        'bg'         => 'rgba(27,42,107,.10)',
                        'text'       => 'Cek harian unit ' . ($cu->unit_nama ?? $cu->pos ?? 'Armada') . ' (' . ucfirst($cu->kategori ?? 'pemadam') . ')',
                        'created_at' => $cu->created_at ? $cu->created_at->diffForHumans() : 'Baru saja',
                        'raw_time'   => $cu->created_at ? $cu->created_at->timestamp : 0,
                    ];
                })
                ->toArray();

            $recentCekAlats = \App\Models\CekHarianAlat::latest('id')
                ->take(4)
                ->get(['id', 'kategori', 'pos', 'created_at'])
                ->map(function ($ca) {
                    $catLabel = $ca->kategori === 'command_center' ? 'Command Center' : ucfirst($ca->kategori ?? 'pemadam');
                    return [
                        'icon'       => $ca->kategori === 'command_center' ? 'radio-tower' : 'clipboard-check',
                        'color'      => '#D97706',
                        'bg'         => 'rgba(217,119,6,.10)',
                        'text'       => 'Cek harian alat ' . $catLabel . ' (' . ($ca->pos ?? 'Utama') . ')',
                        'created_at' => $ca->created_at ? $ca->created_at->diffForHumans() : 'Baru saja',
                        'raw_time'   => $ca->created_at ? $ca->created_at->timestamp : 0,
                    ];
                })
                ->toArray();

            $activities = collect(array_merge($recentPengajuans, $recentCekUnits, $recentCekAlats))
                ->sortByDesc('raw_time')
                ->take(6)
                ->values()
                ->toArray();

            return [
                'totalUnit'         => (int) ($unitKpi->total ?? 0),
                'unitPemadam'       => (int) ($unitKpi->pemadam ?? 0),
                'unitRescue'        => (int) ($unitKpi->rescue ?? 0),
                'unitAktif'         => (int) ($unitKpi->aktif ?? 0),
                'unitPerbaikan'     => (int) ($unitKpi->perbaikan ?? 0),
                'totalPeralatan'    => $totalPeralatan,
                'jenisPeralatan'    => $totalPeralatan,
                'peralatanBaik'     => $totalPeralatan,
                'totalPengajuan'    => $totalPengajuan,
                'totalPemeriksaan'  => $totalPemeriksaan,
                'totalInvoiceBiaya' => $totalInvoiceBiaya,
                'totalInvoiceCount' => $totalInvoiceCount,
                'chartInspeksiUnit' => $chartInspeksiUnit,
                'chartInspeksiAlat' => $chartInspeksiAlat,
                'chartPemeliharaan' => $chartPemeliharaan,
                'chartBiaya'        => $chartBiaya,
                'posDistribution'   => $posDistribution,
                'activities'        => $activities,
            ];
        });

        // 5. Absen Pengecekan Harian Unit (Cached for 15s to guarantee high responsiveness)
        $absenData = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_absen_unit_v2', 15, function () {
            $todayStart = now()->startOfDay()->toDateTimeString();
            $todayEnd   = now()->endOfDay()->toDateTimeString();
            $todayDate  = now()->format('Y-m-d');

            $todayCekUnits = \App\Models\CekHarianUnit::where(function ($q) use ($todayStart, $todayEnd, $todayDate) {
                    $q->whereBetween('created_at', [$todayStart, $todayEnd])
                      ->orWhere('tanggal_pemeriksaan', $todayDate);
                })
                ->latest('id')
                ->get(['id', 'unit_id', 'nama_pemeriksa', 'jabatan', 'kebersihan_unit', 'created_at'])
                ->keyBy('unit_id');

            $allUnits = \App\Models\Unit::orderBy('nomor_lambung', 'asc')
                ->get(['id', 'nomor_lambung', 'plat_nomor', 'merk_tipe', 'kategori', 'pos', 'status']);

            $absenUnitList = $allUnits->map(function ($unit) use ($todayCekUnits) {
                $cek = $todayCekUnits->get($unit->id);
                return [
                    'unit_id'        => $unit->id,
                    'nomor_lambung'  => $unit->nomor_lambung,
                    'plat_nomor'     => $unit->plat_nomor,
                    'merk_tipe'      => $unit->merk_tipe,
                    'kategori'       => $unit->kategori,
                    'pos'            => $unit->pos ?? '—',
                    'status_unit'    => $unit->status,
                    'sudah_dicek'    => $cek !== null,
                    'nama_pemeriksa' => $cek ? $cek->nama_pemeriksa : null,
                    'jabatan'        => $cek ? $cek->jabatan : null,
                    'kebersihan'     => $cek ? ($cek->kebersihan_unit ?? 'bersih') : null,
                    'waktu_cek'      => $cek ? $cek->created_at->format('H:i') : null,
                ];
            })->values()->toArray();

            $sudahCount = count(array_filter($absenUnitList, fn($u) => !empty($u['sudah_dicek'])));
            $absenSummary = [
                'total_unit'  => count($absenUnitList),
                'sudah_dicek' => $sudahCount,
                'belum_dicek' => count($absenUnitList) - $sudahCount,
            ];

            return [
                'absenUnitList' => $absenUnitList,
                'absenSummary'  => $absenSummary,
            ];
        });

        return view('admin.dashboard', array_merge($stats, $absenData, [
            'currentYear' => $currentYear,
        ]));
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
        if ($statusFilter !== 'semua' && in_array($statusFilter, ['menunggu', 'disetujui', 'selesai', 'ditolak'])) {
            if ($statusFilter === 'selesai') {
                $query->where(function ($q) {
                    $q->where('status', 'selesai')
                      ->orWhere('status_pengerjaan', 'selesai');
                });
            } elseif ($statusFilter === 'disetujui') {
                $query->where('status', 'disetujui')
                      ->where(function ($q) {
                          $q->whereNull('status_pengerjaan')
                            ->orWhere('status_pengerjaan', '!=', 'selesai');
                      });
            } else {
                $query->where('status', $statusFilter);
            }
        }

        // Pencarian berdasarkan nomor_lambung, nama_pemegang, atau pos
        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_lambung', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $pengajuanList = $query->paginate(10)->withQueryString();

        // Ringkasan KPI (1 single query)
        $kpiRaw = Pengajuan::selectRaw("
            count(*) as total,
            count(case when status = 'menunggu' then 1 end) as menunggu,
            count(case when status = 'disetujui' and (status_pengerjaan is null or status_pengerjaan != 'selesai') then 1 end) as disetujui,
            count(case when status = 'selesai' or status_pengerjaan = 'selesai' then 1 end) as selesai,
            count(case when status = 'ditolak' then 1 end) as ditolak
        ")->first();

        $kpi = [
            'total'     => (int) ($kpiRaw->total ?? 0),
            'menunggu'  => (int) ($kpiRaw->menunggu ?? 0),
            'disetujui' => (int) ($kpiRaw->disetujui ?? 0),
            'selesai'   => (int) ($kpiRaw->selesai ?? 0),
            'ditolak'   => (int) ($kpiRaw->ditolak ?? 0),
        ];

        return view('admin.pemeliharaan.pengajuan', compact('pengajuanList', 'kpi', 'statusFilter', 'searchQuery'));
    }

    /**
     * Memverifikasi pengajuan (Setujui / Selesai / Tolak) oleh Admin + Verifikasi per item
     */
    public function verifikasiPengajuan(Request $request, $id)
    {
        $request->validate([
            'status'                => 'required|in:disetujui,ditolak,menunggu,selesai',
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

            if ($request->status === 'selesai') {
                $pengajuan->status = 'selesai';
            } elseif ($hasDisetujui && !$hasDitolak) {
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

        if ($pengajuan->status === 'selesai') {
            $pengajuan->status_pengerjaan = 'selesai';
            $pengajuan->progress_persen = 100;
            if (empty($pengajuan->tanggal_selesai_pengerjaan)) {
                $pengajuan->tanggal_selesai_pengerjaan = now()->format('Y-m-d');
            }
        } elseif ($pengajuan->status === 'disetujui' && $request->filled('tanggal_keberangkatan')) {
            $pengajuan->tanggal_keberangkatan = $request->tanggal_keberangkatan;
            
            // Otomatis isi tanggal_mulai_pengerjaan dengan tanggal keberangkatan jika belum diisi
            if (empty($pengajuan->tanggal_mulai_pengerjaan)) {
                $pengajuan->tanggal_mulai_pengerjaan = $request->tanggal_keberangkatan;
            }

            // Jika tanggal keberangkatan diset tanggal hari ini atau telah lewat, otomatis ubah status ke 'proses'
            $today = now()->format('Y-m-d');
            if ($request->tanggal_keberangkatan <= $today) {
                if ($pengajuan->status_pengerjaan === 'belum_mulai') {
                    $pengajuan->status_pengerjaan = 'proses';
                    if ((int)$pengajuan->progress_persen === 0) {
                        $pengajuan->progress_persen = 10;
                    }
                }
            } else {
                // Jika tanggal keberangkatan di masa depan (misal besok), unit belum masuk bengkel (status masih belum_mulai)
                $pengajuan->status_pengerjaan = 'belum_mulai';
                $pengajuan->progress_persen = 0;
            }
        } elseif ($pengajuan->status !== 'disetujui' && $pengajuan->status !== 'selesai') {
            $pengajuan->tanggal_keberangkatan = null;
        }

        $pengajuan->save();

        // Sinkronisasi status unit secara langsung
        \App\Models\Unit::syncStatusAll();
        \App\Http\Controllers\Admin\InvoiceController::syncPengajuanToAktualInvoice($pengajuan);
        CacheService::invalidate(['pengajuan', 'unit']);

        $statusText = match ($pengajuan->status) {
            'disetujui' => 'disetujui' . ($pengajuan->tanggal_keberangkatan ? ' (Jadwal: ' . $pengajuan->tanggal_keberangkatan->format('d/m/Y') . ')' : ''),
            'selesai'   => 'selesai dan unit siap beroperasi',
            'ditolak'   => 'ditolak',
            default     => 'diperbarui',
        };

        return redirect()
            ->route('admin.pemeliharaan.pengajuan')
            ->with('success', "Pengajuan unit {$pengajuan->nomor_lambung} berhasil {$statusText}.");
    }

    /**
     * Selesaikan Pengajuan perbaikan saat armada unit kembali ke pos.
     */
    public function selesaikanPengajuan($id)
    {
        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuan->status = 'selesai';
        $pengajuan->status_pengerjaan = 'selesai';
        $pengajuan->progress_persen = 100;
        if (empty($pengajuan->tanggal_selesai_pengerjaan)) {
            $pengajuan->tanggal_selesai_pengerjaan = now()->format('Y-m-d');
        }
        $pengajuan->save();

        if ($pengajuan->nomor_lambung) {
            $unit = \App\Models\Unit::where('nomor_lambung', $pengajuan->nomor_lambung)
                ->orWhere('id', $pengajuan->unit_id)
                ->first();
            if ($unit) {
                $unit->status = 'aktif';
                $unit->saveQuietly();
            }
        }

        \App\Models\Unit::syncStatusAll();
        CacheService::invalidate(['pengajuan', 'unit']);

        return redirect()
            ->route('admin.pemeliharaan.pengajuan')
            ->with('success', "Pengajuan untuk unit {$pengajuan->nomor_lambung} berhasil diselesaikan. Unit telah kembali ke pos dan berstatus Siap Operasi (Ready)!");
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
                $q->where('nomor_lambung', 'ILIKE', "%{$search}%")
                  ->orWhere('pos', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_pemegang', 'ILIKE', "%{$search}%")
                  ->orWhere('item_perbaikan', 'ILIKE', "%{$search}%");
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

        // Ambil pengaturan dokumen (PKS/SPK/Bengkel) dari database
        $pengaturanDokumen = PengaturanDokumen::getAktif((int) date('Y'));

        return view('admin.pemeliharaan.cetak-dokumen', compact('pengajuan', 'type', 'title', 'pengaturanDokumen'));
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
            } elseif ($keberangkatan && $keberangkatan > $today && $item->status_pengerjaan === 'proses') {
                $item->status_pengerjaan = 'belum_mulai';
                $item->progress_persen = 0;
                $changed = true;
            }

            if ($changed) {
                $item->save();
            }
        }

        \App\Models\Unit::syncStatusAll();

        // Hanya unit yang sudah disetujui yang masuk pipeline pengerjaan aktual
        $query = Pengajuan::where('status', 'disetujui')->latest();

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['belum_mulai', 'proses', 'selesai'])) {
            $query->where('status_pengerjaan', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_lambung', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $records = $query->paginate(10)->withQueryString();

        $kpiRaw = Pengajuan::where('status', 'disetujui')
            ->selectRaw("
                count(*) as total,
                count(case when status_pengerjaan = 'belum_mulai' or status_pengerjaan is null then 1 end) as belum_mulai,
                count(case when status_pengerjaan = 'proses' then 1 end) as proses,
                count(case when status_pengerjaan = 'selesai' then 1 end) as selesai
            ")->first();

        $kpi = [
            'total'       => (int) ($kpiRaw->total ?? 0),
            'belum_mulai' => (int) ($kpiRaw->belum_mulai ?? 0),
            'proses'      => (int) ($kpiRaw->proses ?? 0),
            'selesai'     => (int) ($kpiRaw->selesai ?? 0),
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
        \App\Models\Unit::syncStatusAll();
        CacheService::invalidate(['pengajuan', 'unit']);

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
            ->where(function ($q) {
                $q->where('kategori_monitoring', 'invoice')
                  ->orWhereNull('kategori_monitoring');
            })
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->filter()
            ->values();

        $tahunFilter    = $request->query('tahun', $tahunList->first() ?? date('Y'));
        $searchQuery    = $request->query('search', '');

        $query = \App\Models\Invoice::with('unit')
            ->where(function ($q) {
                $q->where('kategori_monitoring', 'invoice')
                  ->orWhereNull('kategori_monitoring');
            })
            ->where(function ($q) use ($tahunFilter) {
                $q->where('tahun_anggaran', $tahunFilter)
                  ->orWhereYear('tanggal_invoice', $tahunFilter);
            })
            ->orderBy('tanggal_invoice', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_invoice', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('no_pol', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('no_lambung', 'ILIKE', "%{$searchQuery}%");
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

        $totalInvoice = $invoiceList->count();
        $totalNilai   = (float) $invoiceList->sum('total_biaya');
        $totalUnit    = $invoiceList->pluck('no_lambung')->filter()->unique()->count() 
                     ?: $invoiceList->pluck('unit_id')->filter()->unique()->count();
        $rataRata     = $totalInvoice > 0 ? ($totalNilai / $totalInvoice) : 0;

        $kpi = [
            'total_invoice' => $totalInvoice,
            'total_nilai'   => $totalNilai,
            'total_unit'    => $totalUnit,
            'rata_rata'     => $rataRata,
        ];

        $pejabatKasi = \App\Models\User::where('jabatan', 'ILIKE', '%pemeliharaan sarana%')
            ->orWhere('jabatan', 'ILIKE', '%seksi pemeliharaan%')
            ->orWhere('jabatan', 'ILIKE', '%pemeliharaan%')
            ->first();

        return view('admin.pemeliharaan.kartu-kendali-pembayaran', [
            'kartuKendaliRows' => $kartuKendaliRows,
            'kpi'              => $kpi,
            'tahunList'        => $tahunList,
            'tahunFilter'      => $tahunFilter,
            'searchQuery'      => $searchQuery,
            'pejabatKasi'      => $pejabatKasi,
        ]);
    }

    /**
     * Kartu Kendali Aktual Pemeliharaan — ledger realisasi fisik pekerjaan
     * berbasis data Monitoring Aktual (poin 1.f), menampilkan progres
     * pengerjaan tiap unit per tahun (poin 1.h).
     */
    public function pemeliharaanKartuKendaliAktual(Request $request)
    {
        $tahunList = \App\Models\Invoice::where('kategori_monitoring', 'aktual')
            ->whereNotNull('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran')
            ->filter()
            ->values();

        $tahunFilter    = $request->query('tahun', $tahunList->first() ?? date('Y'));
        $searchQuery    = $request->query('search', '');

        $query = \App\Models\Invoice::with('unit')
            ->where('kategori_monitoring', 'aktual')
            ->where(function ($q) use ($tahunFilter) {
                $q->where('tahun_anggaran', $tahunFilter)
                  ->orWhereYear('tanggal_invoice', $tahunFilter);
            })
            ->orderBy('tanggal_invoice', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_invoice', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('no_pol', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('no_lambung', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $invoiceList = $query->get();

        // Hitung saldo kumulatif berjalan
        $saldoBerjalan = 0;
        $kartuKendaliRows = $invoiceList->map(function ($invoice) use (&$saldoBerjalan) {
            $saldoBerjalan += (float) $invoice->total_biaya;
            $invoice->saldo_kumulatif = $saldoBerjalan;
            return $invoice;
        });

        $totalInvoice = $invoiceList->count();
        $totalNilai   = (float) $invoiceList->sum('total_biaya');
        $totalUnit    = $invoiceList->pluck('no_lambung')->filter()->unique()->count() 
                     ?: $invoiceList->pluck('unit_id')->filter()->unique()->count();
        $rataRata     = $totalInvoice > 0 ? ($totalNilai / $totalInvoice) : 0;

        $kpi = [
            'total_invoice' => $totalInvoice,
            'total_nilai'   => $totalNilai,
            'total_unit'    => $totalUnit,
            'rata_rata'     => $rataRata,
        ];

        $pejabatKasi = \App\Models\User::where('jabatan', 'ILIKE', '%pemeliharaan sarana%')
            ->orWhere('jabatan', 'ILIKE', '%seksi pemeliharaan%')
            ->orWhere('jabatan', 'ILIKE', '%pemeliharaan%')
            ->first();

        return view('admin.pemeliharaan.kartu-kendali-aktual', [
            'kartuKendaliRows' => $kartuKendaliRows,
            'kpi'              => $kpi,
            'tahunList'        => $tahunList,
            'tahunFilter'      => $tahunFilter,
            'searchQuery'      => $searchQuery,
            'pejabatKasi'      => $pejabatKasi,
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
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
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
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $cekAlatList = $alatQuery->latest()
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Ringkasan KPI (Cached 5 min) =====
        $kpi = CacheService::rememberStats('cek_pemadam_kpi', function () {
            $unitKpi = CekHarianUnit::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->selectRaw("count(*) as total, count(case when jumlah_rusak > 0 then 1 end) as rusak")->first();

            $alatKpi = CekHarianAlat::where(function ($q) {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            })->selectRaw("count(*) as total, coalesce(sum(total_rusak), 0) as rusak")->first();

            return [
                'total_cek_unit'   => (int) ($unitKpi->total ?? 0),
                'unit_ada_rusak'   => (int) ($unitKpi->rusak ?? 0),
                'total_cek_alat'   => (int) ($alatKpi->total ?? 0),
                'alat_rusak_total' => (int) ($alatKpi->rusak ?? 0),
            ];
        });

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
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $cekUnitList = $unitQuery->latest()
            ->paginate(10, ['*'], 'unit_page')
            ->withQueryString();

        // ===== Hasil Cek Harian Alat Rescue =====
        $alatQuery = CekHarianAlat::where('kategori', 'rescue');

        if (!empty($searchQuery)) {
            $alatQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $cekAlatList = $alatQuery->latest()
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Ringkasan KPI (Cached 5 min) =====
        $kpi = CacheService::rememberStats('cek_rescue_kpi', function () {
            $unitKpi = CekHarianUnit::where('kategori', 'rescue')
                ->selectRaw("count(*) as total, count(case when jumlah_rusak > 0 then 1 end) as rusak")->first();

            $alatKpi = CekHarianAlat::where('kategori', 'rescue')
                ->selectRaw("count(*) as total, coalesce(sum(total_rusak), 0) as rusak")->first();

            return [
                'total_cek_unit'   => (int) ($unitKpi->total ?? 0),
                'unit_ada_rusak'   => (int) ($unitKpi->rusak ?? 0),
                'total_cek_alat'   => (int) ($alatKpi->total ?? 0),
                'alat_rusak_total' => (int) ($alatKpi->rusak ?? 0),
            ];
        });

        return view('admin.unit-rescue.pengecekan', compact('cekUnitList', 'cekAlatList', 'kpi', 'tab', 'searchQuery'));
    }

    public function unitRescueRiwayat()
    {
        return view('admin.placeholder', [
            'pageTitle'  => 'Riwayat',
            'breadcrumb' => ['Unit Rescue', 'Riwayat'],
        ]);
    }

    /* ==================== UNIT PENCEGAHAN ==================== */

    /**
     * Halaman Pengecekan Pencegahan: menampilkan hasil input Cek Harian Unit
     * Kendaraan Pencegahan dan Cek Harian Alat Pencegahan yang diisi oleh petugas.
     */
    public function unitPencegahanPengecekan(Request $request)
    {
        $tab         = $request->query('tab', 'unit');
        $searchQuery = $request->query('search', '');

        // ===== Hasil Cek Harian Unit Kendaraan Pencegahan =====
        $unitQuery = CekHarianUnit::where('kategori', 'pencegahan');

        if (!empty($searchQuery)) {
            $unitQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $cekUnitList = $unitQuery->latest()
            ->paginate(10, ['*'], 'unit_page')
            ->withQueryString();

        // ===== Hasil Cek Harian Alat Pencegahan =====
        $alatQuery = CekHarianAlat::where('kategori', 'pencegahan');

        if (!empty($searchQuery)) {
            $alatQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $cekAlatList = $alatQuery->latest()
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Ringkasan KPI (Cached 5 min) =====
        $kpi = CacheService::rememberStats('cek_pencegahan_kpi', function () {
            $unitKpi = CekHarianUnit::where('kategori', 'pencegahan')
                ->selectRaw("count(*) as total, count(case when jumlah_rusak > 0 then 1 end) as rusak")->first();

            $alatKpi = CekHarianAlat::where('kategori', 'pencegahan')
                ->selectRaw("count(*) as total, coalesce(sum(total_rusak), 0) as rusak")->first();

            return [
                'total_cek_unit'   => (int) ($unitKpi->total ?? 0),
                'unit_ada_rusak'   => (int) ($unitKpi->rusak ?? 0),
                'total_cek_alat'   => (int) ($alatKpi->total ?? 0),
                'alat_rusak_total' => (int) ($alatKpi->rusak ?? 0),
            ];
        });

        return view('admin.unit-pencegahan.pengecekan', compact('cekUnitList', 'cekAlatList', 'kpi', 'tab', 'searchQuery'));
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
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%");
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

        $query = \App\Models\User::where('has_account', true)->orderBy('id', 'asc');

        if ($roleFilter !== 'semua') {
            $query->where('role', $roleFilter);
        }

        if ($posFilter !== 'semua') {
            $query->where('pos', 'ILIKE', $posFilter);
        }

        if ($reguFilter !== 'semua') {
            $query->where('regu', 'ILIKE', $reguFilter);
        }

        if ($bidangFilter !== 'semua') {
            $query->where('bidang', 'ILIKE', $bidangFilter);
        }

        if ($statusFilter !== 'semua') {
            $query->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nip', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('email', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('jabatan', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $userList = $query->paginate(12)->withQueryString();

        $posList = CacheService::rememberList('active_pos_objects', function () {
            return \App\Models\Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get(['id', 'nama']);
        });

        $metaData = CacheService::rememberStats('pengaturan_meta', function () {
            $existingBidangList = \App\Models\User::whereNotNull('bidang')
                ->where('bidang', '!=', '')
                ->distinct()
                ->pluck('bidang')
                ->map(fn($v) => trim($v))
                ->filter(fn($v) => !empty($v))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $bidangCounts = \App\Models\User::where('has_account', true)
                ->whereNotNull('bidang')
                ->where('bidang', '!=', '')
                ->selectRaw("bidang, count(*) as total")
                ->groupBy('bidang')
                ->pluck('total', 'bidang')
                ->toArray();

            $kpiStats = \App\Models\User::where('has_account', true)
                ->selectRaw("
                    count(*) as total,
                    count(case when role = 'admin' then 1 end) as admin,
                    count(case when status = 'aktif' then 1 end) as aktif
                ")->first();

            $kpi = [
                'total'   => (int) ($kpiStats->total ?? 0),
                'admin'   => (int) ($kpiStats->admin ?? 0),
                'aktif'   => (int) ($kpiStats->aktif ?? 0),
                'bidang'  => $bidangCounts,
            ];

            $existingReguList = \App\Models\Regu::distinct()
                ->orderBy('nama', 'asc')
                ->pluck('nama')
                ->map(fn($v) => ucwords(strtolower(trim($v))))
                ->unique()
                ->values()
                ->toArray();

            if (empty($existingReguList)) {
                $existingReguList = ['Regu 1', 'Regu 2'];
            }

            $existingJabatanList = \App\Models\User::whereNotNull('jabatan')
                ->where('jabatan', '!=', '')
                ->pluck('jabatan')
                ->map(fn($v) => trim($v))
                ->filter(fn($v) => !empty($v))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $allReguList = \App\Models\Regu::orderBy('pos', 'asc')->orderBy('nama', 'asc')->get(['id', 'nama', 'pos', 'bidang', 'danru', 'nip_danru'])->toArray();

            return compact('existingBidangList', 'kpi', 'existingReguList', 'existingJabatanList', 'allReguList');
        });

        $existingBidangList  = $metaData['existingBidangList'];
        $kpi                 = $metaData['kpi'];
        $existingReguList    = $metaData['existingReguList'];
        $existingJabatanList = $metaData['existingJabatanList'];
        $allReguList         = $metaData['allReguList'];

        $pegawaiList = \App\Models\User::with(['bidangRelasi:id,nama', 'reguRelasi:id,nama'])
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'nip', 'jabatan', 'bidang', 'bidang_id', 'pos', 'regu', 'regu_id', 'no_hp', 'email', 'role', 'status'])
            ->map(function ($pegawai) {
                return [
                    'id'      => $pegawai->id,
                    'name'    => $pegawai->name,
                    'nip'     => $pegawai->nip,
                    'jabatan' => $pegawai->jabatan,
                    'bidang'  => $pegawai->bidang ?: ($pegawai->bidangRelasi->nama ?? ''),
                    'pos'     => $pegawai->pos,
                    'regu'    => $pegawai->regu ?: ($pegawai->reguRelasi->nama ?? ''),
                    'no_hp'   => $pegawai->no_hp,
                    'email'   => $pegawai->email,
                    'role'    => $pegawai->role,
                    'status'  => $pegawai->status,
                ];
            });

        // Data pengaturan dokumen (PKS/SPK/Bengkel) per tahun
        $pengaturanDokumenList = PengaturanDokumen::orderBy('tahun', 'desc')->get();

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
            'pegawaiList',
            'allReguList',
            'existingBidangList',
            'existingReguList',
            'existingJabatanList',
            'pengaturanDokumenList'
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

    /**
     * Simpan pengaturan dokumen (PKS/SPK/Bengkel) baru
     */
    public function storePengaturanDokumen(Request $request)
    {
        $validated = $request->validate([
            'tahun'                      => 'required|integer|min:2020|max:2099|unique:pengaturan_dokumen,tahun',
            'nomor_pks'                  => 'required|string|max:255',
            'nomor_spk'                  => 'required|string|max:255',
            'tanggal_pks_spk'            => 'required|date',
            'nama_bengkel'               => 'required|string|max:255',
            'alamat_bengkel'             => 'nullable|string|max:500',
            'nama_pimpinan_bengkel'      => 'nullable|string|max:255',
            'ttd_kpa_nama'               => 'nullable|string|max:255',
            'ttd_kpa_nip'                => 'nullable|string|max:50',
            'ttd_kpa_jabatan'            => 'nullable|string|max:255',
            'ttd_kpa_pangkat'            => 'nullable|string|max:100',
            'ttd_pptk_nama'              => 'nullable|string|max:255',
            'ttd_pptk_nip'               => 'nullable|string|max:50',
            'ttd_pptk_jabatan'           => 'nullable|string|max:255',
            'ttd_pptk_pangkat'           => 'nullable|string|max:100',
            'ttd_kabid_pemadam_nama'     => 'nullable|string|max:255',
            'ttd_kabid_pemadam_nip'      => 'nullable|string|max:50',
            'ttd_kabid_pemadam_jabatan'  => 'nullable|string|max:255',
            'ttd_kabid_rescue_nama'      => 'nullable|string|max:255',
            'ttd_kabid_rescue_nip'       => 'nullable|string|max:50',
            'ttd_kabid_rescue_jabatan'   => 'nullable|string|max:255',
            'ttd_kabid_pencegahan_nama'  => 'nullable|string|max:255',
            'ttd_kabid_pencegahan_nip'   => 'nullable|string|max:50',
            'ttd_kabid_pencegahan_jabatan'=> 'nullable|string|max:255',
        ]);

        PengaturanDokumen::create($validated);

        return redirect()->route('admin.pengaturan', ['tab' => 'dokumen'])
            ->with('success', 'Pengaturan dokumen tahun ' . $request->tahun . ' berhasil disimpan.');
    }

    /**
     * Update pengaturan dokumen (PKS/SPK/Bengkel)
     */
    public function updatePengaturanDokumen(Request $request, $id)
    {
        $doc = PengaturanDokumen::findOrFail($id);

        $validated = $request->validate([
            'tahun'                      => 'required|integer|min:2020|max:2099|unique:pengaturan_dokumen,tahun,' . $id,
            'nomor_pks'                  => 'required|string|max:255',
            'nomor_spk'                  => 'required|string|max:255',
            'tanggal_pks_spk'            => 'required|date',
            'nama_bengkel'               => 'required|string|max:255',
            'alamat_bengkel'             => 'nullable|string|max:500',
            'nama_pimpinan_bengkel'      => 'nullable|string|max:255',
            'ttd_kpa_nama'               => 'nullable|string|max:255',
            'ttd_kpa_nip'                => 'nullable|string|max:50',
            'ttd_kpa_jabatan'            => 'nullable|string|max:255',
            'ttd_kpa_pangkat'            => 'nullable|string|max:100',
            'ttd_pptk_nama'              => 'nullable|string|max:255',
            'ttd_pptk_nip'               => 'nullable|string|max:50',
            'ttd_pptk_jabatan'           => 'nullable|string|max:255',
            'ttd_pptk_pangkat'           => 'nullable|string|max:100',
            'ttd_kabid_pemadam_nama'     => 'nullable|string|max:255',
            'ttd_kabid_pemadam_nip'      => 'nullable|string|max:50',
            'ttd_kabid_pemadam_jabatan'  => 'nullable|string|max:255',
            'ttd_kabid_rescue_nama'      => 'nullable|string|max:255',
            'ttd_kabid_rescue_nip'       => 'nullable|string|max:50',
            'ttd_kabid_rescue_jabatan'   => 'nullable|string|max:255',
            'ttd_kabid_pencegahan_nama'  => 'nullable|string|max:255',
            'ttd_kabid_pencegahan_nip'   => 'nullable|string|max:50',
            'ttd_kabid_pencegahan_jabatan'=> 'nullable|string|max:255',
        ]);

        $doc->update($validated);

        return redirect()->route('admin.pengaturan', ['tab' => 'dokumen'])
            ->with('success', 'Pengaturan dokumen tahun ' . $doc->tahun . ' berhasil diperbarui.');
    }

    /**
     * Hapus pengaturan dokumen
     */
    public function destroyPengaturanDokumen($id)
    {
        $doc = PengaturanDokumen::findOrFail($id);
        $tahun = $doc->tahun;
        $doc->delete();

        return redirect()->route('admin.pengaturan', ['tab' => 'dokumen'])
            ->with('success', 'Pengaturan dokumen tahun ' . $tahun . ' berhasil dihapus.');
    }

    /**
     * Hapus Data Pengecekan Unit
     */
    public function destroyCekHarianUnit($id)
    {
        $cekUnit = CekHarianUnit::findOrFail($id);
        $cekUnit->delete();

        return redirect()->back()->with('success', 'Data pengecekan unit berhasil dihapus.');
    }

    /**
     * Hapus Data Pengecekan Alat
     */
    public function destroyCekHarianAlat($id)
    {
        $cekAlat = CekHarianAlat::findOrFail($id);
        $cekAlat->delete();

        return redirect()->back()->with('success', 'Data pengecekan alat berhasil dihapus.');
    }
}
