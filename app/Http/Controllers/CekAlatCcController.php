<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
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

        $namaAlat = [
            'Telepon',
            'Tablet / Handphone',
            'Handy Talky (HT)',
            'Walky Talky',
            'Radio RIG',
            'Komputer Operasional',
            'Speaker Komputer',
            'UPS',
            'Headphone / Headset',
            'Megaphone (TOA)',
            'TV Monitoring',
            'Monitor Display',
            'Laser Distance Meter',
        ];

        $daftarAlat = collect($namaAlat)->map(function ($nama, $index) {
            return (object) [
                'id'           => $index + 1,
                'nama'         => $nama,
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
            'foto_umum'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $namaAlatMap = [
            1  => 'Telepon',
            2  => 'Tablet / Handphone',
            3  => 'Handy Talky (HT)',
            4  => 'Walky Talky',
            5  => 'Radio RIG',
            6  => 'Komputer Operasional',
            7  => 'Speaker Komputer',
            8  => 'UPS',
            9  => 'Headphone / Headset',
            10 => 'Megaphone (TOA)',
            11 => 'TV Monitoring',
            12 => 'Monitor Display',
            13 => 'Laser Distance Meter',
        ];

        $reguMap = [
            1 => 'Regu 1',
            2 => 'Regu 2',
            3 => 'Regu 3',
        ];

        $unitNama = $reguMap[(int) $validated['unit_id']] ?? ('Regu ' . $validated['unit_id']);

        $processedAlat = [];
        $totalBaik = 0;
        $totalRusak = 0;

        foreach ($validated['alat'] as $item) {
            $id = (int) $item['id'];
            $baik = (int) ($item['jumlah_baik'] ?? 0);
            $rusak = (int) ($item['jumlah_rusak'] ?? 0);

            $totalBaik += $baik;
            $totalRusak += $rusak;

            $processedAlat[] = [
                'id'           => $id,
                'nama'         => $namaAlatMap[$id] ?? ("Alat #" . $id),
                'jumlah_baik'  => $baik,
                'jumlah_rusak' => $rusak,
                'nomor_rusak'  => $rusak > 0 ? ($item['nomor_rusak'] ?? null) : null,
            ];
        }

        $fotoPath = null;
        if ($request->hasFile('foto_umum')) {
            $fotoPath = $request->file('foto_umum')->store('cek-harian-alat-cc', 'public');
        }

        CekHarianAlat::create([
            'user_id'             => auth()->id(),
            'kategori'            => 'command_center',
            'nama_pemeriksa'      => $validated['nama_pemeriksa'],
            'jabatan'             => $validated['jabatan'],
            'unit_id'             => $validated['unit_id'],
            'unit_nama'           => $unitNama,
            'tanggal_pemeriksaan' => $validated['tanggal_pemeriksaan'],
            'alat'                => $processedAlat,
            'total_baik'          => $totalBaik,
            'total_rusak'         => $totalRusak,
            'catatan_umum'        => $validated['catatan_umum'] ?? null,
            'foto_umum'           => $fotoPath,
        ]);

        return redirect()
            ->route('alat-cc.cek-alat-cc')
            ->with('success', 'Pemeriksaan alat Command Center berhasil disimpan!');
    }
}