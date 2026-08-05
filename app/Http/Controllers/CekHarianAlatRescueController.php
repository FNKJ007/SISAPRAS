<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Unit;
// use App\Models\AlatPemadam;
// use App\Models\CekHarianAlat;

class CekHarianAlatRescueController extends Controller
{
    /**
     * Menampilkan form Cek Harian Alat Pemadam.
     */
    public function index()
    {
        // Contoh data unit/kendaraan untuk dropdown (ganti dengan query Model asli)
        $unitList = collect([
            (object) ['id' => 1, 'nama' => 'Damkar 01 - Toyota Dyna'],
            (object) ['id' => 2, 'nama' => 'Damkar 02 - Hino Ranger'],
            (object) ['id' => 3, 'nama' => 'Damkar 03 - Isuzu Elf'],
        ]);

        // Daftar alat pemadam (26 item). Ganti dengan query Model asli,
        // mis. AlatPemadam::all(), jika data ini nantinya disimpan di database.
        $namaAlat = [
            'SELANG KANVAS 1,5"',
            'SELANG KANVAS 2,5"',
            'SELANG RUBBER 1,5"',
            'SELANG RUBBER 2,5"',
            'NOZZLE GUN 1,5"',
            'NOZZLE GUN 2,5"',
            'NOZZLE VARIABEL / NOZZLE JET 1,5"',
            'NOZZLE VARIABEL / NOZZLE JET 2,5"',
            'NOZZLE FOAM',
            'Y CONNECTION/ADAPTOR 2,5" X 1,5"',
            'Y CONNECTION/ADAPTOR 2,5" X 2,5"',
            'POMPA PORTABLE',
            'SELANG HISAP POMPA PORTABLE',
            'TANGKI AIR PORTABLE',
            'FLOATING PUMP / POMPA APUNG',
            'FIRE BLANKET / SELIMUT API',
            'BAKRIK',
            'SELANG HISAP PTO',
            'KUNCI SELANG HISAP',
            'JET SHOOTER',
            'ALAT PEMADAM API RINGAN (APAR) 3KG',
            'ALAT PEMADAM API RINGAN (APAR) 6KG',
            'ALAT PEMADAM API RINGAN (APAR) 9KG',
            'EXHAUSE PORTABLE',
            'GAS DETECTOR KAMERA',
            'TANGGA',
        ]; // total = 26 item

        $daftarAlat = collect($namaAlat)->map(function ($nama, $index) {
            return (object) [
                'id'           => $index + 1,
                'nama'         => $nama,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-rescue.cek-harian-alat-rescue', compact('unitList', 'daftarAlat'));
    }

    /**
     * Menyimpan hasil pemeriksaan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemeriksa'          => 'required|string|max:255',
            'jabatan'                 => 'required|string|max:255',
            'unit_id'                 => 'required|integer',
            'tanggal_pemeriksaan'     => 'required|date',

            'alat'                    => 'required|array|min:1',
            'alat.*.id'               => 'required|integer',
            'alat.*.jumlah_baik'      => 'required|integer|min:0',
            'alat.*.jumlah_rusak'     => 'required|integer|min:0',
            'alat.*.nomor_rusak'      => 'nullable|string|max:255',

            // Catatan & foto untuk keseluruhan pemeriksaan
            'catatan_umum'            => 'nullable|string',
            'foto_umum'               => 'nullable|image|max:2048', // maks 2MB
        ]);

        // Upload foto umum (jika ada), sebelum simpan header
        $fotoUmumPath = null;
        if ($request->hasFile('foto_umum')) {
            $fotoUmumPath = $request->file('foto_umum')->store('cek-harian-alat', 'public');
        }

        // TODO: simpan header pemeriksaan, misal:
        //
        // $pemeriksaan = CekHarianAlat::create([
        //     'nama_pemeriksa'      => $validated['nama_pemeriksa'],
        //     'jabatan'             => $validated['jabatan'],
        //     'unit_id'             => $validated['unit_id'],
        //     'tanggal_pemeriksaan' => $validated['tanggal_pemeriksaan'],
        //     'catatan_umum'        => $validated['catatan_umum'] ?? null,
        //     'foto_umum'           => $fotoUmumPath,
        // ]);
        //
        // Lalu loop untuk simpan detail tiap alat:
        //
        // foreach ($validated['alat'] as $item) {
        //     $pemeriksaan->detailAlat()->create([
        //         'alat_id'      => $item['id'],
        //         'status'       => $item['status'],
        //         'nomor_rusak'  => $item['status'] === 'rusak'
        //                             ? ($item['nomor_rusak'] ?? null)
        //                             : null,
        //     ]);
        // }

        return redirect()
            ->route('alat-rescue.cek-harian-alat')
            ->with('success', 'Pemeriksaan alat berhasil disimpan.');
    }
}