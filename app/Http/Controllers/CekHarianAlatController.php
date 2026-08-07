<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use Illuminate\Http\Request;

class CekHarianAlatController extends Controller
{
    /**
     * Daftar unit/kendaraan (dummy, ganti dengan Model Unit::all() bila sudah tersedia).
     */
    protected function unitList()
    {
        return collect([
            (object) ['id' => 1, 'nama' => 'Damkar 01 - Toyota Dyna'],
            (object) ['id' => 2, 'nama' => 'Damkar 02 - Hino Ranger'],
            (object) ['id' => 3, 'nama' => 'Damkar 03 - Isuzu Elf'],
        ]);
    }

    /**
     * Daftar nama alat pemadam (26 item). Ganti dengan query Model asli
     * (mis. AlatPemadam::all()) jika data ini nantinya disimpan di database.
     */
    protected function namaAlat(): array
    {
        return [
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
    }

    /**
     * Menampilkan form Cek Harian Alat Pemadam.
     */
    public function index()
    {
        $unitList = $this->unitList();

        $daftarAlat = collect($this->namaAlat())->map(function ($nama, $index) {
            return (object) [
                'id'           => $index + 1,
                'nama'         => $nama,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-pemadam.cek-harian-alat', compact('unitList', 'daftarAlat'));
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

        // Gabungkan nama alat ke setiap baris & hitung total baik/rusak
        $namaAlat = $this->namaAlat();
        $alat = [];
        $totalBaik = 0;
        $totalRusak = 0;

        foreach ($validated['alat'] as $item) {
            $jumlahBaik  = (int) ($item['jumlah_baik'] ?? 0);
            $jumlahRusak = (int) ($item['jumlah_rusak'] ?? 0);
            $totalBaik  += $jumlahBaik;
            $totalRusak += $jumlahRusak;

            $alat[] = [
                'id'           => $item['id'],
                'nama'         => $namaAlat[$item['id'] - 1] ?? ('Alat #' . $item['id']),
                'jumlah_baik'  => $jumlahBaik,
                'jumlah_rusak' => $jumlahRusak,
                'nomor_rusak'  => $jumlahRusak > 0 ? ($item['nomor_rusak'] ?? null) : null,
            ];
        }

        // Ambil nama unit terpilih untuk disimpan sebagai snapshot
        $unit = $this->unitList()->firstWhere('id', (int) $validated['unit_id']);

        CekHarianAlat::create([
            'user_id'             => auth()->id(),
            'nama_pemeriksa'      => $validated['nama_pemeriksa'],
            'jabatan'             => $validated['jabatan'],
            'unit_id'             => $validated['unit_id'],
            'unit_nama'           => $unit->nama ?? null,
            'tanggal_pemeriksaan' => $validated['tanggal_pemeriksaan'],
            'alat'                => $alat,
            'total_baik'          => $totalBaik,
            'total_rusak'         => $totalRusak,
            'catatan_umum'        => $validated['catatan_umum'] ?? null,
            'foto_umum'           => $fotoUmumPath,
        ]);

        return redirect()
            ->route('alat-pemadam.cek-harian-alat')
            ->with('success', 'Pemeriksaan alat berhasil disimpan.');
    }
}
