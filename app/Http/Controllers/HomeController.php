<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Menampilkan Halaman Utama (Home User) dengan Kalender & Ringkasan KPI.
     */
    public function index(Request $request)
    {
        // Set locale ke Indonesia untuk format nama hari/bulan
        Carbon::setLocale('id');

        // 1. Dapatkan bulan & tahun dari query string (default: bulan & tahun saat ini)
        $month = (int) $request->query('month', $request->query('bulan', date('n')));
        $year  = (int) $request->query('year', $request->query('tahun', date('Y')));

        // Buat objek Carbon untuk navigasi bulan
        $currentDate     = Carbon::createFromDate($year, $month, 1);
        $bulanAktif      = $currentDate;
        $bulanSebelumnya = $currentDate->copy()->subMonth();
        $bulanBerikutnya = $currentDate->copy()->addMonth();

        $prevMonthUrl = route('home', ['month' => $bulanSebelumnya->month, 'year' => $bulanSebelumnya->year]);
        $nextMonthUrl = route('home', ['month' => $bulanBerikutnya->month, 'year' => $bulanBerikutnya->year]);

        // Informasi Tanggal Hari Ini
        $todayDate     = Carbon::today();
        $hariIniString = $todayDate->translatedFormat('l, d F Y');

        // 2. Query data pengajuan aktual dari database untuk bulan ini
        $tanggalAwal  = $currentDate->copy()->startOfMonth();
        $tanggalAkhir = $currentDate->copy()->endOfMonth();

        $dbPengajuan = Pengajuan::where(function ($query) use ($tanggalAwal, $tanggalAkhir) {
            $query->whereBetween('tanggal_keberangkatan', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
                  ->orWhereBetween('tanggal_selesai_pengerjaan', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
                  ->orWhereBetween('created_at', [$tanggalAwal->copy()->startOfDay(), $tanggalAkhir->copy()->endOfDay()]);
        })->latest()->get();

        $todayStr = now()->format('Y-m-d');

        if ($dbPengajuan->count() > 0) {
            $pengajuanList = $dbPengajuan->map(function ($item) use ($todayStr) {
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
                    'tanggal_pengajuan'     => $tglTarget,
                    'unit_nama'             => strtoupper($item->nomor_lambung) . ' (' . ucfirst($item->pos) . ')',
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
            $pengajuanList = collect([]);
        }

        // Grouping data pengajuan berdasarkan tanggal_pengajuan ('Y-m-d')
        $eventsByDate = $pengajuanList->groupBy('tanggal_pengajuan');

        // 3. Bangun Grid Minggu Kalender (Minggu s.d. Sabtu)
        $startOfCalendar = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar   = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

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

        // 4. Hitung Statistik Ringkasan KPI
        $kpi = [
            'total_pengajuan'  => $pengajuanList->count(),
            'menunggu'         => $pengajuanList->where('status_kalender', 'menunggu')->count(),
            'disetujui'        => $pengajuanList->whereIn('status_kalender', ['disetujui_ke_bengkel', 'dalam_perbaikan'])->count(),
            'selesai'          => $pengajuanList->where('status_kalender', 'selesai')->count(),
            'ditolak'          => $pengajuanList->where('status_kalender', 'ditolak')->count(),
        ];
        $ringkasan      = $kpi;
        $totalPengajuan = $kpi['total_pengajuan'];

        // 5. Hitung Kesiapan Armada (Ready vs Di Bengkel) langsung dari Database Admin Data Unit
        \App\Models\Unit::syncStatusAll();
        
        $unitStats = \App\Models\Unit::selectRaw("
            count(*) as total,
            count(case when status = 'aktif' then 1 end) as aktif,
            count(case when status = 'perbaikan' then 1 end) as perbaikan
        ")->first();

        $totalDbUnits = (int) ($unitStats->total ?? 0);
        $totalReady   = (int) ($unitStats->aktif ?? 0);
        $totalBengkel = (int) ($unitStats->perbaikan ?? 0);
        $totalMasterArmada = $totalDbUnits;

        if ($totalDbUnits > 0) {
            $unitsInBengkel = \App\Models\Unit::where('status', 'perbaikan')->orderBy('nama', 'asc')->get();
            
            // Pre-fetch active approved pengajuans in 1 single query to prevent N+1 loop
            $activePengajuans = Pengajuan::where('status', 'disetujui')
                ->where('status_pengerjaan', '!=', 'selesai')
                ->latest('id')
                ->get();

            $listBengkel = [];
            foreach ($unitsInBengkel as $u) {
                $posText = $u->pos ? " (" . ucfirst($u->pos) . ")" : "";
                $platText = ($u->plat_nomor && $u->plat_nomor !== '—') ? " / {$u->plat_nomor}" : "";

                $activePengajuan = $activePengajuans->first(function ($p) use ($u) {
                    return $p->unit_id == $u->id || 
                           ($p->nomor_lambung && $u->nomor_lambung && strcasecmp(trim($p->nomor_lambung), trim($u->nomor_lambung)) === 0);
                });

                $tglBerangkatStr = '-';
                if ($activePengajuan && $activePengajuan->tanggal_keberangkatan) {
                    $tglBerangkatStr = $activePengajuan->tanggal_keberangkatan->translatedFormat('d F Y');
                } elseif ($u->updated_at) {
                    $tglBerangkatStr = $u->updated_at->translatedFormat('d F Y');
                }

                $listBengkel[] = [
                    'nomor_lambung'         => $u->nomor_lambung ?? $u->nama,
                    'unit_nama'             => ($u->nomor_lambung ? strtoupper($u->nomor_lambung) . $platText : $u->nama) . $posText,
                    'tanggal_keberangkatan' => $tglBerangkatStr,
                ];
            }
        } else {
            // Fallback jika tabel unit belum diisi
            $today = Carbon::today();
            $pengajuanBengkelAktif = Pengajuan::where('status', 'disetujui')
                ->whereNotNull('tanggal_keberangkatan')
                ->whereDate('tanggal_keberangkatan', '<=', $today)
                ->get();

            $listBengkel = [];
            $unitsBengkelSet = [];

            foreach ($pengajuanBengkelAktif as $pb) {
                $unitKey = strtoupper($pb->nomor_lambung);
                if (!isset($unitsBengkelSet[$unitKey])) {
                    $unitsBengkelSet[$unitKey] = true;
                    $listBengkel[] = [
                        'nomor_lambung'         => $pb->nomor_lambung,
                        'unit_nama'             => strtoupper($pb->nomor_lambung) . ' (' . ucfirst($pb->pos) . ')',
                        'tanggal_keberangkatan' => $pb->tanggal_keberangkatan ? $pb->tanggal_keberangkatan->translatedFormat('d F Y') : '-',
                    ];
                }
            }

            $totalBengkel = count($unitsBengkelSet);
            $totalMasterArmada = max(10, Pengajuan::distinct('nomor_lambung')->count('nomor_lambung'));
            $totalReady = max(0, $totalMasterArmada - $totalBengkel);
        }

        $summaryArmada = [
            'total_armada'  => $totalMasterArmada,
            'total_ready'   => $totalReady,
            'total_bengkel' => $totalBengkel,
            'list_bengkel'  => $listBengkel,
        ];

        // 5b. Status Pemeriksaan Unit Hari Ini Berdasarkan Pos & Bidang Pengguna yang Login
        $user = auth()->user();
        $userPos = $user ? trim((string) $user->pos) : '';
        $userBidang = strtolower(trim((string) ($user->bidang ?? '')));
        $isAdminSimulasi = $user && $user->isAdmin() && session('admin_viewing_as_user');
        $isSpi = str_contains($userBidang, 'sarana prasarana') || str_contains($userBidang, 'spi');
        $showAllBidang = ($user && $user->isAdmin() && !$isAdminSimulasi) || $isSpi;

        $today = Carbon::today();

        $todayChecks = \App\Models\CekHarianUnit::whereDate('created_at', $today)
            ->orWhereDate('tanggal_pemeriksaan', $today)
            ->pluck('unit_id')
            ->toArray();

        $queryPosUnits = \App\Models\Unit::query();

        // 1. Filter berdasarkan Pos Penempatan Pengguna
        if (!empty($userPos)) {
            $cleanPos = explode('(', $userPos)[0];
            $cleanPos = trim($cleanPos);

            $queryPosUnits->where(function ($q) use ($userPos, $cleanPos) {
                $q->where('pos', $userPos)
                  ->orWhere('pos', 'ILIKE', "%{$cleanPos}%");
            });
        }

        // 2. Filter berdasarkan Bidang Pengguna (Pemadam, Rescue, Pencegahan)
        if (!$showAllBidang && !empty($userBidang)) {
            if (str_contains($userBidang, 'pemadam')) {
                $queryPosUnits->where(function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('kategori', 'Pemadam')
                            ->where(function ($s2) {
                                $s2->where('peruntukan', 'NOT LIKE', '%pencegahan%')
                                   ->orWhereNull('peruntukan');
                            });
                    })
                    ->orWhere('peruntukan', 'ILIKE', '%pemadam%')
                    ->orWhere('nomor_lambung', 'ILIKE', 'P-%')
                    ->orWhere('nomor_lambung', 'ILIKE', 'S-%')
                    ->orWhere('nomor_lambung', 'ILIKE', 'MP-%');
                })
                ->where('nomor_lambung', 'NOT LIKE', 'PC-%')
                ->where('peruntukan', 'NOT LIKE', '%pencegahan%')
                ->where('peruntukan', 'NOT LIKE', '%rescue%')
                ->where('kategori', '!=', 'Rescue')
                ->where('kategori', '!=', 'Komando');
            } elseif (str_contains($userBidang, 'rescue')) {
                $queryPosUnits->where(function ($q) {
                    $q->where('kategori', 'Rescue')
                      ->orWhere('peruntukan', 'ILIKE', '%rescue%')
                      ->orWhere('nomor_lambung', 'ILIKE', 'R-%');
                });
            } elseif (str_contains($userBidang, 'pencegahan')) {
                $queryPosUnits->where(function ($q) {
                    $q->where('kategori', 'Pencegahan')
                      ->orWhere('peruntukan', 'ILIKE', '%pencegahan%')
                      ->orWhere('nomor_lambung', 'ILIKE', 'PC-%');
                });
            }
        }

        $posUnits = $queryPosUnits->orderBy('nomor_lambung', 'asc')->get();

        $userUnitStatus = $posUnits->map(function ($u) use ($todayChecks) {
            return (object) [
                'id'            => $u->id,
                'nomor_lambung' => $u->nomor_lambung ?? $u->nama,
                'plat_nomor'    => $u->plat_nomor,
                'merk_tipe'     => $u->merk_tipe,
                'pos'           => $u->pos,
                'pengemudi_1'   => $u->pengemudi_1,
                'pengemudi_2'   => $u->pengemudi_2,
                'status_armada' => $u->status === 'perbaikan' ? 'Dalam Perbaikan' : 'Siap Tempur / Operasi',
                'sudah_dicek'   => in_array($u->id, $todayChecks),
            ];
        })->sort(function ($a, $b) {
            // 1. Prioritas Utama: Belum Dicek (false/0) di atas, Sudah Dicek (true/1) di bawah
            if ($a->sudah_dicek !== $b->sudah_dicek) {
                return $a->sudah_dicek ? 1 : -1;
            }
            // 2. Prioritas Kedua: Urutan alfabetis & angka natural berdasarkan nomor lambung
            return strnatcasecmp($a->nomor_lambung, $b->nomor_lambung);
        })->values();

        // 6. Range Tahun Dinamis (Otomatis mencakup record tertua di DB s.d. 10 tahun ke depan)
        $minDbYear = Pengajuan::min('created_at') ? Carbon::parse(Pengajuan::min('created_at'))->year : date('Y') - 5;
        $startYear = min(2020, $minDbYear);
        $endYear   = max((int) date('Y') + 10, $year + 5);
        $availableYears = range($startYear, $endYear);

        return view('home', compact(
            'currentDate',
            'bulanAktif',
            'bulanSebelumnya',
            'bulanBerikutnya',
            'prevMonthUrl',
            'nextMonthUrl',
            'hariIniString',
            'calendarWeeks',
            'eventsByDate',
            'kpi',
            'ringkasan',
            'totalPengajuan',
            'summaryArmada',
            'userUnitStatus',
            'availableYears'
        ));
    }
}