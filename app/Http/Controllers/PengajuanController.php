<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Pos;
use App\Models\Unit;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * Menampilkan form Pengajuan Pemeliharaan Unit Operasional.
     */
    public function index()
    {
        $bidangList         = Pengajuan::$bidangMap;
        $reguList           = Pengajuan::$reguMap;
        $jenisKendaraanList = Pengajuan::$jenisKendaraanMap;

        // Ambil Pos dari Database Admin Data Pos
        $posDb = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();
        $posList = [];
        foreach ($posDb as $p) {
            $posList[strtolower(str_replace(' ', '', $p->nama))] = $p->nama;
        }
        if (empty($posList)) {
            $posList = Pengajuan::$posMap;
        }

        // Ambil Unit Kendaraan dari Database Admin Data Unit
        $unitDb = Unit::where('status', 'aktif')->orderBy('nomor_lambung', 'asc')->get();
        $nomorLambungList = [];
        foreach ($unitDb as $u) {
            $key = strtolower(str_replace(['-', ' ', '/'], '', $u->nomor_lambung));
            $nomorLambungList[$key] = "{$u->nomor_lambung} / {$u->plat_nomor} ({$u->merk_tipe})";
        }
        if (empty($nomorLambungList)) {
            $nomorLambungList = Pengajuan::$nomorLambungMap;
        }

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

        // Transform / fallback map jika key dikirim
        $validated['bidang']          = Pengajuan::$bidangMap[$validated['bidang']] ?? $validated['bidang'];
        $validated['regu']            = Pengajuan::$reguMap[$validated['regu']] ?? $validated['regu'];
        $validated['jenis_kendaraan'] = Pengajuan::$jenisKendaraanMap[$validated['jenis_kendaraan']] ?? $validated['jenis_kendaraan'];

        $validated['user_id'] = auth()->id() ?? 1;
        $validated['status']  = 'menunggu'; // Status awal: Menunggu verifikasi admin

        Pengajuan::create($validated);

        return redirect()
            ->route('pemeliharaan.pengajuan')
            ->with('success', 'Pengajuan pemeliharaan berhasil dikirim! Data telah masuk ke antrean verifikasi Admin.');
    }
}
