<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Unit;
// use App\Models\AlatPemadam;
// use App\Models\CekHarianAlat;

class CekAlatCcController extends Controller
{
    /**
     * Menampilkan form Cek Harian Alat Pemadam.
     */
    public function index()
    {
        // Contoh data unit/kendaraan untuk dropdown (ganti dengan query Model asli)
        $unitList = collect([
            (object) ['id' => 1, 'nama' => 'Regu 1'],
            (object) ['id' => 2, 'nama' => 'Regu 2'],
            
        ]);

        // Daftar alat pemadam sesuai data yang diberikan (26 item, termasuk varian
        // ukuran/kapasitas yang dipisah jadi baris tersendiri: Y Connection 2 ukuran,
        // APAR 3 kapasitas). Ganti dengan query Model asli, mis. AlatPemadam::all(),
        // jika data ini nantinya disimpan di database.
        $namaAlat = [
            'Telepon',
            'Tablet/Handphone',
            'Handy Talky',
            'Walky Talky',
            'RIG',
            'Komputer',
            'Speaker Komputer',
            'UPS',
            'Headphone',
            'Megaphone (TOA)',
            'TV',
            'Monitor',
            'Laser Distance',
            
        ]; // total = 26 item

        $daftarAlat = collect($namaAlat)->map(function ($nama, $index) {
            return (object) [
                'id'     => $index + 1,
                'nama'   => $nama,
                'status' => 'baik',
            ];
        });

        return view('auth.alat-cc.cek-alat-cc', compact('unitList', 'daftarAlat'));
    }

    /**
     * Menyimpan hasil pemeriksaan.
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
            'alat.*.status'       => 'required|in:baik,rusak',
            'alat.*.keterangan'   => 'nullable|string',
            'alat.*.foto'         => 'nullable|image|max:2048', // maks 2MB
        ]);

        // TODO: simpan header pemeriksaan, lalu loop $validated['alat']
        // untuk simpan tiap baris + upload foto ke storage, mis:
        //
        // foreach ($validated['alat'] as $item) {
        //     $fotoPath = null;
        //     if ($request->hasFile("alat.{$loopIndex}.foto")) {
        //         $fotoPath = $request->file("alat.{$loopIndex}.foto")->store('cek-harian-alat', 'public');
        //     }
        //     CekHarianAlat::create([...]);
        // }

        return redirect()
            ->route('alat-cc.cek-alat-cc')
            ->with('success', 'Pemeriksaan alat berhasil disimpan.');
    }
}