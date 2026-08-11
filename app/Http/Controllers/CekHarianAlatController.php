<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Unit;
use Illuminate\Http\Request;

class CekHarianAlatController extends Controller
{
    /**
     * Daftar unit/kendaraan dari database Admin Data Unit.
     */
    protected function unitList()
    {
        $units = Unit::where('kategori', 'pemadam')->orderBy('nomor_lambung', 'asc')->get();

        if ($units->isEmpty()) {
            return collect([
                (object) ['id' => 1, 'nama' => 'P-01 - HINO (4X4)'],
            ]);
        }

        return $units;
    }

    /**
     * Menampilkan form Cek Harian Alat Pemadam.
     */
    public function index()
    {
        $unitList = $this->unitList();

        // Ambil data peralatan pemadam langsung dari Panel Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'pemadam')->orderBy('nama', 'asc')->get();

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-pemadam.cek-harian-alat', compact('unitList', 'daftarAlat'));
    }

    /**
     * Menyimpan hasil pemeriksaan alat pemadam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemeriksa'      => 'required|string|max:255',
            'jabatan'             => 'required|string|max:255',
            'unit_id'             => 'required|integer',
            'tanggal_pemeriksaan' => 'required|date',

            'alat'                => 'required|array|min:1',
            'alat.*.id'           => 'required|integer',
            'alat.*.jumlah_baik'  => 'nullable|integer|min:0',
            'alat.*.jumlah_rusak' => 'nullable|integer|min:0',
            'alat.*.nomor_rusak'  => 'nullable|string|max:255',

            'catatan_umum'        => 'nullable|string',
            'foto_umum'           => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ], [
            'foto_umum.uploaded' => 'File foto dokumentasi gagal diunggah. Ukuran foto terlalu besar (Maks 10MB).',
            'foto_umum.max'      => 'Ukuran foto dokumentasi tidak boleh lebih dari 10 MB.',
        ]);

        $unitObj  = Unit::find($validated['unit_id']);
        $unitNama = $unitObj ? "{$unitObj->nomor_lambung} ({$unitObj->plat_nomor})" : ("Unit #" . $validated['unit_id']);

        $fotoPath = null;
        if ($request->hasFile('foto_umum')) {
            $fotoPath = $request->file('foto_umum')->store('cek-harian-alat', 'public');
        }

        $processedAlat = [];
        $totalBaik = 0;
        $totalRusak = 0;

        foreach ($validated['alat'] as $itemData) {
            $alatObj = Peralatan::find($itemData['id']);
            $namaAlat = $alatObj ? $alatObj->nama : ("Alat #" . $itemData['id']);

            $baik  = (int) ($itemData['jumlah_baik'] ?? 0);
            $rusak = (int) ($itemData['jumlah_rusak'] ?? 0);

            $totalBaik += $baik;
            $totalRusak += $rusak;

            $processedAlat[] = [
                'id'           => $itemData['id'],
                'nama'         => $namaAlat,
                'jumlah_baik'  => $baik,
                'jumlah_rusak' => $rusak,
                'nomor_rusak'  => $itemData['nomor_rusak'] ?? null,
            ];
        }

        CekHarianAlat::create([
            'user_id'             => auth()->id() ?? 1,
            'unit_id'             => $validated['unit_id'],
            'unit_nama'           => $unitNama,
            'nama_pemeriksa'      => $validated['nama_pemeriksa'],
            'jabatan'             => $validated['jabatan'],
            'tanggal_pemeriksaan' => $validated['tanggal_pemeriksaan'],
            'alat'                => $processedAlat,
            'total_baik'          => $totalBaik,
            'total_rusak'         => $totalRusak,
            'catatan_umum'        => $validated['catatan_umum'] ?? null,
            'foto_umum'           => $fotoPath,
        ]);

        return redirect()
            ->route('alat-pemadam.cek-harian-alat')
            ->with('success', "Pemeriksaan harian alat Pemadam untuk unit '{$unitNama}' berhasil disimpan!");
    }
}
