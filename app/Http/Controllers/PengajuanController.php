<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Pos;
use App\Models\Unit;
use App\Services\CacheService;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    /**
     * Menampilkan form Pengajuan Pemeliharaan Unit Operasional.
     */
    public function index()
    {
        $meta = CacheService::rememberStats('pengajuan_form_meta', function () {
            // Ambil Bidang murni dari Master Data Pegawai (User)
            $bidangUserDb = \App\Models\User::whereNotNull('bidang')
                ->where('bidang', '!=', '')
                ->pluck('bidang')
                ->map(fn($b) => trim($b))
                ->filter(fn($b) => !empty($b))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $bidangList = [];
            foreach ($bidangUserDb as $b) {
                $bidangList[$b] = $b;
            }

            // Ambil Regu murni dari Master Data Regu (Regu 1 dan Regu 2)
            $reguDb = \App\Models\Regu::distinct()
                ->orderBy('nama', 'asc')
                ->pluck('nama')
                ->map(fn($r) => ucwords(strtolower(trim($r))))
                ->unique()
                ->values()
                ->toArray();

            if (empty($reguDb)) {
                $reguDb = ['Regu 1', 'Regu 2'];
            }

            $reguList = [];
            foreach ($reguDb as $r) {
                $reguList[$r] = $r;
            }

            // Ambil Pos dari Database Admin Data Pos
            $posDb = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();
            $posList = [];
            foreach ($posDb as $p) {
                $posList[$p->nama] = $p->nama;
            }

            // Ambil Unit Kendaraan dari Database Admin Data Unit
            $unitDb = Unit::where('status', 'aktif')->orderBy('nomor_lambung', 'asc')->get();
            $unitList = [];
            $nomorLambungList = [];
            $unitDetails = [];
            foreach ($unitDb as $u) {
                $label = $u->nomor_lambung;
                if ($u->plat_nomor) $label .= ' / ' . $u->plat_nomor;

                $unitData = [
                    'id'              => $u->id,
                    'key'             => $u->nomor_lambung,
                    'label'           => $label,
                    'clean_label'     => $label,
                    'nomor_lambung'   => $u->nomor_lambung,
                    'plat_nomor'      => $u->plat_nomor,
                    'jenis_kendaraan' => in_array(strtoupper(trim($u->jenis_kendaraan ?? '')), ['R2', 'R3']) ? strtoupper(trim($u->jenis_kendaraan)) : ucwords(strtolower(trim($u->jenis_kendaraan ?? ''))),
                    'peruntukan'      => $u->peruntukan,
                    'pos'             => $u->pos,
                    'kategori'        => $u->kategori,
                    'pengemudi_1'     => $u->pengemudi_1 && $u->pengemudi_1 !== '—' ? $u->pengemudi_1 : '',
                    'pengemudi_2'     => $u->pengemudi_2 && $u->pengemudi_2 !== '—' ? $u->pengemudi_2 : '',
                ];

                $unitList[] = $unitData;
                $nomorLambungList[$u->nomor_lambung] = $label;
                $unitDetails[$u->nomor_lambung] = $unitData;
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

            $allReguList = \App\Models\Regu::all(['id', 'nama', 'pos', 'bidang', 'danru', 'nip_danru'])->toArray();
            $danruUsers  = $danruUsers->toArray();
            $kabidUsers  = $kabidUsers->toArray();

            return compact('bidangList', 'reguList', 'posList', 'unitList', 'unitDetails', 'nomorLambungList', 'jenisKendaraanList', 'danruUsers', 'kabidUsers', 'allReguList');
        });

        $bidangList         = $meta['bidangList'];
        $reguList           = $meta['reguList'];
        $posList            = $meta['posList'];
        $unitList           = $meta['unitList'];
        $unitDetails        = $meta['unitDetails'];
        $nomorLambungList   = $meta['nomorLambungList'];
        $jenisKendaraanList = $meta['jenisKendaraanList'];
        $danruUsers         = collect($meta['danruUsers'] ?? [])->map(fn($u) => (object)$u);
        $kabidUsers         = collect($meta['kabidUsers'] ?? [])->map(fn($u) => (object)$u);
        $allReguList        = collect($meta['allReguList'] ?? [])->map(fn($r) => (object)$r);

        $currentUser = auth()->user();

        // Cari Unit yang dipegang khusus oleh user ini (sebagai Pengemudi 1 atau Pengemudi 2 di Data Unit)
        $userUnits = collect($unitList)->filter(function ($u) use ($currentUser) {
            if (!$currentUser) return false;
            $uName = strtolower(trim($currentUser->name ?? ''));
            if (!$uName) return false;
            $pengemudi1 = strtolower(trim($u['pengemudi_1'] ?? ''));
            $pengemudi2 = strtolower(trim($u['pengemudi_2'] ?? ''));
            return ($pengemudi1 && (str_contains($pengemudi1, $uName) || str_contains($uName, $pengemudi1))) ||
                   ($pengemudi2 && (str_contains($pengemudi2, $uName) || str_contains($uName, $pengemudi2)));
        })->values();

        // 1. Tentukan Unit Default: prioritaskan unit yang dipegang khusus oleh pengguna (misal P-01)
        $defaultUnit = null;
        if ($userUnits->isNotEmpty()) {
            $defaultUnit = $userUnits->first();
        } elseif ($currentUser && $currentUser->pos) {
            $userPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->pos));
            $posUnit = collect($unitList)->first(function ($u) use ($userPosClean) {
                $posClean = strtolower(preg_replace('/[^a-z0-9]/', '', $u['pos'] ?? ''));
                return $posClean && (str_contains($posClean, $userPosClean) || str_contains($userPosClean, $posClean));
            });
            $defaultUnit = $posUnit ?: ($unitList[0] ?? null);
        } else {
            $defaultUnit = !empty($unitList) ? $unitList[0] : null;
        }

        // 2. Dropdown Nomor Lambung: tampilkan semua unit yang ada di Pos penempatan pengguna (misal seluruh unit di Soreang)
        $posUnitsForDropdown = $unitList;
        if ($currentUser && $currentUser->pos) {
            $userPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->pos));
            $posUnits = collect($unitList)->filter(function ($u) use ($userPosClean) {
                $posClean = strtolower(preg_replace('/[^a-z0-9]/', '', $u['pos'] ?? ''));
                return $posClean && (str_contains($posClean, $userPosClean) || str_contains($userPosClean, $posClean));
            })->values();
            if ($posUnits->isNotEmpty()) {
                $posUnitsForDropdown = $posUnits->toArray();
            }
        }

        // Susun daftar Nomor Lambung sesuai unit di Pos penempatan
        $nomorLambungList = [];
        foreach ($posUnitsForDropdown as $u) {
            $nomorLambungList[$u['nomor_lambung']] = $u['label'];
        }

        // Cari Danru dari Master Data Regu sesuai Pos & Regu user
        $defaultDanru = null;
        if ($currentUser && $currentUser->pos && $currentUser->regu) {
            $userPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->pos));
            $userReguClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->regu));
            $matchedRegu = $allReguList->first(function ($r) use ($userPosClean, $userReguClean) {
                $rPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $r->pos ?? ''));
                $rReguClean = strtolower(preg_replace('/[^a-z0-9]/', '', $r->nama ?? ''));
                return (str_contains($rPosClean, $userPosClean) || str_contains($userPosClean, $rPosClean)) && $rReguClean === $userReguClean;
            });
            if ($matchedRegu && $matchedRegu->danru) {
                $defaultDanru = (object)[
                    'name' => $matchedRegu->danru,
                    'nip'  => $matchedRegu->nip_danru ?? '',
                ];
            }
        }
        if (!$defaultDanru) {
            $defaultDanru = $danruUsers->first();
        }

        // Cari Kabid sesuai Bidang user
        $defaultKabid = null;
        if ($currentUser && $currentUser->bidang) {
            $userBidangLower = strtolower($currentUser->bidang);
            $matchedKabid = $kabidUsers->first(function ($u) use ($userBidangLower) {
                $uBidangLower = strtolower($u->bidang ?? '');
                $uJabatanLower = strtolower($u->jabatan ?? '');
                return ($uBidangLower && (str_contains($userBidangLower, $uBidangLower) || str_contains($uBidangLower, $userBidangLower))) ||
                       ($uJabatanLower && str_contains($uJabatanLower, $userBidangLower));
            });
            if ($matchedKabid) {
                $defaultKabid = $matchedKabid;
            }
        }
        if (!$defaultKabid) {
            $defaultKabid = $kabidUsers->first();
        }

        // Susun opsi Danru/Kasi (gabungan Data Pegawai + Data Regu) untuk dipilih user
        $danruOptions = collect();
        foreach ($danruUsers as $u) {
            if (!empty($u->name)) {
                $danruOptions->push([
                    'name'    => $u->name,
                    'nip'     => $u->nip ?? '',
                    'jabatan' => $u->jabatan ?? 'Danru',
                    'pos'     => $u->pos ?? '',
                    'bidang'  => $u->bidang ?? '',
                ]);
            }
        }
        foreach ($allReguList as $r) {
            if (!empty($r->danru)) {
                $danruOptions->push([
                    'name'    => $r->danru,
                    'nip'     => $r->nip_danru ?? '',
                    'jabatan' => 'Danru ' . ($r->nama ?? ''),
                    'pos'     => $r->pos ?? '',
                    'bidang'  => $r->bidang ?? '',
                ]);
            }
        }
        $danruOptions = $danruOptions->unique('name')->sortBy('name')->values();

        // Susun opsi Kabid dari Data Pegawai
        $kabidOptions = $kabidUsers
            ->filter(fn($u) => !empty($u->name))
            ->map(fn($u) => [
                'name'    => $u->name,
                'nip'     => $u->nip ?? '',
                'jabatan' => $u->jabatan ?? 'Kepala Bidang',
                'pos'     => $u->pos ?? '',
                'bidang'  => $u->bidang ?? '',
            ])
            ->unique('name')
            ->sortBy('name')
            ->values();

        return view('pemeliharaan.pengajuan', compact(
            'bidangList',
            'posList',
            'reguList',
            'allReguList',
            'jenisKendaraanList',
            'nomorLambungList',
            'unitList',
            'unitDetails',
            'currentUser',
            'danruUsers',
            'kabidUsers',
            'danruOptions',
            'kabidOptions',
            'defaultUnit',
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

        // Normalisasi Unit dari Master Unit
        $unitMatch = Unit::where('nomor_lambung', 'LIKE', $validated['nomor_lambung'])
            ->orWhereRaw("REPLACE(REPLACE(nomor_lambung, '-', ''), ' ', '') LIKE ?", [str_replace(['-', ' '], '', $validated['nomor_lambung'])])
            ->first();
        if ($unitMatch) {
            $validated['nomor_lambung']   = $unitMatch->nomor_lambung;
            $validated['unit_id']         = $unitMatch->id;
            if (empty($validated['jenis_kendaraan']) || strtolower($validated['jenis_kendaraan']) === 'lainnya') {
                $validated['jenis_kendaraan'] = $unitMatch->jenis_kendaraan;
            }
        } else {
            $rawL = trim($validated['nomor_lambung']);
            if (preg_match('/^([a-zA-Z]+)[-_ ]*(\d+)$/', $rawL, $m)) {
                $validated['nomor_lambung'] = strtoupper($m[1]) . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT);
            }
        }

        // Normalisasi Pos dari Master Pos
        $posMatch = Pos::where('nama', 'LIKE', $validated['pos'])
            ->orWhereRaw("LOWER(REPLACE(REPLACE(REPLACE(nama, ' ', ''), '(', ''), ')', '')) LIKE ?", [strtolower(preg_replace('/[^a-z0-9]/', '', $validated['pos']))])
            ->first();
        if ($posMatch) {
            $validated['pos'] = $posMatch->nama;
            $validated['pos_id'] = $posMatch->id;
        } else {
            $cleanPos = strtolower(preg_replace('/[^a-z0-9]/', '', $validated['pos']));
            if (str_contains($cleanPos, 'soreang') || str_contains($cleanPos, 'mako')) {
                $validated['pos'] = 'Soreang (MAKO)';
            } elseif (str_contains($cleanPos, 'ciwidey') || str_contains($cleanPos, 'pacira')) {
                $validated['pos'] = 'Ciwidey (PACIRA)';
            } elseif (str_contains($cleanPos, 'margaasih') || str_contains($cleanPos, 'tki')) {
                $validated['pos'] = 'Margaasih (TKI)';
            } else {
                $validated['pos'] = ucwords(strtolower(trim($validated['pos'])));
            }
        }

        // Normalisasi Bidang
        $cleanB = strtolower(trim($validated['bidang']));
        if ($cleanB === 'spi' || str_contains($cleanB, 'sarana')) {
            $validated['bidang'] = 'Sarana Prasarana Dan Informasi';
        } elseif ($cleanB === 'cc' || str_contains($cleanB, 'command')) {
            $validated['bidang'] = 'Command Center';
        } else {
            $validated['bidang'] = ucwords($cleanB);
        }

        // Normalisasi Regu
        $cleanR = strtolower(trim($validated['regu']));
        if (preg_match('/regu[_\s]*([0-9]+)/i', $cleanR, $m)) {
            $num = (int)$m[1];
            $validated['regu'] = $num > 0 ? "Regu {$num}" : "Regu 1";
        } else {
            $validated['regu'] = ucwords($cleanR);
        }

        // Title Case Nama
        $validated['nama_pemegang'] = ucwords(strtolower(trim($validated['nama_pemegang'])));
        $validated['nama_komandan_regu'] = ucwords(strtolower(trim($validated['nama_komandan_regu'])));
        $parts = explode(',', $validated['nama_kepala_bidang']);
        $name = ucwords(strtolower(trim($parts[0])));
        if (count($parts) > 1) {
            $gelar = implode(',', array_slice($parts, 1));
            $validated['nama_kepala_bidang'] = $name . ',' . $gelar;
        } else {
            $validated['nama_kepala_bidang'] = $name;
        }

        if (!empty($validated['item_perbaikan'])) {
            $rawItems = preg_split('/[,;\n\r]+/', $validated['item_perbaikan']);
            $cleanItems = array_map(function ($item) {
                return ucwords(strtolower(trim($item)));
            }, $rawItems);
            $cleanItems = array_values(array_filter($cleanItems));
            $validated['item_perbaikan'] = implode(', ', $cleanItems);
        }

        $validated['user_id'] = auth()->id();
        $validated['status']  = 'menunggu'; // Status awal: Menunggu verifikasi admin

        $pengajuan = Pengajuan::create($validated);
        \App\Http\Controllers\Admin\InvoiceController::syncPengajuanToAktualInvoice($pengajuan);
        CacheService::invalidate('pengajuan');

        return redirect()
            ->route('pemeliharaan.pengajuan')
            ->with('success', 'Pengajuan pemeliharaan berhasil dikirim! Data telah masuk ke antrean verifikasi Admin.');
    }
}
