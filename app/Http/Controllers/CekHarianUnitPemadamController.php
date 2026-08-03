<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Unit;
// use App\Models\CekHarianUnit;

class CekHarianUnitPemadamController extends Controller
{
    /**
     * Menampilkan form wizard Cek Harian Unit Kendaraan Pemadam.
     */
    public function index()
    {
        // Contoh data unit/kendaraan untuk dropdown (ganti dengan query Model asli, mis. Unit::all())
        $unitList = collect([
            (object) ['id' => 1, 'nama' => 'Damkar 01 - Toyota Dyna'],
            (object) ['id' => 2, 'nama' => 'Damkar 02 - Hino Ranger'],
            (object) ['id' => 3, 'nama' => 'Damkar 03 - Isuzu Elf'],
        ]);

        return view('auth.unit-pemadam.cek-harian-unit', compact('unitList'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan pemadam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Step 1 - Identitas
            'nama_pemeriksa'   => 'required|string|max:255',
            'jabatan'          => 'required|string|max:255',
            'unit_id'          => 'required|integer',
            'shift'            => 'required|in:pagi,siang,malam',

            // Step 2 - Pemanasan & BBM
            'bukti_pemanasan'  => 'nullable|image|max:2048',
            'jenis_bbm'        => 'required|in:solar,bensin',
            'level_bbm'        => 'required|in:penuh,3_4,1_2,kosong',
            'jumlah_bbm_liter' => 'nullable|numeric|min:0',

            // Step 3 - Tangki & Pompa
            'level_air'               => 'required|in:penuh,3_4,1_2,kosong',
            'kondisi_tangki'          => 'required|in:baik,perlu_perhatian,rusak',
            'kebocoran_tangki'        => 'required|in:ada,tidak_ada',
            'tekanan_pompa'           => 'required|in:baik,rusak',
            'pengisian_pompa'         => 'required|in:baik,rusak',
            'selang_induk'            => 'required|in:baik,rusak',
            'catatan_tangki_pompa'    => 'nullable|string',
            'dokumentasi_tangki_pompa'   => 'nullable|array|max:3',
            'dokumentasi_tangki_pompa.*' => 'image|max:2048',

            // Step 4 - Perlengkapan
            'perlengkapan'                    => 'required|array',
            'perlengkapan.*.status'           => 'required|in:baik,rusak',
            'perlengkapan.*.catatan'          => 'nullable|string',
        ]);

        // TODO: simpan header pemeriksaan + upload file (bukti_pemanasan,
        // dokumentasi_tangki_pompa[]) ke storage, lalu simpan tiap baris
        // perlengkapan, mis:
        //
        // $fotoPemanasanPath = $request->hasFile('bukti_pemanasan')
        //     ? $request->file('bukti_pemanasan')->store('cek-harian-unit', 'public')
        //     : null;
        //
        // foreach ($request->file('dokumentasi_tangki_pompa', []) as $foto) {
        //     $foto->store('cek-harian-unit', 'public');
        // }
        //
        // CekHarianUnit::create([...]);

        return redirect()
            ->route('unit-pemadam.cek-harian-unit')
            ->with('success', 'Pemeriksaan unit kendaraan pemadam berhasil disimpan.');
    }
}
