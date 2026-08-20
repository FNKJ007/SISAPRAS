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
        // Ambil Bidang dari Database Akun Pengguna (Generate Akun) + Fallback
        $bidangUserDb = \App\Models\User::whereNotNull('bidang')
            ->where('bidang', '!=', '')
            ->get()
            ->pluck('bidang')
            ->map(fn($b) => ucwords(strtolower(trim($b))))
            ->concat(['Pemadam', 'Rescue', 'Command Center', 'Sekretariat', 'Sarana Prasarana'])
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $bidangList = [];
        foreach ($bidangUserDb as $b) {
            $key = strtolower(str_replace([' ', '-'], '_', $b));
            $bidangList[$key] = $b;
        }

        // Ambil Regu dari Database Akun Pengguna (Generate Akun) + Fallback
        $reguUserDb = \App\Models\User::whereNotNull('regu')
            ->where('regu', '!=', '')
            ->get()
            ->pluck('regu')
            ->map(fn($r) => ucwords(strtolower(trim($r))))
            ->concat(['Regu 1', 'Regu 2', 'Regu 3', 'Regu 4'])
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $reguList = [];
        foreach ($reguUserDb as $r) {
            $key = strtolower(str_replace([' ', '-'], '_', $r));
            $reguList[$key] = $r;
        }

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

            $unitData = [
                'key'             => $key,
                'label'           => $label,
                'clean_label'     => $label,
                'nomor_lambung'   => $u->nomor_lambung,
                'plat_nomor'      => $u->plat_nomor,
                'jenis_kendaraan' => in_array(strtoupper(trim($u->jenis_kendaraan ?? '')), ['R2', 'R3']) ? strtoupper(trim($u->jenis_kendaraan)) : ucwords(strtolower(trim($u->jenis_kendaraan ?? ''))),
                'peruntukan'      => $u->peruntukan,
                'pos'             => $u->pos,
                'kategori'        => $u->kategori,
                'pengemudi_1'     => $u->pengemudi_1 && $u->pengemudi_1 !== '—' ? $u->pengemudi_1 : '',
            ];

            $unitList[] = $unitData;
            $nomorLambungList[$key] = $label;
            $unitDetails[$key] = $unitData;
        }

        // Ambil daftar Jenis Kendaraan langsung dari Master Data Unit (Deduplikasi & Title Case)
        $jenisKendaraanDb = Unit::whereNotNull('jenis_kendaraan')
            ->where('jenis_kendaraan', '!=', '')
            ->get()
            ->pluck('jenis_kendaraan')
            ->map(fn($v) => in_array(strtoupper(trim($v)), ['R2', 'R3']) ? strtoupper(trim($v)) : ucwords(strtolower(trim($v))))
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

        $currentUser = auth()->user();

        // Ambil User Pejabat/Atasan (Danru & Kabid) dari Generate Akun
        $officials = \App\Models\User::whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->get(['name', 'nip', 'jabatan', 'bidang', 'pos', 'regu']);

        $danruUsers = $officials->filter(function($u) {
            $j = strtolower($u->jabatan);
            return str_contains($j, 'danru') || str_contains($j, 'komandan') || str_contains($j, 'kasi') || str_contains($j, 'seksi');
        })->values();

        $kabidUsers = $officials->filter(function($u) {
            $j = strtolower($u->jabatan);
            return str_contains($j, 'kabid') || str_contains($j, 'bidang');
        })->values();

        $defaultDanru = $danruUsers->first();
        $defaultKabid = $kabidUsers->first();

        return view('pemeliharaan.pengajuan', compact(
            'bidangList',
            'posList',
            'reguList',
            'jenisKendaraanList',
            'nomorLambungList',
            'unitList',
            'unitDetails',
            'currentUser',
            'danruUsers',
            'kabidUsers',
            'defaultDanru',
            'defaultKabid'
        ));
    }

    /**
     * Menyimpan data pengajuan yang dikirim dari form.
     */
    public function store(Request $request)
    {
        $messages = [
            'bidang.required'             => 'Silakan pilih Bidang / Sektor Anda.',
            'pos.required'                => 'Silakan pilih Pos Penempatan armada.',
            'regu.required'               => 'Silakan pilih Regu tugas.',
            'jenis_kendaraan.required'    => 'Silakan pilih Jenis Kendaraan.',
            'nomor_lambung.required'      => 'Silakan pilih No. Lambung / Armada.',
            'item_perbaikan.required'     => 'Deskripsi perbaikan / kerusakan wajib diisi.',
            'nama_pemegang.required'      => 'Nama pemegang / pengemudi wajib diisi.',
            'nip_pemegang.required'       => 'NIP pemegang / pengemudi wajib diisi.',
            'nama_komandan_regu.required' => 'Nama Komandan Regu (Danru) wajib diisi.',
            'nip_komandan_regu.required'  => 'NIP Komandan Regu (Danru) wajib diisi.',
            'nama_kepala_bidang.required' => 'Nama Kepala Bidang (Kabid) wajib diisi.',
            'nip_kepala_bidang.required'  => 'NIP Kepala Bidang (Kabid) wajib diisi.',
        ];

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
        ], $messages);

        if (!empty($validated['item_perbaikan'])) {
            $rawItems = preg_split('/[,;\n\r]+/', $validated['item_perbaikan']);
            $cleanItems = array_map(function ($item) {
                return ucwords(strtolower(trim($item)));
            }, $rawItems);
            $cleanItems = array_values(array_filter($cleanItems));
            $validated['item_perbaikan'] = implode(', ', $cleanItems);
        }

        if (!empty($validated['pos'])) {
            $pos = trim($validated['pos']);
            if (str_starts_with(strtolower($pos), 'pos ')) {
                $pos = trim(substr($pos, 4));
            }
            $validated['pos'] = $pos;
        }

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'menunggu'; // Status awal: Menunggu verifikasi admin

        $pengajuan = Pengajuan::create($validated);
        \App\Http\Controllers\Admin\InvoiceController::syncPengajuanToAktualInvoice($pengajuan);

        return redirect()
            ->route('pemeliharaan.pengajuan')
            ->with('success', 'Pengajuan pemeliharaan berhasil dikirim! Data telah masuk ke antrean verifikasi Admin.');
    }
}
