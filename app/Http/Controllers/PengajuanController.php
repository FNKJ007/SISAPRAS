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

        // Ambil Unit Kendaraan dari Database Admin Data Unit
        $unitDb = Unit::where('status', 'aktif')->orderBy('nomor_lambung', 'asc')->get();
        $unitList = [];
        $nomorLambungList = [];
        $unitDetails = [];
        foreach ($unitDb as $u) {
            $key = strtolower(str_replace(['-', ' ', '/'], '', $u->nomor_lambung));
            $label = $u->nomor_lambung;
            if ($u->plat_nomor) $label .= ' / ' . $u->plat_nomor;
            if ($u->pos) $label .= ' [' . $u->pos . ']';
            if ($u->jenis_kendaraan) $label .= ' (' . $u->jenis_kendaraan . ')';

            $unitData = [
                'key'             => $key,
                'label'           => $label,
                'nomor_lambung'   => $u->nomor_lambung,
                'plat_nomor'      => $u->plat_nomor,
                'jenis_kendaraan' => strtoupper(trim($u->jenis_kendaraan ?? '')),
                'peruntukan'      => $u->peruntukan,
                'pos'             => $u->pos,
                'kategori'        => $u->kategori,
                'pengemudi_1'     => $u->pengemudi_1 && $u->pengemudi_1 !== '—' ? $u->pengemudi_1 : '',
            ];

            $unitList[] = $unitData;
            $nomorLambungList[$key] = $label;
            $unitDetails[$key] = $unitData;
        }

        // Ambil daftar Jenis Kendaraan langsung dari Master Data Unit (Deduplikasi & Normalisasi Huruf)
        $jenisKendaraanDb = Unit::whereNotNull('jenis_kendaraan')
            ->where('jenis_kendaraan', '!=', '')
            ->get()
            ->pluck('jenis_kendaraan')
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $jenisKendaraanList = [];
        if (!empty($jenisKendaraanDb)) {
            foreach ($jenisKendaraanDb as $jk) {
                $jenisKendaraanList[$jk] = $jk;
            }
        } else {
            $jenisKendaraanList = Pengajuan::$jenisKendaraanMap;
        }

        return view('pemeliharaan.pengajuan', compact(
            'bidangList',
            'posList',
            'reguList',
            'jenisKendaraanList',
            'nomorLambungList',
            'unitList',
            'unitDetails'
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

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'menunggu'; // Status awal: Menunggu verifikasi admin

        Pengajuan::create($validated);

        return redirect()
            ->route('pemeliharaan.pengajuan')
            ->with('success', 'Pengajuan pemeliharaan berhasil dikirim! Data telah masuk ke antrean verifikasi Admin.');
    }
}
