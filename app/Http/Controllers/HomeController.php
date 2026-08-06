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
        $month = (int) $request->query('month', date('n'));
        $year  = (int) $request->query('year', date('Y'));

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
            // Data sampel bawaan jika belum ada record di bulan ini
            $pengajuanList = collect([
                (object) [
                    'tanggal_pengajuan'     => $tanggalAwal->copy()->addDays(2)->toDateString(),
                    'unit_nama'             => 'P-01 / D 8518 V (Soreang)',
                    'status'                => 'menunggu',
                    'item_perbaikan'        => 'Penggantian oli mesin & servis rem',
                    'item_verifikasis'      => [],
                    'tanggal_keberangkatan' => null,
                    'catatan_admin'         => null,
                ],
                (object) [
                    'tanggal_pengajuan'     => $tanggalAwal->copy()->addDays(5)->toDateString(),
                    'unit_nama'             => 'R-01 / D 9933 V (Baleendah)',
                    'status'                => 'disetujui',
                    'item_perbaikan'        => 'Perbaikan katup hidrolik rescue & kelistrikan siren',
                    'item_verifikasis'      => [
                        ['nama' => 'katup hidrolik', 'status' => 'disetujui'],
                        ['nama' => 'kelistrikan siren', 'status' => 'disetujui'],
                    ],
                    'tanggal_keberangkatan' => $tanggalAwal->copy()->addDays(5)->translatedFormat('l, d F Y'),
                    'catatan_admin'         => 'Servis rutin berkala',
                ],
            ]);
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
            'totalPengajuan'
        ));
    }
}