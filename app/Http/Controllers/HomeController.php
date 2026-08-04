<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
// use App\Models\Pengajuan;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $tanggalAwal  = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $tanggalAkhir = $tanggalAwal->copy()->endOfMonth();

        // Ganti dengan query Model asli, misalnya:
        //
        // $pengajuanList = Pengajuan::with('unit')
        //     ->whereBetween('tanggal_pengajuan', [$tanggalAwal, $tanggalAkhir])
        //     ->get()
        //     ->map(function ($item) {
        //         return (object) [
        //             'tanggal_pengajuan' => $item->tanggal_pengajuan,
        //             'unit_nama'         => $item->unit->nama ?? '-',
        //             'status'            => $item->status,
        //             'keterangan'        => $item->keterangan_kerusakan ?? null,
        //         ];
        //     });

        $pengajuanList = collect([
            (object) [
                'tanggal_pengajuan' => $tanggalAwal->copy()->addDays(2)->toDateString(),
                'unit_nama' => 'Damkar 01 - Toyota Dyna',
                'status' => 'menunggu',
                'keterangan' => null,
            ],
            (object) [
                'tanggal_pengajuan' => $tanggalAwal->copy()->addDays(2)->toDateString(),
                'unit_nama' => 'Damkar 02 - Hino Ranger',
                'status' => 'dibengkel',
                'keterangan' => 'Rem tangan tidak berfungsi, selang hisap pompa bocor.',
            ],
            (object) [
                'tanggal_pengajuan' => $tanggalAwal->copy()->addDays(5)->toDateString(),
                'unit_nama' => 'Damkar 03 - Isuzu Elf',
                'status' => 'selesai',
                'keterangan' => 'Perbaikan sistem kelistrikan sudah selesai.',
            ],
            (object) [
                'tanggal_pengajuan' => $tanggalAwal->copy()->addDays(10)->toDateString(),
                'unit_nama' => 'Damkar 01 - Toyota Dyna',
                'status' => 'dibengkel',
                'keterangan' => 'Mesin pompa mengalami overheat saat pengujian.',
            ],
        ]);

        $eventsByDate = $pengajuanList->groupBy(function ($item) {
            return Carbon::parse($item->tanggal_pengajuan)->toDateString();
        });

        $ringkasan = [
            'menunggu'  => $pengajuanList->where('status', 'menunggu')->count(),
            'dibengkel' => $pengajuanList->where('status', 'dibengkel')->count(),
            'selesai'   => $pengajuanList->where('status', 'selesai')->count(),
        ];

        $startOfCalendar = $tanggalAwal->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar   = $tanggalAkhir->copy()->endOfWeek(Carbon::SUNDAY);

        $calendarWeeks = [];
        $current = $startOfCalendar->copy();
        while ($current <= $endOfCalendar) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $week[] = $current->copy();
                $current->addDay();
            }
            $calendarWeeks[] = $week;
        }

        $bulanSebelumnya = $tanggalAwal->copy()->subMonth();
        $bulanBerikutnya = $tanggalAwal->copy()->addMonth();

        return view('home', [
            'calendarWeeks'   => $calendarWeeks,
            'eventsByDate'    => $eventsByDate,
            'bulanAktif'      => $tanggalAwal,
            'bulanSebelumnya' => $bulanSebelumnya,
            'bulanBerikutnya' => $bulanBerikutnya,
            'ringkasan'       => $ringkasan,
            'totalPengajuan'  => $pengajuanList->count(),
        ]);
    }
}