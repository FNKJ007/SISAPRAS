<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * Menampilkan form Pengajuan Pemeliharaan Unit Operasional.
     */
    public function index()
    {
        $bidangList         = Pengajuan::$bidangMap;
        $posList            = Pengajuan::$posMap;
        $reguList           = Pengajuan::$reguMap;
        $jenisKendaraanList = Pengajuan::$jenisKendaraanMap;
        $nomorLambungList   = Pengajuan::$nomorLambungMap;

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
            'bidang'              => 'required|string',
            'pos'                 => 'required|string',
            'regu'                => 'required|string',
            'jenis_kendaraan'     => 'required|string',
            'nomor_lambung'       => 'required|string',
            'item_perbaikan'      => 'required|string|max:255',
            'nama_pemegang'       => 'required|string|max:255',
            'nip_pemegang'        => 'required|string|max:50',
            'nama_komandan_regu'   => 'required|string|max:255',
            'nip_komandan_regu'    => 'required|string|max:50',
            'nama_kepala_bidang'   => 'required|string|max:255',
            'nip_kepala_bidang'    => 'required|string|max:50',
        ]);

        // Map kode internal ke Teks Label yang Human-Readable
        $validated['bidang']          = Pengajuan::$bidangMap[$validated['bidang']] ?? $validated['bidang'];
        $validated['pos']             = Pengajuan::$posMap[$validated['pos']] ?? $validated['pos'];
        $validated['regu']            = Pengajuan::$reguMap[$validated['regu']] ?? $validated['regu'];
        $validated['jenis_kendaraan'] = Pengajuan::$jenisKendaraanMap[$validated['jenis_kendaraan']] ?? $validated['jenis_kendaraan'];
        $validated['nomor_lambung']   = Pengajuan::$nomorLambungMap[$validated['nomor_lambung']] ?? $validated['nomor_lambung'];

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'menunggu'; // Status awal: Menunggu verifikasi admin

        Pengajuan::create($validated);

        return redirect()
            ->route('pemeliharaan.pengajuan')
            ->with('success', 'Pengajuan pemeliharaan berhasil dikirim! Data telah masuk ke antrean verifikasi Admin.');
    }
}
