<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use Illuminate\Http\Request;

class CekAlatCcController extends Controller
{
    /**
     * Menampilkan form Cek Harian Alat Command Center.
     */
    public function index()
    {
        $unitList = collect([
            (object) ['id' => 1, 'nama' => 'Regu 1'],
            (object) ['id' => 2, 'nama' => 'Regu 2'],
            (object) ['id' => 3, 'nama' => 'Regu 3'],
        ]);

        // Ambil data peralatan Command Center dari database Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'command_center')->orderBy('nama', 'asc')->get();

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-cc.cek-alat-cc', compact('unitList', 'daftarAlat'));
    }

    /**
     * Menyimpan hasil pemeriksaan alat Command Center.
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
            'alat.*.nomor_rusak'  => 'nullable|string|max:500',
            'catatan_umum'        => 'nullable|string|max:1000',
            'foto_umum'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], [
            'foto_umum.uploaded' => 'File Foto Umum gagal diunggah. Ukuran foto terlalu besar atau melebihi batas upload PHP server (Maks 10MB).',
            'foto_umum.max'      => 'Ukuran foto tidak boleh lebih dari 10 MB.',
        ]);

        $reguMap = [
            1 => 'Regu 1',
            2 => 'Regu 2',
            3 => 'Regu 3',
        ];

        $unitNama = $reguMap[(int) $validated['unit_id']] ?? ('Regu ' . $validated['unit_id']);

        $processedAlat = [];
        $totalBaik = 0;
        $totalRusak = 0;

        foreach ($validated['alat'] as $itemData) {
            $alatObj  = Peralatan::find($itemData['id']);
            $namaAlat = $alatObj ? $alatObj->nama : ("Alat CC #" . $itemData['id']);

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

        $fotoPath = null;
        if ($request->hasFile('foto_umum')) {
            $fotoPath = $request->file('foto_umum')->store('cek-alat-cc', 'public');
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
            ->route('alat-cc.cek-alat-cc')
            ->with('success', "Pemeriksaan harian alat Command Center ({$unitNama}) berhasil disimpan!");
    }
}