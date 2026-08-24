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
                  ->orWhereBetween('created_at', [$tanggalAwal->copy()->startOfDay(), $tanggalAkhir->copy()->endOfDay()]);
        })->latest()->get();

        if ($dbPengajuan->count() > 0) {
            $pengajuanList = $dbPengajuan->map(function ($item) {
                // Tanggal penempatan di kalender: Gunakan tanggal_keberangkatan jika disetujui, atau created_at
                $tglTarget = ($item->status === 'disetujui' && $item->tanggal_keberangkatan)
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

                return (object) [
                    'tanggal_pengajuan'     => $tglTarget,
                    'unit_nama'             => strtoupper($item->nomor_lambung) . ' (' . ucfirst($item->pos) . ')',
                    'status'                => $item->status, // 'menunggu', 'disetujui', 'ditolak'
                    'item_perbaikan'        => $item->item_perbaikan,
                    'item_verifikasis'      => $itemVerificatedList,
                    'tanggal_keberangkatan' => $item->tanggal_keberangkatan ? $item->tanggal_keberangkatan->translatedFormat('l, d F Y') : null,
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
            'menunggu'         => $pengajuanList->where('status', 'menunggu')->count(),
            'disetujui'        => $pengajuanList->where('status', 'disetujui')->count(),
            'ditolak'          => $pengajuanList->where('status', 'ditolak')->count(),
        ];
        $ringkasan      = $kpi;
        $totalPengajuan = $kpi['total_pengajuan'];

        // 5. Hitung Kesiapan Armada (Ready vs Di Bengkel) langsung dari Database Admin Data Unit
        \App\Models\Unit::syncStatusAll();
        $totalDbUnits = \App\Models\Unit::count();

        if ($totalDbUnits > 0) {
            $totalReady   = \App\Models\Unit::where('status', 'aktif')->count();
            $totalBengkel = \App\Models\Unit::where('status', 'perbaikan')->count();
            $totalMasterArmada = $totalDbUnits;

            $unitsInBengkel = \App\Models\Unit::where('status', 'perbaikan')->orderBy('nama', 'asc')->get();
            $listBengkel = [];
            foreach ($unitsInBengkel as $u) {
                $posText = $u->pos ? " (" . ucfirst($u->pos) . ")" : "";
                $platText = ($u->plat_nomor && $u->plat_nomor !== '—') ? " / {$u->plat_nomor}" : "";

                // Ambil tanggal keberangkatan riil dari Pengajuan yang sedang disetujui/berjalan
                $activePengajuan = Pengajuan::where(function ($q) use ($u) {
                        $q->where('unit_id', $u->id)
                          ->orWhere('nomor_lambung', $u->nomor_lambung);
                    })
                    ->where('status', 'disetujui')
                    ->where('status_pengerjaan', '!=', 'selesai')
                    ->latest()
                    ->first();

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

        // 5b. Status Pemeriksaan Unit Hari Ini Berdasarkan Pos Penempatan Pengguna yang Login
        $user = auth()->user();
        $userPos = $user ? trim((string) $user->pos) : '';
        $today = Carbon::today();

        $todayChecks = \App\Models\CekHarianUnit::whereDate('created_at', $today)
            ->orWhereDate('tanggal_pemeriksaan', $today)
            ->pluck('unit_id')
            ->toArray();

        $queryPosUnits = \App\Models\Unit::query();
        if (!empty($userPos)) {
            $cleanPos = explode('(', $userPos)[0];
            $cleanPos = trim($cleanPos);

            $queryPosUnits->where(function ($q) use ($userPos, $cleanPos) {
                $q->where('pos', $userPos)
                  ->orWhere('pos', 'LIKE', "%{$cleanPos}%");
            });
        }

        $posUnits = $queryPosUnits->orderBy('nomor_lambung', 'asc')->get();

        // Fallback jika belum ada unit di pos tersebut: tampilkan default
        if ($posUnits->isEmpty()) {
            $posUnits = \App\Models\Unit::orderBy('nomor_lambung', 'asc')->take(6)->get();
        }

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
        });

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