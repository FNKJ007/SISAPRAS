<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kejadian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MonitoringKejadianController extends Controller
{
    public function index(Request $request)
    {
        $query = Kejadian::query();

        // Filter Jenis Kejadian
        if ($request->filled('jenis')) {
            $query->where('jenis_kejadian', $request->jenis);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_kejadian', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('kategori_detail', 'like', "%{$search}%")
                  ->orWhere('nama_pelapor', 'like', "%{$search}%");
            });
        }

        $dataKejadian = $query->orderBy('waktu_kejadian', 'desc')->paginate(10);

        // Kartu Ringkasan
        $totalKejadian  = Kejadian::count();
        $totalKebakaran = Kejadian::where('jenis_kejadian', 'Kebakaran')->count();
        $totalRescue    = Kejadian::whereIn('jenis_kejadian', ['Rescue', 'Penyelamatan'])->count();
        $totalKerugian  = Kejadian::sum('estimasi_kerugian');

        // ===================== DATA GRAFIK =====================
        // Menggunakan whereYear()/whereMonth() (bukan raw MONTH()/YEAR()) agar tetap
        // kompatibel lintas driver database (SQLite maupun MySQL).
        $tahunTersedia = Kejadian::whereNotNull('waktu_kejadian')
            ->pluck('waktu_kejadian')
            ->map(fn ($tgl) => \Illuminate\Support\Carbon::parse($tgl)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
        if (empty($tahunTersedia)) {
            $tahunTersedia = [now()->year];
        }

        $tahunGrafik = (int) $request->input('tahun_grafik', $tahunTersedia[0]);
        if (!in_array($tahunGrafik, $tahunTersedia)) {
            $tahunGrafik = $tahunTersedia[0];
        }

        // Tren jumlah kejadian per bulan (dipecah per jenis) untuk tahun terpilih
        $jenisList = ['Kebakaran', 'Rescue', 'Penyelamatan', 'Non-Kebakaran'];
        $chartBulanan = [];
        foreach ($jenisList as $jenis) {
            $chartBulanan[$jenis] = array_fill(0, 12, 0);
        }

        $kejadianTahunIni = Kejadian::whereYear('waktu_kejadian', $tahunGrafik)
            ->get(['waktu_kejadian', 'jenis_kejadian']);

        foreach ($kejadianTahunIni as $item) {
            $bulanIndex = \Illuminate\Support\Carbon::parse($item->waktu_kejadian)->month - 1;
            if (isset($chartBulanan[$item->jenis_kejadian])) {
                $chartBulanan[$item->jenis_kejadian][$bulanIndex]++;
            }
        }

        // Distribusi jenis kejadian (seluruh data yang sudah diinput)
        $chartDistribusiJenis = Kejadian::select('jenis_kejadian')
            ->get()
            ->countBy('jenis_kejadian');

        // Distribusi status penanganan
        $chartDistribusiStatus = Kejadian::select('status')
            ->get()
            ->countBy('status');

        return view('admin.apar-kejadian.monitoring-kejadian', compact(
            'dataKejadian',
            'totalKejadian',
            'totalKebakaran',
            'totalRescue',
            'totalKerugian',
            'tahunGrafik',
            'tahunTersedia',
            'chartBulanan',
            'chartDistribusiJenis',
            'chartDistribusiStatus'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kejadian'      => 'required|unique:kejadian,kode_kejadian',
            'waktu_kejadian'     => 'required|date',
            'jenis_kejadian'     => 'required',
            'kategori_detail'    => 'required|string',
            'lokasi'             => 'required|string',
            'status'             => 'required',
            'estimasi_kerugian'  => 'nullable|integer|min:0',
            'korban_luka'        => 'nullable|integer|min:0',
            'korban_jiwa'        => 'nullable|integer|min:0',
            'file_laporan'       => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = $this->sanitizeAngka($request->except('file_laporan'));

        if ($request->hasFile('file_laporan')) {
            $data['file_laporan'] = $request->file('file_laporan')->store('kejadian-dokumen', 'public');
        }

        Kejadian::create($data);

        return redirect()->back()->with('success', 'Data kejadian berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kejadian = Kejadian::findOrFail($id);

        $validated = $request->validate([
            'kode_kejadian'      => 'required|unique:kejadian,kode_kejadian,' . $kejadian->id,
            'waktu_kejadian'     => 'required|date',
            'jenis_kejadian'     => 'required',
            'kategori_detail'    => 'required|string',
            'lokasi'             => 'required|string',
            'status'             => 'required',
            'estimasi_kerugian'  => 'nullable|integer|min:0',
            'korban_luka'        => 'nullable|integer|min:0',
            'korban_jiwa'        => 'nullable|integer|min:0',
            'file_laporan'       => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = $this->sanitizeAngka($request->except('file_laporan'));

        if ($request->hasFile('file_laporan')) {
            if ($kejadian->file_laporan) {
                Storage::disk('public')->delete($kejadian->file_laporan);
            }
            $data['file_laporan'] = $request->file('file_laporan')->store('kejadian-dokumen', 'public');
        }

        $kejadian->update($data);

        return redirect()->back()->with('success', 'Data kejadian berhasil diperbarui.');
    }

    /**
     * Kolom angka (estimasi_kerugian, korban_luka, korban_jiwa) bersifat NOT NULL
     * dengan default 0 di database. Middleware bawaan Laravel (ConvertEmptyStringsToNull)
     * mengubah input kosong menjadi null, sehingga perlu dikembalikan ke 0 di sini
     * agar tidak melanggar constraint NOT NULL saat disimpan.
     */
    private function sanitizeAngka(array $data): array
    {
        foreach (['estimasi_kerugian', 'korban_luka', 'korban_jiwa'] as $kolom) {
            if (!isset($data[$kolom]) || $data[$kolom] === '' || $data[$kolom] === null) {
                $data[$kolom] = 0;
            }
        }

        return $data;
    }

    public function destroy($id)
    {
        $kejadian = Kejadian::findOrFail($id);

        if ($kejadian->file_laporan) {
            Storage::disk('public')->delete($kejadian->file_laporan);
        }

        $kejadian->delete();

        return redirect()->back()->with('success', 'Data kejadian berhasil dihapus.');
    }
}