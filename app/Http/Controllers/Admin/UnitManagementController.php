<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Pos;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;

class UnitManagementController extends Controller
{
    /**
     * Halaman Utama Data Unit (CRUD Index).
     */
    public function index(Request $request)
    {
        Unit::syncStatusAll();

        $kategoriFilter = $request->query('kategori', 'semua');
        $statusFilter   = $request->query('status', 'semua');
        $posFilter      = $request->query('pos', 'semua');
        $searchQuery    = $request->query('search', '');

        $query = Unit::orderBy('id', 'asc');

        if ($kategoriFilter !== 'semua') {
            $query->where('kategori', 'ILIKE', $kategoriFilter);
        }

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['aktif', 'perbaikan', 'nonaktif'])) {
            $query->where('status', $statusFilter);
        }

        if ($posFilter !== 'semua') {
            $aliases = $this->posAliases($posFilter);
            $query->where(function ($q) use ($aliases) {
                foreach ($aliases as $alias) {
                    $q->orWhere('pos', 'ILIKE', '%' . $alias . '%');
                }
            });
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nomor_lambung', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('plat_nomor', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('no_rangka_mesin', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('merk_tipe', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('jenis_kendaraan', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('peruntukan', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_1', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_2', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $unitList = $query->paginate(15)->withQueryString();

        $kpi = CacheService::rememberStats('unit_kpi', function () {
            $kpiRaw = Unit::selectRaw("
                count(*) as total,
                count(case when lower(kategori) like '%pemadam%' then 1 end) as pemadam,
                count(case when lower(kategori) like '%rescue%' then 1 end) as rescue,
                count(case when status = 'aktif' then 1 end) as aktif,
                count(case when status = 'perbaikan' then 1 end) as perbaikan
            ")->first();

            return [
                'total'     => (int) ($kpiRaw->total ?? 0),
                'pemadam'   => (int) ($kpiRaw->pemadam ?? 0),
                'rescue'    => (int) ($kpiRaw->rescue ?? 0),
                'aktif'     => (int) ($kpiRaw->aktif ?? 0),
                'perbaikan' => (int) ($kpiRaw->perbaikan ?? 0),
            ];
        });

        $posList = CacheService::rememberList('active_pos_objects', function () {
            return Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get(['id', 'nama']);
        });

        $typeData = CacheService::rememberStats('unit_types', function () {
            $defaultTypes = ['Pancar', 'Supply', 'Pompa', 'Rescue', 'R2', 'R3', 'Pick Up', 'Lainnya'];
            $defaultKategori = ['Pemadam', 'Rescue', 'Pencegahan', 'Komando'];

            $dbTypes = Unit::whereNotNull('jenis_kendaraan')
                ->where('jenis_kendaraan', '!=', '')
                ->where('jenis_kendaraan', '!=', 'Komando')
                ->distinct()
                ->pluck('jenis_kendaraan')
                ->map(function ($v) {
                    $v = trim($v);
                    if (in_array(strtoupper($v), ['R2', 'R3', 'R4'])) {
                        return strtoupper($v);
                    }
                    return ucwords(strtolower($v));
                })
                ->toArray();

            $existingJenisList = collect($defaultTypes)
                ->merge($dbTypes)
                ->filter(fn($v) => $v !== 'Komando')
                ->unique()
                ->values()
                ->toArray();

            $dbKategori = Unit::whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->distinct()
                ->pluck('kategori')
                ->map(fn($v) => ucwords(strtolower(str_replace('_', ' ', trim($v)))))
                ->toArray();

            $existingKategoriList = collect($defaultKategori)
                ->merge($dbKategori)
                ->unique()
                ->values()
                ->toArray();

            return compact('existingJenisList', 'existingKategoriList');
        });

        $existingJenisList = $typeData['existingJenisList'];
        $existingKategoriList = $typeData['existingKategoriList'];

        $pegawaiList = CacheService::rememberList('pegawai_list', function () {
            return User::orderBy('name', 'asc')->get(['id', 'name', 'nip', 'jabatan', 'pos']);
        });

        return view('admin.pemeliharaan.data-unit.index', compact(
            'unitList',
            'kpi',
            'kategoriFilter',
            'statusFilter',
            'posFilter',
            'searchQuery',
            'posList',
            'pegawaiList',
            'existingJenisList',
            'existingKategoriList'
        ));
    }

    /**
     * Alias/kode singkat posko — data lama pada kolom `pos` unit sering
     * tersimpan sebagai kode singkat (SOREANG, TKI, PACIRA) alih-alih
     * nama lengkap posko (Soreang (MAKO), Margaasih (TKI), Ciwidey (Pacira)).
     * Mapping ini menyamakan keduanya supaya filter Posko tetap match.
     */
    protected function posAliases(string $namaPos): array
    {
        $map = [
            'Baleendah'        => ['Baleendah'],
            'Cicalengka'       => ['Cicalengka'],
            'Cileunyi'         => ['Cileunyi'],
            'Ciparay'          => ['Ciparay'],
            'Ciwidey (Pacira)' => ['Ciwidey', 'Pacira'],
            'Majalaya'         => ['Majalaya'],
            'Margaasih (TKI)'  => ['Margaasih', 'TKI'],
            'Pangalengan'      => ['Pangalengan'],
            'Soreang (MAKO)'   => ['Soreang', 'MAKO'],
        ];

        return $map[$namaPos] ?? [$namaPos];
    }

    public function store(Request $request)
    {
        $messages = [
            'nama.required'           => 'Nama unit kendaraan wajib diisi.',
            'kategori.required'       => 'Kategori unit (Pemadam/Rescue) wajib diisi.',
            'status.required'         => 'Status operasional unit wajib diisi.',
            'tahun_pembuatan.integer' => 'Tahun pembuatan harus berupa angka tahun (contoh: 2018).',
            'tahun_pembuatan.min'     => 'Tahun pembuatan minimal adalah tahun 1950.',
            'tahun_pembuatan.max'     => 'Tahun pembuatan tidak boleh melebihi tahun ' . (date('Y') + 1) . '.',
            'cc.integer'              => 'Kapasitas mesin (CC) harus berupa angka bulat (contoh: 7684).',
            'cc.min'                  => 'Kapasitas mesin (CC) minimal 50 cc.',
            'cc.max'                  => 'Kapasitas mesin (CC) tidak boleh melebihi 30.000 cc.',
        ];

        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kategori'         => 'required|string|max:100',
            'nomor_lambung'    => 'nullable|string|max:100',
            'plat_nomor'       => 'nullable|string|max:100',
            'no_rangka_mesin'  => 'nullable|string|max:100',
            'merk_tipe'        => 'nullable|string|max:255',
            'tahun_pembuatan'  => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'cc'               => 'nullable|integer|min:50|max:30000',
            'jenis_kendaraan'  => 'nullable|string|max:100',
            'peruntukan'       => 'nullable|string|max:100',
            'pos'              => 'nullable|string|max:255',
            'pengemudi_1'      => 'nullable|string|max:255',
            'pengemudi_2'      => 'nullable|string|max:255',
            'status'           => 'nullable|in:aktif,perbaikan,nonaktif',
            'catatan'          => 'nullable|string',
        ], $messages);

        if (empty($validated['status'])) {
            $validated['status'] = 'aktif';
        }

        if (!empty($validated['jenis_kendaraan'])) {
            $jk = trim($validated['jenis_kendaraan']);
            $validated['jenis_kendaraan'] = in_array(strtoupper($jk), ['R2', 'R3', 'R4'])
                ? strtoupper($jk)
                : ucwords(strtolower($jk));
        }

        if (empty($validated['peruntukan'])) {
            $validated['peruntukan'] = $validated['kategori'];
        }

        Unit::create($validated);
        CacheService::invalidate('unit');

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$validated['nama']}' berhasil ditambahkan.");
    }

    /**
     * Update data unit.
     */
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $messages = [
            'nama.required'           => 'Nama unit kendaraan wajib diisi.',
            'kategori.required'       => 'Kategori unit (Pemadam/Rescue) wajib diisi.',
            'status.required'         => 'Status operasional unit wajib diisi.',
            'tahun_pembuatan.integer' => 'Tahun pembuatan harus berupa angka tahun (contoh: 2018).',
            'tahun_pembuatan.min'     => 'Tahun pembuatan minimal adalah tahun 1950.',
            'tahun_pembuatan.max'     => 'Tahun pembuatan tidak boleh melebihi tahun ' . (date('Y') + 1) . '.',
            'cc.integer'              => 'Kapasitas mesin (CC) harus berupa angka bulat (contoh: 7684).',
            'cc.min'                  => 'Kapasitas mesin (CC) minimal 50 cc.',
            'cc.max'                  => 'Kapasitas mesin (CC) tidak boleh melebihi 30.000 cc.',
        ];

        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kategori'         => 'required|string|max:100',
            'nomor_lambung'    => 'nullable|string|max:100',
            'plat_nomor'       => 'nullable|string|max:100',
            'no_rangka_mesin'  => 'nullable|string|max:100',
            'merk_tipe'        => 'nullable|string|max:255',
            'tahun_pembuatan'  => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'cc'               => 'nullable|integer|min:50|max:30000',
            'jenis_kendaraan'  => 'nullable|string|max:100',
            'peruntukan'       => 'nullable|string|max:100',
            'pos'              => 'nullable|string|max:255',
            'pengemudi_1'      => 'nullable|string|max:255',
            'pengemudi_2'      => 'nullable|string|max:255',
            'status'           => 'nullable|in:aktif,perbaikan,nonaktif',
            'catatan'          => 'nullable|string',
        ], $messages);

        if (empty($validated['status'])) {
            $validated['status'] = $unit->status ?: 'aktif';
        }

        if (!empty($validated['jenis_kendaraan'])) {
            $jk = trim($validated['jenis_kendaraan']);
            $validated['jenis_kendaraan'] = in_array(strtoupper($jk), ['R2', 'R3', 'R4'])
                ? strtoupper($jk)
                : ucwords(strtolower($jk));
        }

        if (empty($validated['peruntukan'])) {
            $validated['peruntukan'] = $validated['kategori'];
        }

        $oldStatus = $unit->status;
        $unit->update($validated);

        // Jika status unit diubah menjadi 'aktif' (Ready) dari 'perbaikan',
        // tandai semua Pengajuan perbaikan aktif unit ini menjadi 'selesai'
        if ($validated['status'] === 'aktif' && $oldStatus === 'perbaikan') {
            $activePengajuans = \App\Models\Pengajuan::where(function($q) use ($unit) {
                $q->where('unit_id', $unit->id)
                  ->orWhere('nomor_lambung', $unit->nomor_lambung);
            })
            ->where(function($q2) {
                $q2->where('status', 'disetujui')
                   ->orWhere('status_pengerjaan', '!=', 'selesai');
            })
            ->get();

            foreach ($activePengajuans as $p) {
                $p->status = 'selesai';
                $p->status_pengerjaan = 'selesai';
                $p->progress_persen = 100;
                if (empty($p->tanggal_selesai_pengerjaan)) {
                    $p->tanggal_selesai_pengerjaan = now()->format('Y-m-d');
                }
                $p->save();
            }
        }
        CacheService::invalidate('unit');

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$unit->nama}' berhasil diperbarui.");
    }

    /**
     * Tandai unit selesai servis di bengkel dan kembali siap operasi (Ready).
     */
    public function setReady($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->status = 'aktif';
        $unit->saveQuietly();

        // Tandai seluruh pengajuan perbaikan yang masih aktif/berjalan menjadi selesai
        $activePengajuans = \App\Models\Pengajuan::where(function($q) use ($unit) {
            $q->where('unit_id', $unit->id)
              ->orWhere('nomor_lambung', $unit->nomor_lambung);
        })
        ->where(function($q2) {
            $q2->where('status', 'disetujui')
               ->orWhere('status_pengerjaan', '!=', 'selesai');
        })
        ->get();

        foreach ($activePengajuans as $p) {
            $p->status = 'selesai';
            $p->status_pengerjaan = 'selesai';
            $p->progress_persen = 100;
            if (empty($p->tanggal_selesai_pengerjaan)) {
                $p->tanggal_selesai_pengerjaan = now()->format('Y-m-d');
            }
            $p->save();
        }

        Unit::syncStatusAll();
        CacheService::invalidate('unit');

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Unit '{$unit->nomor_lambung}' ({$unit->plat_nomor}) berhasil ditandai selesai perbaikan dan kembali Siap Operasi (Ready)!");
    }

    /**
     * Hapus data unit.
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $nama = $unit->nama;
        $unit->delete();
        CacheService::invalidate('unit');

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$nama}' berhasil dihapus.");
    }

    /**
     * Hapus Opsi Riwayat (Typo/Kesalahan) dari Database Data Unit.
     */
    public function removeHistoryOption(Request $request)
    {
        $request->validate([
            'type'  => 'required|string|in:jenis_kendaraan,peruntukan,kategori',
            'value' => 'required|string',
        ]);

        $type  = $request->input('type');
        $value = trim($request->input('value'));

        if ($type === 'jenis_kendaraan') {
            Unit::where('jenis_kendaraan', 'ILIKE', $value)->update(['jenis_kendaraan' => null]);
        } elseif ($type === 'peruntukan') {
            Unit::where('peruntukan', 'ILIKE', $value)->update(['peruntukan' => null]);
        } elseif ($type === 'kategori') {
            Unit::where('kategori', 'ILIKE', $value)->update(['kategori' => null]);
        }
        CacheService::invalidate('unit');

        return response()->json([
            'success' => true,
            'message' => "Opsi riwayat '{$value}' berhasil dihapus dari data unit."
        ]);
    }

    /**
     * Ambil data Buku Servis Digital (Riwayat Pemeliharaan per Unit Kendaraan).
     */
    public function riwayatServis($id)
    {
        $unit = Unit::findOrFail($id);

        // Cari Pengajuan Perbaikan yang terkait dengan Unit ini langsung dari DB
        $pengajuanList = \App\Models\Pengajuan::where(function ($q) use ($unit) {
                $q->where('unit_id', $unit->id);
                if (!empty($unit->nomor_lambung)) {
                    $q->orWhere('nomor_lambung', 'ILIKE', '%' . $unit->nomor_lambung . '%');
                }
                if (!empty($unit->plat_nomor)) {
                    $q->orWhere('nomor_lambung', 'ILIKE', '%' . $unit->plat_nomor . '%');
                }
            })
            ->latest('id')
            ->get();

        // Cari Invoice / Realisasi Pembayaran yang terkait dengan Unit ini langsung dari DB
        $invoiceList = \App\Models\Invoice::where(function ($q) use ($unit) {
                $q->where('unit_id', $unit->id);
                if (!empty($unit->nomor_lambung)) {
                    $q->orWhere('no_lambung', 'ILIKE', '%' . $unit->nomor_lambung . '%');
                }
                if (!empty($unit->plat_nomor)) {
                    $q->orWhere('no_pol', 'ILIKE', '%' . $unit->plat_nomor . '%');
                }
            })
            ->latest('tanggal_invoice')
            ->get();

        // Pisahkan menjadi Kartu Kendali Pembayaran vs Kartu Kendali Aktual
        $kendaliPembayaranList = $invoiceList->filter(fn($inv) => ($inv->kategori_monitoring ?? 'invoice') !== 'aktual')->values();
        $kendaliAktualList     = $invoiceList->filter(fn($inv) => ($inv->kategori_monitoring ?? '') === 'aktual')->values();

        $totalBiayaPembayaran = (float) $kendaliPembayaranList->sum('total_biaya');
        $totalBiayaAktual     = (float) $kendaliAktualList->sum('total_biaya');
        $totalBiaya           = (float) $invoiceList->sum('total_biaya');

        $latestPengajuan = $pengajuanList->first();
        $latestInvoice   = $invoiceList->first();

        $terakhirServisDate = $latestPengajuan?->tanggal_mulai_pengerjaan
            ?? ($latestPengajuan?->tanggal_keberangkatan
            ?? ($latestPengajuan?->created_at
            ?? $latestInvoice?->tanggal_invoice));

        $terakhirServis = $terakhirServisDate ? $terakhirServisDate->format('d/m/Y') : '—';

        return response()->json([
            'success' => true,
            'unit' => [
                'id'              => $unit->id,
                'nama'            => $unit->nama,
                'nomor_lambung'   => $unit->nomor_lambung,
                'plat_nomor'      => $unit->plat_nomor,
                'jenis_kendaraan' => $unit->jenis_kendaraan ?? '—',
                'merk_tipe'       => $unit->merk_tipe ?? '—',
                'tahun_pembuatan' => $unit->tahun_pembuatan ?? '—',
                'pos'             => $unit->pos ?? '—',
                'status'          => $unit->status ?? 'aktif',
                'pengemudi_1'     => $unit->pengemudi_1 ?? '—',
                'pengemudi_2'     => $unit->pengemudi_2 ?? '—',
            ],
            'summary' => [
                'total_pengajuan'        => $pengajuanList->count(),
                'total_biaya_pembayaran' => 'Rp ' . number_format($totalBiayaPembayaran, 0, ',', '.'),
                'total_biaya_aktual'     => 'Rp ' . number_format($totalBiayaAktual, 0, ',', '.'),
                'total_biaya'            => 'Rp ' . number_format($totalBiaya, 0, ',', '.'),
                'terakhir_servis'        => $terakhirServis,
            ],
            'pengajuan_history' => $pengajuanList->map(function ($p) {
                $tglFormat = ($p->tanggal_mulai_pengerjaan ?? $p->tanggal_keberangkatan)?->format('d/m/Y')
                    ?? ($p->created_at ? $p->created_at->format('d/m/Y') : '—');

                return [
                    'id'             => $p->id,
                    'created_at'     => $tglFormat,
                    'item_perbaikan' => $p->item_perbaikan,
                    'nama_pemegang'  => $p->nama_pemegang,
                    'pos'            => $p->pos,
                ];
            }),
            'kendali_pembayaran_history' => $kendaliPembayaranList->map(function ($inv) {
                return [
                    'id'              => $inv->id,
                    'nomor_invoice'   => $inv->nomor_invoice,
                    'tanggal_invoice' => $inv->tanggal_invoice ? $inv->tanggal_invoice->format('d/m/Y') : '—',
                    'nama_bengkel'    => $inv->nama_bengkel ?? '—',
                    'total_biaya'     => 'Rp ' . number_format((float)$inv->total_biaya, 0, ',', '.'),
                ];
            }),
            'kendali_aktual_history' => $kendaliAktualList->map(function ($inv) {
                return [
                    'id'              => $inv->id,
                    'nomor_invoice'   => $inv->nomor_invoice,
                    'tanggal_invoice' => $inv->tanggal_invoice ? $inv->tanggal_invoice->format('d/m/Y') : '—',
                    'nama_bengkel'    => $inv->nama_bengkel ?? '—',
                    'total_biaya'     => 'Rp ' . number_format((float)$inv->total_biaya, 0, ',', '.'),
                ];
            }),
        ]);
    }

    /**
     * Cetak Dokumen Formal Buku Servis Digital Armada Kendaraan Dinas.
     */
    public function cetakBukuServis($id)
    {
        $unit = Unit::findOrFail($id);

        // Cari Pengajuan Perbaikan yang terkait dengan Unit ini langsung dari DB
        $pengajuanList = \App\Models\Pengajuan::where(function ($q) use ($unit) {
                $q->where('unit_id', $unit->id);
                if (!empty($unit->nomor_lambung)) {
                    $q->orWhere('nomor_lambung', 'ILIKE', '%' . $unit->nomor_lambung . '%');
                }
                if (!empty($unit->plat_nomor)) {
                    $q->orWhere('nomor_lambung', 'ILIKE', '%' . $unit->plat_nomor . '%');
                }
            })
            ->latest('id')
            ->get();

        // Cari Invoice / Realisasi Pembayaran yang terkait dengan Unit ini langsung dari DB
        $invoiceList = \App\Models\Invoice::where(function ($q) use ($unit) {
                $q->where('unit_id', $unit->id);
                if (!empty($unit->nomor_lambung)) {
                    $q->orWhere('no_lambung', 'ILIKE', '%' . $unit->nomor_lambung . '%');
                }
                if (!empty($unit->plat_nomor)) {
                    $q->orWhere('no_pol', 'ILIKE', '%' . $unit->plat_nomor . '%');
                }
            })
            ->latest('tanggal_invoice')
            ->get();

        // Pisahkan menjadi Kartu Kendali Pembayaran vs Kartu Kendali Aktual
        $kendaliPembayaranList = $invoiceList->filter(fn($inv) => ($inv->kategori_monitoring ?? 'invoice') !== 'aktual')->values();
        $kendaliAktualList     = $invoiceList->filter(fn($inv) => ($inv->kategori_monitoring ?? '') === 'aktual')->values();

        $totalBiayaPembayaran = (float) $kendaliPembayaranList->sum('total_biaya');
        $totalBiayaAktual     = (float) $kendaliAktualList->sum('total_biaya');
        $totalBiaya           = (float) $invoiceList->sum('total_biaya');

        $latestPengajuan = $pengajuanList->first();
        $latestInvoice   = $invoiceList->first();

        $terakhirServisDate = $latestPengajuan?->tanggal_mulai_pengerjaan
            ?? ($latestPengajuan?->tanggal_keberangkatan
            ?? ($latestPengajuan?->created_at
            ?? $latestInvoice?->tanggal_invoice));

        $terakhirServis = $terakhirServisDate ? $terakhirServisDate->format('d/m/Y') : '—';

        return view('admin.pemeliharaan.data-unit.cetak-buku-servis', compact(
            'unit',
            'pengajuanList',
            'kendaliPembayaranList',
            'kendaliAktualList',
            'totalBiayaPembayaran',
            'totalBiayaAktual',
            'totalBiaya',
            'terakhirServis'
        ));
    }
}
