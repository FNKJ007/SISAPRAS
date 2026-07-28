<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * Menampilkan form Pengajuan Pemeliharaan Unit Operasional.
     */
    public function index()
    {
        // NOTE: data dropdown di bawah ini masih statis (contoh saja).
        // Nanti silakan ganti dengan data dari database, misalnya:
        //   $bidangList = Bidang::pluck('nama_bidang', 'id');
        $bidangList = [
            'pemadam'   => 'Pemadam',
            'rescue'   => 'Rescue',
            'pencegahan'      => 'Pencegahan',
            'spi'      => 'SPI',

            
        ];

        $posList = [
            'baleendah' => 'Baleendah',
            'cicalengka' => 'Cicalengka',
            'cileunyi' => 'Cileunyi',
            'ciparay' => 'Ciparay',
            'majalaya' => 'Majalaya',
            'margaasih' => 'Margaasih (TKI)',
            'ciwidey' => 'Ciwidey (PACIRA)',
            'pangalengan' => 'Pangalengan',
            'soreang' => 'Soreang (MAKO)',
            'pencegahan' => 'Pencegahan',
            'spi' => 'SPI',
        ];

        $reguList = [
            'pemadam1' => 'Regu Pemadam 1',
            'pemadam2' => 'Regu Pemadam 2',
            'rescue1' => 'Regu Rescue 1',
            'rescue2' => 'Regu Rescue 2',
            'pencegahan1' => 'Regu Pencegahan ',
            'spi1' => 'SPI ',
        ];

        $jenisKendaraanList = [
            'pancar'  => 'Pancar',
            'pompa'   => 'Pompa',
            'rescueK'   => 'Rescue',
            'tangki' => 'Water supply/tangki',
            'komando' => 'Komando',
            'motor1' => 'Motor roda dua',
            'motor2' => 'Motor roda tiga'
        ];

        $nomorLambungList = [
            'p01' => 'P-01 / D 8518 V',
            'p02' => 'P-02 / D 9923 Z',
            'p03' => 'P-03 / NKR81-7000441',
            'p04' => 'P-04 / D 9429 V',
            'p05' => 'P-05 / D 9921 V',
            'p06' => 'P-06 / D 9932 V',
            'p07' => 'P-07 / D 9914 V',
            'p08' => 'P-08 / D 9060 V',
            'p09' => 'P-09 / D 9920 Z',
            'p10' => 'P-10 / D 8559 V',
            'p11' => 'P-11 / D 9958 Y',
            'p12' => 'P-12 / D 9957 Y',
            'r01' => 'R-01 / D 9933 V',
            'r02' => 'R-02 / NKR816-7000009',
            'r03' => 'R-03 / NKR71G-7403639',
            'r04' => 'R-04 / D 9964 Y',
            's01' => 'S-01 / D 8517 V',
            's02' => 'S-02 / D 8516 V',
            'mp01' => 'MP-01 / D 3296 Z',
            'mp02' => 'MP-02 / D 3297 Z',
            'mp03' => 'MP-03 / D 5650 V (SOREANG)',
            'mp04' => 'MP-04 / D 5061 V (BALEENDAH)',
            'd8507v' => 'D 8507 V',
            'd8508v' => 'D 8508 V',
            'lainlain' => 'LAIN-LAIN',
        ];

        return view('pemeliharaan.pengajuan', compact(
            'bidangList',
            'posList',
            'reguList',
            'jenisKendaraanList',
            'nomorLambungList'
        ));
    }

    /**
     * Menyimpan data pengajuan yang dikirim dari form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bidang'                          => 'required|string',
            'pos'                              => 'required|string',
            'regu'                             => 'required|string',
            'jenis_kendaraan'                  => 'required|string',
            'nomor_lambung'                    => 'required|string',
            'item_perbaikan'                   => 'required|string|max:255',
            'nama_pemegang'                    => 'required|string|max:255',
            'nip_pemegang'                     => 'required|string|max:50',
            'nama_komandan_regu'                => 'required|string|max:255',
            'nip_komandan_regu'                 => 'required|string|max:50',
            'nama_kepala_bidang'                => 'required|string|max:255',
            'nip_kepala_bidang'                 => 'required|string|max:50',
        ]);

        // TODO: simpan $validated ke tabel pengajuan, contoh:
        // Pengajuan::create($validated);

        return redirect()
            ->route('pemeliharaan.pengajuan')
            ->with('success', 'Pengajuan berhasil dikirim.');
    }
}
