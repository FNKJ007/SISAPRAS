<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CekHarianAlat;
use App\Models\CekHarianUnit;
use App\Models\Invoice;
use App\Models\Pengajuan;
use App\Models\PengaturanDokumen;
use App\Models\Unit;
use App\Models\User;
use App\Services\CacheService;
use App\Services\ExcelExportService;
use Carbon\Carbon;
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

        // 5. Absen Pengecekan Harian Unit & Peralatan (Cached for 15s to guarantee high responsiveness)
        $absenData = \Illuminate\Support\Facades\Cache::remember('admin_dashboard_absen_unit_alat_v3', 15, function () {
            $todayStart = now()->startOfDay()->toDateTimeString();
            $todayEnd   = now()->endOfDay()->toDateTimeString();
            $todayDate  = now()->format('Y-m-d');

            // --- A. Data Absen Pengecekan Unit Armada ---
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

            $sudahUnitCount = count(array_filter($absenUnitList, fn($u) => !empty($u['sudah_dicek'])));
            $absenSummary = [
                'total_unit'  => count($absenUnitList),
                'sudah_dicek' => $sudahUnitCount,
                'belum_dicek' => count($absenUnitList) - $sudahUnitCount,
            ];

            // --- B. Data Absen Pengecekan Peralatan Pos ---
            $todayCekAlats = \App\Models\CekHarianAlat::where(function ($q) use ($todayStart, $todayEnd, $todayDate) {
                    $q->whereBetween('created_at', [$todayStart, $todayEnd])
                      ->orWhere('tanggal_pemeriksaan', $todayDate);
                })
                ->latest('id')
                ->get();

            $allActivePos = \App\Models\Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get(['id', 'nama']);

            $absenAlatList = [];
            foreach ($allActivePos as $pos) {
                $requiredCats = \App\Models\Pos::getKategoriAlatByPos($pos->nama);
                $posName = $pos->nama;
                $cleanPos = trim(explode('(', $posName)[0]);
                $aliasInParen = '';
                if (preg_match('/\((.*?)\)/', $posName, $m)) {
                    $aliasInParen = trim($m[1]);
                }

                foreach ($requiredCats as $cat) {
                    $cek = $todayCekAlats->first(function ($item) use ($cat, $posName, $cleanPos, $aliasInParen) {
                        $itemKat = strtolower(trim((string)$item->kategori));
                        $targetKat = strtolower(trim((string)$cat));

                        $catMatch = ($itemKat === $targetKat)
                            || ($targetKat === 'command_center' && str_contains($itemKat, 'command'))
                            || ($targetKat === 'pemadam' && str_contains($itemKat, 'pemadam'))
                            || ($targetKat === 'rescue' && str_contains($itemKat, 'rescue'))
                            || ($targetKat === 'pencegahan' && str_contains($itemKat, 'pencegahan'));

                        if (!$catMatch) return false;

                        // Khusus Command Center di Pos Mako / Soreang
                        if ($targetKat === 'command_center' && (str_contains(strtolower($posName), 'soreang') || str_contains(strtolower($posName), 'mako'))) {
                            return true;
                        }

                        $itemPos = strtolower(trim((string)$item->pos));
                        if (empty($itemPos)) return false;

                        return str_contains($itemPos, strtolower($cleanPos))
                            || (!empty($aliasInParen) && str_contains($itemPos, strtolower($aliasInParen)))
                            || str_contains(strtolower($posName), $itemPos);
                    });

                    $catLabel = match(strtolower($cat)) {
                        'rescue'         => 'Alat Rescue',
                        'pencegahan'     => 'Alat Pencegahan',
                        'command_center' => 'Command Center',
                        default          => 'Alat Pemadam',
                    };

                    $absenAlatList[] = [
                        'pos'            => $posName,
                        'kategori'       => $cat,
                        'kategori_label' => $catLabel,
                        'sudah_dicek'    => $cek !== null,
                        'nama_pemeriksa' => $cek ? $cek->nama_pemeriksa : null,
                        'jabatan'        => $cek ? $cek->jabatan : null,
                        'total_baik'     => $cek ? ($cek->total_baik ?? 0) : 0,
                        'total_rusak'    => $cek ? ($cek->total_rusak ?? 0) : 0,
                        'waktu_cek'      => $cek && $cek->created_at ? $cek->created_at->format('H:i') : null,
                        'cek_id'         => $cek ? $cek->id : null,
                    ];
                }
            }

            $sudahAlatCount = count(array_filter($absenAlatList, fn($a) => !empty($a['sudah_dicek'])));
            $absenAlatSummary = [
                'total_pos_kategori' => count($absenAlatList),
                'sudah_dicek'        => $sudahAlatCount,
                'belum_dicek'        => count($absenAlatList) - $sudahAlatCount,
            ];

            return [
                'absenUnitList'     => $absenUnitList,
                'absenSummary'      => $absenSummary,
                'absenAlatList'     => $absenAlatList,
                'absenAlatSummary'  => $absenAlatSummary,
            ];
        });

        // =====================================================================
        // 6. Kalender Pengajuan Pemeliharaan (Semua Pos & Semua Unit)
        // =====================================================================
        Carbon::setLocale('id');

        $calendarMonth = (int) $request->query('month', $request->query('bulan', date('n')));
        $calendarYear  = (int) $request->query('year', $request->query('tahun', date('Y')));

        // Navigasi Bulan Kalender
        $calendarCurrentDate     = Carbon::createFromDate($calendarYear, $calendarMonth, 1);
        $calendarBulanAktif      = $calendarCurrentDate;
        $calendarBulanSebelumnya = $calendarCurrentDate->copy()->subMonth();
        $calendarBulanBerikutnya = $calendarCurrentDate->copy()->addMonth();

        $calendarPrevMonthUrl = route('admin.dashboard', [
            'month' => $calendarBulanSebelumnya->month,
            'year'  => $calendarBulanSebelumnya->year,
            'tahun' => $currentYear,
        ]);
        $calendarNextMonthUrl = route('admin.dashboard', [
            'month' => $calendarBulanBerikutnya->month,
            'year'  => $calendarBulanBerikutnya->year,
            'tahun' => $currentYear,
        ]);

        // Query SEMUA pengajuan pemeliharaan dari SELURUH POS & UNIT untuk bulan kalender aktif
        $calendarTglAwal  = $calendarCurrentDate->copy()->startOfMonth();
        $calendarTglAkhir = $calendarCurrentDate->copy()->endOfMonth();

        $dbPengajuanKalender = Pengajuan::where(function ($query) use ($calendarTglAwal, $calendarTglAkhir) {
            $query->whereBetween('tanggal_keberangkatan', [$calendarTglAwal->format('Y-m-d'), $calendarTglAkhir->format('Y-m-d')])
                  ->orWhereBetween('tanggal_selesai_pengerjaan', [$calendarTglAwal->format('Y-m-d'), $calendarTglAkhir->format('Y-m-d')])
                  ->orWhereBetween('created_at', [$calendarTglAwal->copy()->startOfDay(), $calendarTglAkhir->copy()->endOfDay()]);
        })
        ->latest('id')
        ->get();

        $todayStr = now()->format('Y-m-d');

        if ($dbPengajuanKalender->count() > 0) {
            $calendarPengajuanList = $dbPengajuanKalender->map(function ($item) use ($todayStr) {
                // Tanggal penempatan di kalender: Gunakan tanggal_keberangkatan jika ada, atau created_at
                $tglTarget = ($item->tanggal_keberangkatan)
                    ? $item->tanggal_keberangkatan->toDateString()
                    : $item->created_at->toDateString();

                $itemVerificatedList = [];
                if (!empty($item->item_verifikasis) && is_array($item->item_verifikasis)) {
                    foreach ($item->item_verifikasis as $itemName => $itemStatus) {
                        $itemVerificatedList[] = [
                            'nama'   => $itemName,
                            'status' => $itemStatus, // 'disetujui' atau 'ditolak'
                        ];
                    }
                }

                $tglBerangkatStr = $item->tanggal_keberangkatan ? $item->tanggal_keberangkatan->format('Y-m-d') : null;

                if ($item->status === 'selesai' || $item->status_pengerjaan === 'selesai') {
                    $statusKalender = 'selesai';
                    $statusLabel    = 'Selesai';
                } elseif ($item->status === 'disetujui') {
                    if ($tglBerangkatStr && $tglBerangkatStr > $todayStr) {
                        $statusKalender = 'disetujui_ke_bengkel';
                        $statusLabel    = 'Disetujui ke Bengkel';
                    } else {
                        $statusKalender = 'dalam_perbaikan';
                        $statusLabel    = 'Dalam Perbaikan';
                    }
                } elseif ($item->status === 'ditolak') {
                    $statusKalender = 'ditolak';
                    $statusLabel    = 'Ditolak Admin';
                } else {
                    $statusKalender = 'menunggu';
                    $statusLabel    = 'Menunggu Verifikasi';
                }

                return (object) [
                    'id'                    => $item->id,
                    'tanggal_pengajuan'     => $tglTarget,
                    'unit_nama'             => strtoupper($item->nomor_lambung ?? 'Unit') . ($item->pos ? ' (' . ucfirst($item->pos) . ')' : ''),
                    'nomor_lambung'         => $item->nomor_lambung,
                    'pos'                   => $item->pos,
                    'status'                => $item->status,
                    'status_kalender'       => $statusKalender,
                    'status_label'          => $statusLabel,
                    'item_perbaikan'        => $item->item_perbaikan,
                    'item_verifikasis'      => $itemVerificatedList,
                    'tanggal_keberangkatan' => $item->tanggal_keberangkatan ? $item->tanggal_keberangkatan->translatedFormat('l, d F Y') : null,
                    'tanggal_selesai'       => $item->tanggal_selesai_pengerjaan ? $item->tanggal_selesai_pengerjaan->translatedFormat('l, d F Y') : null,
                    'catatan_admin'         => $item->catatan_admin,
                ];
            });
        } else {
            $calendarPengajuanList = collect([]);
        }

        // Grouping data pengajuan berdasarkan tanggal_pengajuan ('Y-m-d')
        $calendarEventsByDate = $calendarPengajuanList->groupBy('tanggal_pengajuan');

        // Bangun Grid Minggu Kalender (Minggu s.d. Sabtu)
        $startOfCalendar = $calendarCurrentDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar   = $calendarCurrentDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $calendarWeeks = [];
        $dayCursor = $startOfCalendar->copy();
        while ($dayCursor->lte($endOfCalendar)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $dayCursor->copy();
                $dayCursor->addDay();
            }
            $calendarWeeks[] = $week;
        }

        // Hitung Ringkasan Status Pengajuan Bulan Ini
        $calendarRingkasan = [
            'total_pengajuan' => $calendarPengajuanList->count(),
            'menunggu'        => $calendarPengajuanList->where('status_kalender', 'menunggu')->count(),
            'disetujui'       => $calendarPengajuanList->whereIn('status_kalender', ['disetujui_ke_bengkel', 'dalam_perbaikan'])->count(),
            'selesai'         => $calendarPengajuanList->where('status_kalender', 'selesai')->count(),
            'ditolak'         => $calendarPengajuanList->where('status_kalender', 'ditolak')->count(),
        ];

        // Range Tahun Dinamis
        $minDbYear = Pengajuan::min('created_at') ? Carbon::parse(Pengajuan::min('created_at'))->year : date('Y') - 5;
        $startYear = min(2020, $minDbYear);
        $endYear   = max((int) date('Y') + 10, $calendarYear + 5);
        $calendarAvailableYears = range($startYear, $endYear);

        return view('admin.dashboard', array_merge($stats, $absenData, [
            'currentYear'             => $currentYear,
            'calendarMonth'           => $calendarMonth,
            'calendarYear'            => $calendarYear,
            'calendarCurrentDate'     => $calendarCurrentDate,
            'calendarBulanAktif'      => $calendarBulanAktif,
            'calendarBulanSebelumnya'  => $calendarBulanSebelumnya,
            'calendarBulanBerikutnya'  => $calendarBulanBerikutnya,
            'calendarPrevMonthUrl'    => $calendarPrevMonthUrl,
            'calendarNextMonthUrl'    => $calendarNextMonthUrl,
            'calendarWeeks'           => $calendarWeeks,
            'calendarEventsByDate'    => $calendarEventsByDate,
            'calendarRingkasan'       => $calendarRingkasan,
            'calendarAvailableYears'  => $calendarAvailableYears,
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

        // Ambil pengaturan dokumen (PKS/SPK/Bengkel) dari database berdasarkan tahun dokumen
        $tahunDoc = (int) ($pengajuan->created_at ? $pengajuan->created_at->format('Y') : date('Y'));
        $pengaturanDokumen = PengaturanDokumen::getAktif($tahunDoc);

        return view('admin.pemeliharaan.cetak-dokumen', compact('pengajuan', 'type', 'title', 'pengaturanDokumen'));
    }

    public function pemeliharaanMonitoringAktual(Request $request)
    {
        return redirect()->route('admin.pemeliharaan.spj-pembayaran.index');
    }


    /**
    /**
     * Kartu Kendali Aktual Pemeliharaan — Matriks biaya pemeliharaan armada
     * per unit dan per bulan (12 bulan) dalam satu tahun anggaran berdasarkan Aktual Pembayaran.
     */
    public function pemeliharaanKartuKendaliAktual(Request $request)
    {
        $existingYears = \App\Models\Invoice::whereNotNull('tahun_anggaran')
            ->where('kategori_monitoring', 'aktual')
            ->pluck('tahun_anggaran')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->toArray();

        $currentYear = (int) date('Y');
        $defaultYears = range($currentYear - 2, $currentYear + 2);
        $tahunList = collect(array_unique(array_merge($existingYears, $defaultYears)))
            ->sortDesc()
            ->values();

        $tahunFilter    = (string) $request->query('tahun', in_array($currentYear, $tahunList->toArray()) ? $currentYear : ($tahunList->first() ?? date('Y')));
        $searchQuery    = $request->query('search', '');

        // Ambil semua armada unit dengan pengurutan standar Damkar
        $units = \App\Models\Unit::all()->sort(function ($a, $b) {
            $getPriority = function ($code) {
                $code = strtoupper(trim($code ?? ''));
                if (str_starts_with($code, 'P-')) return 1;
                if (str_starts_with($code, 'R-')) return 2;
                if (str_starts_with($code, 'S-')) return 3;
                if (str_starts_with($code, 'PC-')) return 4;
                if (str_starts_with($code, 'MP-')) return 5;
                if (str_starts_with($code, 'K-')) return 6;
                return 99;
            };
            $pA = $getPriority($a->nomor_lambung);
            $pB = $getPriority($b->nomor_lambung);
            if ($pA !== $pB) return $pA <=> $pB;
            return strnatcasecmp($a->nomor_lambung ?? '', $b->nomor_lambung ?? '');
        });

        // Ambil data invoice pembayaran untuk tahun yang dipilih
        $invoices = \App\Models\Invoice::with('unit')
            ->where('kategori_monitoring', 'aktual')
            ->where(function ($q) use ($tahunFilter) {
                $q->where('tahun_anggaran', $tahunFilter)
                  ->orWhereYear('tanggal_invoice', $tahunFilter);
            })
            ->get();

        $matrixRows = [];
        $monthlyTotals = array_fill(1, 12, 0);
        $grandTotal = 0;
        $totalUnitsServed = 0;
        $lastPrefix = null;

        foreach ($units as $unit) {
            if (!empty($searchQuery)) {
                $q = strtolower($searchQuery);
                if (
                    !str_contains(strtolower($unit->nomor_lambung ?? ''), $q) &&
                    !str_contains(strtolower($unit->plat_nomor ?? ''), $q) &&
                    !str_contains(strtolower($unit->nama ?? ''), $q)
                ) {
                    continue;
                }
            }

            $currCode = strtoupper(trim($unit->nomor_lambung ?? ''));
            $currPrefix = explode('-', $currCode)[0] ?? '';
            $isNewGroup = ($lastPrefix !== null && $lastPrefix !== $currPrefix);
            $lastPrefix = $currPrefix;

            // Cari invoice yang terkait dengan unit ini
            $unitInvoices = $invoices->filter(function ($inv) use ($unit) {
                if ($inv->unit_id && $inv->unit_id == $unit->id) return true;
                if (!empty($inv->no_lambung) && strtoupper(trim($inv->no_lambung)) === strtoupper(trim($unit->nomor_lambung ?? ''))) return true;
                return false;
            });

            $months = [];
            $unitTotal = 0;
            for ($m = 1; $m <= 12; $m++) {
                $mInvoices = $unitInvoices->filter(function ($inv) use ($m) {
                    return $inv->tanggal_invoice && (int) $inv->tanggal_invoice->format('n') === $m;
                });
                $mCost = (float) $mInvoices->sum('total_biaya');
                $months[$m] = $mCost;
                $unitTotal += $mCost;
                $monthlyTotals[$m] += $mCost;
            }

            if ($unitTotal > 0) {
                $totalUnitsServed++;
            }
            $grandTotal += $unitTotal;

            $matrixRows[] = [
                'unit'          => $unit,
                'no_lambung'    => $unit->nomor_lambung ?? '—',
                'tnkb'          => $unit->plat_nomor ?? $unit->no_rangka_mesin ?? '—',
                'months'        => $months,
                'total'         => $unitTotal,
                'invoice_count' => $unitInvoices->count(),
                'is_new_group'  => $isNewGroup,
            ];
        }

        $totalInvoice = $invoices->count();
        $rataRata = $totalUnitsServed > 0 ? ($grandTotal / $totalUnitsServed) : 0;

        $kpi = [
            'total_invoice'  => $totalInvoice,
            'total_nilai'    => $grandTotal,
            'total_unit'     => $totalUnitsServed,
            'total_all_unit' => count($matrixRows),
            'rata_rata'      => $rataRata,
        ];

        $pejabatKasi = \App\Models\User::where('jabatan', 'ILIKE', '%pemeliharaan sarana%')
            ->orWhere('jabatan', 'ILIKE', '%seksi pemeliharaan%')
            ->orWhere('jabatan', 'ILIKE', '%pemeliharaan%')
            ->first();

        return view('admin.pemeliharaan.kartu-kendali-aktual', [
            'matrixRows'    => $matrixRows,
            'monthlyTotals' => $monthlyTotals,
            'grandTotal'    => $grandTotal,
            'kpi'           => $kpi,
            'tahunList'     => $tahunList,
            'tahunFilter'   => $tahunFilter,
            'searchQuery'   => $searchQuery,
            'pejabatKasi'   => $pejabatKasi,
        ]);
    }

    /**
     * Kartu Kendali SPJ Pemeliharaan — Matriks realisasi pemeliharaan armada
     * berdasarkan data SPJ Pembayaran per unit dan per bulan.
     */
    public function pemeliharaanKartuKendaliSpj(Request $request)
    {
        $existingYears = \App\Models\Invoice::whereNotNull('tahun_anggaran')
            ->where(function ($q) {
                $q->where('kategori_monitoring', 'invoice')
                  ->orWhereNull('kategori_monitoring');
            })
            ->pluck('tahun_anggaran')
            ->filter()
            ->map(fn($y) => (int)$y)
            ->toArray();

        $currentYear = (int) date('Y');
        $defaultYears = range($currentYear - 2, $currentYear + 2);
        $tahunList = collect(array_unique(array_merge($existingYears, $defaultYears)))
            ->sortDesc()
            ->values();

        $tahunFilter    = (string) $request->query('tahun', in_array($currentYear, $tahunList->toArray()) ? $currentYear : ($tahunList->first() ?? date('Y')));
        $searchQuery    = $request->query('search', '');

        // Ambil semua armada unit dengan pengurutan standar Damkar
        $units = \App\Models\Unit::all()->sort(function ($a, $b) {
            $getPriority = function ($code) {
                $code = strtoupper(trim($code ?? ''));
                if (str_starts_with($code, 'P-')) return 1;
                if (str_starts_with($code, 'R-')) return 2;
                if (str_starts_with($code, 'S-')) return 3;
                if (str_starts_with($code, 'PC-')) return 4;
                if (str_starts_with($code, 'MP-')) return 5;
                if (str_starts_with($code, 'K-')) return 6;
                return 99;
            };
            $pA = $getPriority($a->nomor_lambung);
            $pB = $getPriority($b->nomor_lambung);
            if ($pA !== $pB) return $pA <=> $pB;
            return strnatcasecmp($a->nomor_lambung ?? '', $b->nomor_lambung ?? '');
        });

        // Ambil data invoice SPJ pembayaran untuk tahun yang dipilih
        $invoices = \App\Models\Invoice::with('unit')
            ->where(function ($q) {
                $q->where('kategori_monitoring', 'invoice')
                  ->orWhereNull('kategori_monitoring');
            })
            ->where(function ($q) use ($tahunFilter) {
                $q->where('tahun_anggaran', $tahunFilter)
                  ->orWhereYear('tanggal_invoice', $tahunFilter);
            })
            ->get();

        $matrixRows = [];
        $monthlyTotals = array_fill(1, 12, 0);
        $grandTotal = 0;
        $totalUnitsServed = 0;
        $lastPrefix = null;

        foreach ($units as $unit) {
            if (!empty($searchQuery)) {
                $q = strtolower($searchQuery);
                if (
                    !str_contains(strtolower($unit->nomor_lambung ?? ''), $q) &&
                    !str_contains(strtolower($unit->plat_nomor ?? ''), $q) &&
                    !str_contains(strtolower($unit->nama ?? ''), $q)
                ) {
                    continue;
                }
            }

            $currCode = strtoupper(trim($unit->nomor_lambung ?? ''));
            $currPrefix = explode('-', $currCode)[0] ?? '';
            $isNewGroup = ($lastPrefix !== null && $lastPrefix !== $currPrefix);
            $lastPrefix = $currPrefix;

            // Cari invoice yang terkait dengan unit ini
            $unitInvoices = $invoices->filter(function ($inv) use ($unit) {
                if ($inv->unit_id && $inv->unit_id == $unit->id) return true;
                if (!empty($inv->no_lambung) && strtoupper(trim($inv->no_lambung)) === strtoupper(trim($unit->nomor_lambung ?? ''))) return true;
                return false;
            });

            $months = [];
            $unitTotal = 0;
            for ($m = 1; $m <= 12; $m++) {
                $mInvoices = $unitInvoices->filter(function ($inv) use ($m) {
                    return $inv->tanggal_invoice && (int) $inv->tanggal_invoice->format('n') === $m;
                });
                $mCost = (float) $mInvoices->sum('total_biaya');
                $months[$m] = $mCost;
                $unitTotal += $mCost;
                $monthlyTotals[$m] += $mCost;
            }

            if ($unitTotal > 0) {
                $totalUnitsServed++;
            }
            $grandTotal += $unitTotal;

            $matrixRows[] = [
                'unit'          => $unit,
                'no_lambung'    => $unit->nomor_lambung ?? '—',
                'tnkb'          => $unit->plat_nomor ?? $unit->no_rangka_mesin ?? '—',
                'months'        => $months,
                'total'         => $unitTotal,
                'invoice_count' => $unitInvoices->count(),
                'is_new_group'  => $isNewGroup,
            ];
        }

        $totalInvoice = $invoices->count();
        $rataRata = $totalUnitsServed > 0 ? ($grandTotal / $totalUnitsServed) : 0;

        $kpi = [
            'total_invoice'  => $totalInvoice,
            'total_nilai'    => $grandTotal,
            'total_unit'     => $totalUnitsServed,
            'total_all_unit' => count($matrixRows),
            'rata_rata'      => $rataRata,
        ];

        $pejabatKasi = \App\Models\User::where('jabatan', 'ILIKE', '%pemeliharaan sarana%')
            ->orWhere('jabatan', 'ILIKE', '%seksi pemeliharaan%')
            ->orWhere('jabatan', 'ILIKE', '%pemeliharaan%')
            ->first();

        return view('admin.pemeliharaan.kartu-kendali-spj', [
            'matrixRows'    => $matrixRows,
            'monthlyTotals' => $monthlyTotals,
            'grandTotal'    => $grandTotal,
            'kpi'           => $kpi,
            'tahunList'     => $tahunList,
            'tahunFilter'   => $tahunFilter,
            'searchQuery'   => $searchQuery,
            'pejabatKasi'   => $pejabatKasi,
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

        // Data pejabat TTD aktif saat ini (KPA, PPTK, Kabid) dari Data Pegawai
        $pejabatTtdPreview = User::getPejabatTtd();

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
            'pengaturanDokumenList',
            'pejabatTtdPreview'
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

    /* =====================================================================
     *  EXPORT EXCEL REKAPITULASI (.XLSX)
     * ===================================================================== */

    /**
     * Export Rekap Pengajuan Pemeliharaan ke Excel
     */
    public function exportExcelPengajuan(Request $request)
    {
        $statusFilter = $request->query('status', 'semua');
        $searchQuery  = $request->query('search', '');

        $query = Pengajuan::latest();

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

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nomor_lambung', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $list = $query->get();

        [$spreadsheet, $sheet, $startRow] = ExcelExportService::createWithHeader(
            'REKAPITULASI PENGAJUAN PEMELIHARAAN UNIT',
            'Filter: Status ' . strtoupper($statusFilter) . ' | Dicetak: ' . date('d/m/Y H:i') . ' WIB'
        );

        $headers = [
            'NO',
            'KODE VERIFIKASI',
            'TANGGAL PENGAJUAN',
            'POS PENEMPATAN',
            'REGU',
            'NAMA PEMEGANG / PEMOHON',
            'NIP PEMEGANG',
            'JENIS KENDARAAN',
            'NO. LAMBUNG',
            'ITEM / URAIAN KERUSAKAN',
            'STATUS PENGAJUAN',
            'TANGGAL BERANGKAT BENGKEL',
            'TANGGAL SELESAI / KEMBALI',
            'STATUS PENGERJAAN',
            'CATATAN ADMIN',
        ];

        ExcelExportService::setTableHeaders($sheet, $startRow, $headers);

        $row = $startRow + 1;
        foreach ($list as $idx => $p) {
            $tgl = $p->created_at ? $p->created_at->translatedFormat('d/m/Y H:i') : '—';
            $tglBerangkat = $p->tanggal_keberangkatan ? Carbon::parse($p->tanggal_keberangkatan)->translatedFormat('d/m/Y') : '—';
            $tglSelesai = $p->tanggal_selesai_pengerjaan ? Carbon::parse($p->tanggal_selesai_pengerjaan)->translatedFormat('d/m/Y') : '—';
            $items = is_array($p->item_list) ? implode(', ', $p->item_list) : str_replace("\n", ', ', $p->item_perbaikan ?? '—');

            $colIdx = 1;
            $sheet->setCellValue([$colIdx++, $row], $idx + 1);
            $sheet->setCellValue([$colIdx++, $row], $p->kode_verifikasi ?? ('HAR-' . ($p->created_at ? $p->created_at->format('Ymd') : date('Ymd')) . '-' . sprintf('%04d', $p->id)));
            $sheet->setCellValue([$colIdx++, $row], $tgl);
            $sheet->setCellValue([$colIdx++, $row], $p->pos ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->regu ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nama_pemegang ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nip_pemegang ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->jenis_kendaraan ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nomor_lambung ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $items);
            $sheet->setCellValue([$colIdx++, $row], strtoupper($p->status ?? 'menunggu'));
            $sheet->setCellValue([$colIdx++, $row], $tglBerangkat);
            $sheet->setCellValue([$colIdx++, $row], $tglSelesai);
            $sheet->setCellValue([$colIdx++, $row], strtoupper($p->status_pengerjaan ?? '—'));
            $sheet->setCellValue([$colIdx++, $row], $p->catatan_admin ?? '');

            $row++;
        }

        $endDataRow = $row - 1;
        $totalCols = count($headers);

        if ($endDataRow >= $startRow + 1) {
            ExcelExportService::styleDataRows(
                $sheet,
                $startRow + 1,
                $endDataRow,
                $totalCols,
                [],
                [1, 2, 3, 4, 5, 8, 9, 11, 12, 13, 14]
            );
        }

        ExcelExportService::autoFitColumns($sheet, $totalCols);

        return ExcelExportService::streamDownload($spreadsheet, 'Rekap_Pengajuan_Pemeliharaan_' . date('Ymd_His'));
    }

    /**
     * Export Rekap Surat Permohonan Pemeliharaan ke Excel
     */
    public function exportExcelPemeliharaan(Request $request)
    {
        $search = $request->query('search', '');

        $query = Pengajuan::where('status', 'disetujui')->latest();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_lambung', 'ILIKE', "%{$search}%")
                  ->orWhere('pos', 'ILIKE', "%{$search}%")
                  ->orWhere('nama_pemegang', 'ILIKE', "%{$search}%")
                  ->orWhere('item_perbaikan', 'ILIKE', "%{$search}%");
            });
        }

        $list = $query->get();

        [$spreadsheet, $sheet, $startRow] = ExcelExportService::createWithHeader(
            'REKAPITULASI SURAT PERMOHONAN & PESANAN PEMELIHARAAN',
            'Data Pengajuan Terverifikasi Disetujui | Dicetak: ' . date('d/m/Y H:i') . ' WIB'
        );

        $headers = [
            'NO',
            'KODE VERIFIKASI',
            'TANGGAL DISETUJUI',
            'BIDANG',
            'POS',
            'REGU',
            'JENIS KENDARAAN',
            'NO. LAMBUNG',
            'NAMA PEMEGANG',
            'NIP PEMEGANG',
            'KOMANDAN REGU',
            'KEPALA BIDANG',
            'URAIAN PERBAIKAN / PESANAN',
            'TANGGAL BERANGKAT',
            'STATUS PENGERJAAN',
            'CATATAN ADMIN',
        ];

        ExcelExportService::setTableHeaders($sheet, $startRow, $headers);

        $row = $startRow + 1;
        foreach ($list as $idx => $p) {
            $tgl = $p->created_at ? $p->created_at->translatedFormat('d/m/Y') : '—';
            $tglBerangkat = $p->tanggal_keberangkatan ? Carbon::parse($p->tanggal_keberangkatan)->translatedFormat('d/m/Y') : '—';
            $items = is_array($p->item_list) ? implode(', ', $p->item_list) : str_replace("\n", ', ', $p->item_perbaikan ?? '—');

            $colIdx = 1;
            $sheet->setCellValue([$colIdx++, $row], $idx + 1);
            $sheet->setCellValue([$colIdx++, $row], $p->kode_verifikasi ?? ('HAR-' . ($p->created_at ? $p->created_at->format('Ymd') : date('Ymd')) . '-' . sprintf('%04d', $p->id)));
            $sheet->setCellValue([$colIdx++, $row], $tgl);
            $sheet->setCellValue([$colIdx++, $row], $p->bidang ?? 'Pemadam');
            $sheet->setCellValue([$colIdx++, $row], $p->pos ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->regu ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->jenis_kendaraan ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nomor_lambung ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nama_pemegang ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nip_pemegang ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nama_komandan_regu ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $p->nama_kepala_bidang ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $items);
            $sheet->setCellValue([$colIdx++, $row], $tglBerangkat);
            $sheet->setCellValue([$colIdx++, $row], strtoupper($p->status_pengerjaan ?? 'DISETUJUI'));
            $sheet->setCellValue([$colIdx++, $row], $p->catatan_admin ?? '');

            $row++;
        }

        $endDataRow = $row - 1;
        $totalCols = count($headers);

        if ($endDataRow >= $startRow + 1) {
            ExcelExportService::styleDataRows(
                $sheet,
                $startRow + 1,
                $endDataRow,
                $totalCols,
                [],
                [1, 2, 3, 4, 5, 6, 7, 8, 14, 15]
            );
        }

        ExcelExportService::autoFitColumns($sheet, $totalCols);

        return ExcelExportService::streamDownload($spreadsheet, 'Rekap_Surat_Permohonan_' . date('Ymd_His'));
    }

    /**
     * Export Matriks Kartu Kendali Aktual ke Excel
     */
    public function exportExcelKartuKendaliAktual(Request $request)
    {
        return $this->exportKartuKendaliMatrix($request, 'aktual', 'REKAP KARTU KENDALI PEMELIHARAAN (AKTUAL)', 'Kartu_Kendali_Aktual_');
    }

    /**
     * Export Matriks Kartu Kendali SPJ ke Excel
     */
    public function exportExcelKartuKendaliSpj(Request $request)
    {
        return $this->exportKartuKendaliMatrix($request, 'spj', 'REKAP KARTU KENDALI PEMELIHARAAN (SPJ)', 'Kartu_Kendali_SPJ_');
    }

    private function exportKartuKendaliMatrix(Request $request, string $type, string $title, string $filePrefix)
    {
        $currentYear = (int) date('Y');
        $tahunFilter = (string) $request->query('tahun', (string) $currentYear);
        $searchQuery = $request->query('search', '');

        $units = Unit::all()->sort(function ($a, $b) {
            $getPriority = function ($code) {
                $code = strtoupper(trim($code ?? ''));
                if (str_starts_with($code, 'P-')) return 1;
                if (str_starts_with($code, 'R-')) return 2;
                if (str_starts_with($code, 'S-')) return 3;
                if (str_starts_with($code, 'PC-')) return 4;
                if (str_starts_with($code, 'MP-')) return 5;
                if (str_starts_with($code, 'K-')) return 6;
                return 99;
            };
            $pA = $getPriority($a->nomor_lambung);
            $pB = $getPriority($b->nomor_lambung);
            if ($pA !== $pB) return $pA <=> $pB;
            return strnatcasecmp($a->nomor_lambung ?? '', $b->nomor_lambung ?? '');
        });

        $isAktual = ($type === 'aktual');

        $invoices = Invoice::with('unit')
            ->where(function ($q) use ($isAktual) {
                if ($isAktual) {
                    $q->where('kategori_monitoring', 'aktual');
                } else {
                    $q->where('kategori_monitoring', 'invoice')
                      ->orWhereNull('kategori_monitoring');
                }
            })
            ->where(function ($q) use ($tahunFilter) {
                $q->where('tahun_anggaran', $tahunFilter)
                  ->orWhereYear('tanggal_invoice', $tahunFilter);
            })
            ->get();

        [$spreadsheet, $sheet, $startRow] = ExcelExportService::createWithHeader(
            $title,
            'Tahun Anggaran: ' . $tahunFilter . ' | Dicetak: ' . date('d/m/Y H:i') . ' WIB'
        );

        $headers = [
            'NO',
            'NO. LAMBUNG',
            'MERK / TIPE ARMADA',
            'NO. POLISI',
            'POS PENEMPATAN',
            'JAN',
            'FEB',
            'MAR',
            'APR',
            'MEI',
            'JUN',
            'JUL',
            'AGU',
            'SEP',
            'OKT',
            'NOV',
            'DES',
            'TOTAL',
        ];

        ExcelExportService::setTableHeaders($sheet, $startRow, $headers);

        $row = $startRow + 1;
        $monthlyTotals = array_fill(1, 12, 0);
        $grandTotal = 0;
        $no = 1;

        foreach ($units as $unit) {
            if (!empty($searchQuery)) {
                $q = strtolower($searchQuery);
                if (
                    !str_contains(strtolower($unit->nomor_lambung ?? ''), $q) &&
                    !str_contains(strtolower($unit->plat_nomor ?? ''), $q) &&
                    !str_contains(strtolower($unit->nama ?? ''), $q)
                ) {
                    continue;
                }
            }

            $uInvoices = $invoices->filter(function ($inv) use ($unit) {
                return $inv->unit_id == $unit->id ||
                       (strtolower(trim($inv->no_lambung ?? '')) === strtolower(trim($unit->nomor_lambung ?? '')) && !empty($unit->nomor_lambung));
            });

            $months = array_fill(1, 12, 0);
            foreach ($uInvoices as $inv) {
                $m = $inv->tanggal_invoice ? (int) date('n', strtotime($inv->tanggal_invoice)) : 0;
                if ($m >= 1 && $m <= 12) {
                    $months[$m] += (float) ($inv->total_biaya ?? 0);
                }
            }

            $rowTotal = array_sum($months);
            $grandTotal += $rowTotal;
            for ($m = 1; $m <= 12; $m++) {
                $monthlyTotals[$m] += $months[$m];
            }

            $colIdx = 1;
            $sheet->setCellValue([$colIdx++, $row], $no++);
            $sheet->setCellValue([$colIdx++, $row], $unit->nomor_lambung ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $unit->merk_tipe ?? ($unit->nama ?? '—'));
            $sheet->setCellValue([$colIdx++, $row], $unit->plat_nomor ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $unit->pos ?? '—');

            for ($m = 1; $m <= 12; $m++) {
                $sheet->setCellValue([$colIdx++, $row], $months[$m]);
            }
            $sheet->setCellValue([$colIdx++, $row], $rowTotal);

            $row++;
        }

        $endDataRow = $row - 1;
        $totalCols = count($headers);

        if ($endDataRow >= $startRow + 1) {
            $currencyCols = range(6, 18);
            $centerCols   = [1, 2, 4, 5];

            ExcelExportService::styleDataRows(
                $sheet,
                $startRow + 1,
                $endDataRow,
                $totalCols,
                $currencyCols,
                $centerCols
            );

            // Row Total / Ringkasan
            $totalRow = $row;
            $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", "TOTAL PENGELUARAN TAHUN {$tahunFilter}");
            
            $colIdx = 6;
            for ($m = 1; $m <= 12; $m++) {
                $sheet->setCellValue([$colIdx++, $totalRow], $monthlyTotals[$m]);
            }
            $sheet->setCellValue([$colIdx++, $totalRow], $grandTotal);

            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
            $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '94A3B8'],
                    ],
                ],
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$totalRow}:{$lastColLetter}{$totalRow}")->getNumberFormat()->setFormatCode('"Rp "#,##0');
        }

        ExcelExportService::autoFitColumns($sheet, $totalCols);

        return ExcelExportService::streamDownload($spreadsheet, $filePrefix . $tahunFilter . '_' . date('Ymd_His'));
    }

    /**
     * Export Pengecekan Unit & Alat Handlers
     */
    public function exportExcelCekUnitPemadam(Request $request)
    {
        return $this->exportCekUnit($request, 'pemadam', 'REKAP PENGECEKAN HARIAN UNIT PEMADAM', 'Rekap_Cek_Unit_Pemadam_');
    }

    public function exportExcelCekAlatPemadam(Request $request)
    {
        return $this->exportCekAlat($request, 'pemadam', 'REKAP PENGECEKAN HARIAN PERALATAN PEMADAM', 'Rekap_Cek_Alat_Pemadam_');
    }

    public function exportExcelCekUnitRescue(Request $request)
    {
        return $this->exportCekUnit($request, 'rescue', 'REKAP PENGECEKAN HARIAN UNIT RESCUE', 'Rekap_Cek_Unit_Rescue_');
    }

    public function exportExcelCekAlatRescue(Request $request)
    {
        return $this->exportCekAlat($request, 'rescue', 'REKAP PENGECEKAN HARIAN PERALATAN RESCUE', 'Rekap_Cek_Alat_Rescue_');
    }

    public function exportExcelCekUnitPencegahan(Request $request)
    {
        return $this->exportCekUnit($request, 'pencegahan', 'REKAP PENGECEKAN HARIAN UNIT PENCEGAHAN', 'Rekap_Cek_Unit_Pencegahan_');
    }

    public function exportExcelCekAlatPencegahan(Request $request)
    {
        return $this->exportCekAlat($request, 'pencegahan', 'REKAP PENGECEKAN HARIAN PERALATAN PENCEGAHAN', 'Rekap_Cek_Alat_Pencegahan_');
    }

    public function exportExcelCekAlatCommandCenter(Request $request)
    {
        return $this->exportCekAlat($request, 'command_center', 'REKAP PENGECEKAN PERALATAN COMMAND CENTER', 'Rekap_Cek_Alat_Command_Center_');
    }

    private function exportCekUnit(Request $request, string $kategori, string $title, string $filePrefix)
    {
        $searchQuery = $request->query('search', '');

        $query = CekHarianUnit::with(['user', 'unit'])->where(function ($q) use ($kategori) {
            if ($kategori === 'pemadam') {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            } else {
                $q->where('kategori', $kategori);
            }
        });

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $list = $query->latest()->get();

        [$spreadsheet, $sheet, $startRow] = ExcelExportService::createWithHeader(
            $title,
            'Dicetak pada: ' . date('d/m/Y H:i') . ' WIB'
        );

        $headers = [
            'NO',
            'TANGGAL & WAKTU CEK',
            'POS PENEMPATAN',
            'REGU',
            'NAMA UNIT / ARMADA',
            'NAMA PEMERIKSA',
            'KILOMETER (KM)',
            'BBM & AIR TANGKI',
            'KEBERSIHAN',
            'STATUS UNIT',
            'JUMLAH RUSAK',
            'RINCIAN / CATATAN KERUSAKAN',
        ];

        ExcelExportService::setTableHeaders($sheet, $startRow, $headers);

        $row = $startRow + 1;
        $totalRusakUnit = 0;

        foreach ($list as $idx => $item) {
            $tgl = $item->created_at ? $item->created_at->translatedFormat('d/m/Y H:i') : ($item->tanggal_pemeriksaan ? $item->tanggal_pemeriksaan->format('d/m/Y') : '—');
            $status = ($item->jumlah_rusak > 0) ? 'ADA KERUSAKAN' : 'SIAP OPERASI';
            $regu = $item->user?->regu ?? ($item->regu ?? '—');
            $unitName = $item->unit_nama ?? ($item->unit?->nama ?? ($item->nomor_lambung ?? '—'));
            $pemeriksa = $item->nama_pemeriksa ?? ($item->user?->name ?? '—');
            $km = $item->kilometer ?? '—';

            // BBM & Air info
            $bbmStr = !empty($item->jenis_bbm) ? ucfirst($item->jenis_bbm) : (!empty($item->level_bbm) ? (CekHarianUnit::$levelMap[$item->level_bbm] ?? $item->level_bbm) : '—');
            if (!empty($item->jenis_bbm) && !empty($item->level_bbm)) {
                $bbmStr .= ' (' . (CekHarianUnit::$levelMap[$item->level_bbm] ?? $item->level_bbm) . ')';
            }
            $airLevel = !empty($item->level_air) ? (CekHarianUnit::$levelMap[$item->level_air] ?? ucfirst($item->level_air)) : '—';
            $bbmAir = "BBM: {$bbmStr} | Air: {$airLevel}";

            $kebersihan = $item->kebersihan_unit ? ucfirst($item->kebersihan_unit) : '—';
            $jmlRusak = (int) ($item->jumlah_rusak ?? 0);
            $totalRusakUnit += $jmlRusak;

            // Rincian kerusakan dari perlengkapan
            $rincianList = [];
            if (!empty($item->perlengkapan) && is_array($item->perlengkapan)) {
                foreach ($item->perlengkapan as $k => $comp) {
                    if (($comp['status'] ?? '') === 'rusak') {
                        $label = $comp['label'] ?? ucwords(str_replace('_', ' ', $k));
                        $catatan = !empty($comp['catatan']) ? " ({$comp['catatan']})" : "";
                        $rincianList[] = $label . $catatan;
                    }
                }
            }
            if (!empty($item->catatan_tangki_pompa)) {
                $rincianList[] = "Pompa/Tangki: " . $item->catatan_tangki_pompa;
            }
            $rincian = !empty($rincianList) ? implode('; ', $rincianList) : '—';

            $colIdx = 1;
            $sheet->setCellValue([$colIdx++, $row], $idx + 1);
            $sheet->setCellValue([$colIdx++, $row], $tgl);
            $sheet->setCellValue([$colIdx++, $row], $item->pos ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $regu);
            $sheet->setCellValue([$colIdx++, $row], $unitName);
            $sheet->setCellValue([$colIdx++, $row], $pemeriksa);
            $sheet->setCellValue([$colIdx++, $row], $km);
            $sheet->setCellValue([$colIdx++, $row], $bbmAir);
            $sheet->setCellValue([$colIdx++, $row], $kebersihan);
            $sheet->setCellValue([$colIdx++, $row], $status);
            $sheet->setCellValue([$colIdx++, $row], $jmlRusak);
            $sheet->setCellValue([$colIdx++, $row], $rincian);

            $row++;
        }

        $endDataRow = $row - 1;
        $totalCols = count($headers);

        if ($endDataRow >= $startRow + 1) {
            ExcelExportService::styleDataRows(
                $sheet,
                $startRow + 1,
                $endDataRow,
                $totalCols,
                [],
                [1, 2, 3, 4, 7, 8, 9, 10, 11]
            );

            // Total Summary Row
            $totalRow = $row;
            $sheet->mergeCells("A{$totalRow}:J{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'TOTAL KERUSAKAN TERDATA');
            $sheet->setCellValue("K{$totalRow}", $totalRusakUnit);
            $sheet->setCellValue("L{$totalRow}", '');

            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
            $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '94A3B8'],
                    ],
                ],
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("K{$totalRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        ExcelExportService::autoFitColumns($sheet, $totalCols);

        return ExcelExportService::streamDownload($spreadsheet, $filePrefix . date('Ymd_His'));
    }

    private function exportCekAlat(Request $request, string $kategori, string $title, string $filePrefix)
    {
        $searchQuery = $request->query('search', '');

        $query = CekHarianAlat::with(['user', 'unit'])->where(function ($q) use ($kategori) {
            if ($kategori === 'pemadam') {
                $q->where('kategori', 'pemadam')->orWhereNull('kategori');
            } else {
                $q->where('kategori', $kategori);
            }
        });

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $list = $query->latest()->get();

        [$spreadsheet, $sheet, $startRow] = ExcelExportService::createWithHeader(
            $title,
            'Dicetak pada: ' . date('d/m/Y H:i') . ' WIB'
        );

        $headers = [
            'NO',
            'TANGGAL & WAKTU CEK',
            'POS PENEMPATAN',
            'REGU',
            'NAMA PEMERIKSA',
            'TOTAL ALAT',
            'KONDISI BAIK',
            'KONDISI RUSAK',
            'RINCIAN ALAT RUSAK / CATATAN',
        ];

        ExcelExportService::setTableHeaders($sheet, $startRow, $headers);

        $row = $startRow + 1;
        $sumTotal = 0;
        $sumBaik = 0;
        $sumRusak = 0;

        foreach ($list as $idx => $item) {
            $tgl = $item->created_at ? $item->created_at->translatedFormat('d/m/Y H:i') : ($item->tanggal_pemeriksaan ? $item->tanggal_pemeriksaan->format('d/m/Y') : '—');
            $regu = $item->user?->regu ?? ($item->regu ?? '—');
            $pemeriksa = $item->nama_pemeriksa ?? ($item->user?->name ?? '—');

            $baik = (int) ($item->total_baik ?? 0);
            $rusak = (int) ($item->total_rusak ?? 0);
            $tot = $baik + $rusak;

            $sumTotal += $tot;
            $sumBaik += $baik;
            $sumRusak += $rusak;

            // Rincian alat rusak
            $rusakList = [];
            if (!empty($item->alat) && is_array($item->alat)) {
                foreach ($item->alat as $a) {
                    $jmlR = (int) ($a['jumlah_rusak'] ?? 0);
                    if ($jmlR > 0) {
                        $nm = $a['nama'] ?? 'Alat';
                        $noR = !empty($a['nomor_rusak']) ? " [No: {$a['nomor_rusak']}]" : "";
                        $rusakList[] = "{$nm} ({$jmlR} Rusak{$noR})";
                    }
                }
            }
            if (!empty($item->catatan_umum)) {
                $rusakList[] = "Catatan: " . $item->catatan_umum;
            }
            $rincian = !empty($rusakList) ? implode('; ', $rusakList) : '—';

            $colIdx = 1;
            $sheet->setCellValue([$colIdx++, $row], $idx + 1);
            $sheet->setCellValue([$colIdx++, $row], $tgl);
            $sheet->setCellValue([$colIdx++, $row], $item->pos ?? '—');
            $sheet->setCellValue([$colIdx++, $row], $regu);
            $sheet->setCellValue([$colIdx++, $row], $pemeriksa);
            $sheet->setCellValue([$colIdx++, $row], $tot);
            $sheet->setCellValue([$colIdx++, $row], $baik);
            $sheet->setCellValue([$colIdx++, $row], $rusak);
            $sheet->setCellValue([$colIdx++, $row], $rincian);

            $row++;
        }

        $endDataRow = $row - 1;
        $totalCols = count($headers);

        if ($endDataRow >= $startRow + 1) {
            ExcelExportService::styleDataRows(
                $sheet,
                $startRow + 1,
                $endDataRow,
                $totalCols,
                [],
                [1, 2, 3, 4, 6, 7, 8]
            );

            // Total Summary Row
            $totalRow = $row;
            $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'TOTAL AKUMULASI');
            $sheet->setCellValue("F{$totalRow}", $sumTotal);
            $sheet->setCellValue("G{$totalRow}", $sumBaik);
            $sheet->setCellValue("H{$totalRow}", $sumRusak);
            $sheet->setCellValue("I{$totalRow}", '');

            $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);
            $sheet->getStyle("A{$totalRow}:{$lastColLetter}{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '94A3B8'],
                    ],
                ],
            ]);
            $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("F{$totalRow}:H{$totalRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        ExcelExportService::autoFitColumns($sheet, $totalCols);

        return ExcelExportService::streamDownload($spreadsheet, $filePrefix . date('Ymd_His'));
    }
}
