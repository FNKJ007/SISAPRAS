<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

class UnitManagementController extends Controller
{
    /**
     * Halaman Utama Data Unit (CRUD Index).
     */
    public function index(Request $request)
    {
        $kategoriFilter = $request->query('kategori', 'semua');
        $statusFilter   = $request->query('status', 'semua');
        $posFilter      = $request->query('pos', 'semua');
        $searchQuery    = $request->query('search', '');

        $query = Unit::orderBy('id', 'asc');

        if ($kategoriFilter !== 'semua') {
            $query->where('kategori', 'LIKE', $kategoriFilter);
        }

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['aktif', 'perbaikan', 'nonaktif'])) {
            $query->where('status', $statusFilter);
        }

        if ($posFilter !== 'semua') {
            $aliases = $this->posAliases($posFilter);
            $query->where(function ($q) use ($aliases) {
                foreach ($aliases as $alias) {
                    $q->orWhereRaw('UPPER(pos) LIKE ?', ['%' . strtoupper($alias) . '%']);
                }
            });
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nomor_lambung', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('plat_nomor', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('no_rangka_mesin', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('merk_tipe', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('jenis_kendaraan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('peruntukan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('jenis_peruntukan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_1', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_2', 'LIKE', "%{$searchQuery}%");
            });
        }

        $unitList = $query->paginate(15)->withQueryString();

        $kpi = [
            'total'     => Unit::count(),
            'pemadam'   => Unit::where('kategori', 'LIKE', 'pemadam')->count(),
            'rescue'    => Unit::where('kategori', 'LIKE', 'rescue')->count(),
            'aktif'     => Unit::where('status', 'aktif')->count(),
            'perbaikan' => Unit::where('status', 'perbaikan')->count(),
        ];

        $posList = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        $defaultTypes = ['Pancar', 'Supply', 'Pompa', 'Rescue', 'R2', 'R3', 'Pick Up', 'Komando', 'Lainnya'];
        $dbTypes = Unit::whereNotNull('jenis_kendaraan')
            ->where('jenis_kendaraan', '!=', '')
            ->get()
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
            ->unique()
            ->values()
            ->toArray();

        $existingPeruntukanList = Unit::whereNotNull('peruntukan')
            ->where('peruntukan', '!=', '')
            ->get()
            ->pluck('peruntukan')
            ->map(fn($v) => ucwords(strtolower(trim($v))))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $existingKategoriList = Unit::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->get()
            ->pluck('kategori')
            ->map(fn($v) => ucwords(strtolower(str_replace('_', ' ', trim($v)))))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingKategoriList)) {
            $existingKategoriList = ['Pemadam', 'Rescue'];
        }

        return view('admin.pemeliharaan.data-unit.index', compact(
            'unitList',
            'kpi',
            'kategoriFilter',
            'statusFilter',
            'posFilter',
            'searchQuery',
            'posList',
            'existingJenisList',
            'existingPeruntukanList',
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
            'jenis_peruntukan' => 'nullable|string|max:255',
            'pos'              => 'nullable|string|max:255',
            'pengemudi_1'      => 'nullable|string|max:255',
            'pengemudi_2'      => 'nullable|string|max:255',
            'status'           => 'required|in:aktif,perbaikan,nonaktif',
            'catatan'          => 'nullable|string',
        ], $messages);

        if (!empty($validated['jenis_kendaraan'])) {
            $jk = trim($validated['jenis_kendaraan']);
            $validated['jenis_kendaraan'] = in_array(strtoupper($jk), ['R2', 'R3', 'R4'])
                ? strtoupper($jk)
                : ucwords(strtolower($jk));
        }

        Unit::create($validated);

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
            'jenis_peruntukan' => 'nullable|string|max:255',
            'pos'              => 'nullable|string|max:255',
            'pengemudi_1'      => 'nullable|string|max:255',
            'pengemudi_2'      => 'nullable|string|max:255',
            'status'           => 'required|in:aktif,perbaikan,nonaktif',
            'catatan'          => 'nullable|string',
        ], $messages);

        if (!empty($validated['jenis_kendaraan'])) {
            $jk = trim($validated['jenis_kendaraan']);
            $validated['jenis_kendaraan'] = in_array(strtoupper($jk), ['R2', 'R3', 'R4'])
                ? strtoupper($jk)
                : ucwords(strtolower($jk));
        }

        $unit->update($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$unit->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data unit.
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $nama = $unit->nama;
        $unit->delete();

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
            Unit::where('jenis_kendaraan', 'LIKE', $value)->update(['jenis_kendaraan' => null]);
        } elseif ($type === 'peruntukan') {
            Unit::where('peruntukan', 'LIKE', $value)->update(['peruntukan' => null]);
        } elseif ($type === 'kategori') {
            Unit::where('kategori', 'LIKE', $value)->update(['kategori' => null]);
        }

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
        $today = now()->format('Y-m-d');

        $unitLambungClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $unit->nomor_lambung ?? ''));
        $unitPlatClean    = $unit->plat_nomor ? strtolower(preg_replace('/[^A-Za-z0-9]/', '', $unit->plat_nomor)) : '';

        // Cari Pengajuan Perbaikan yang terkait dengan Unit ini
        $pengajuanList = \App\Models\Pengajuan::all()->filter(function ($p) use ($unit, $unitLambungClean, $unitPlatClean) {
            if (!empty($p->unit_id) && $p->unit_id == $unit->id) {
                return true;
            }
            $pLambungClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $p->nomor_lambung ?? ''));
            if ($unitLambungClean && str_contains($pLambungClean, $unitLambungClean)) {
                return true;
            }
            if ($unitPlatClean && str_contains($pLambungClean, $unitPlatClean)) {
                return true;
            }
            return false;
        })->sortByDesc('created_at')->values();

        // Cari Invoice / Realisasi Pembayaran yang terkait dengan Unit ini
        $invoiceList = \App\Models\Invoice::all()->filter(function ($inv) use ($unit, $unitLambungClean, $unitPlatClean) {
            if (!empty($inv->unit_id) && $inv->unit_id == $unit->id) {
                return true;
            }
            $invLambungClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $inv->no_lambung ?? ''));
            if ($unitLambungClean && str_contains($invLambungClean, $unitLambungClean)) {
                return true;
            }
            $invPolClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $inv->no_pol ?? ''));
            if ($unitPlatClean && str_contains($invPolClean, $unitPlatClean)) {
                return true;
            }
            return false;
        })->sortByDesc('tanggal_invoice')->values();

        // Pisahkan pengajuan yang SUDAH TERLAKSANA/BERJALAN vs JADWAL MENDATANG
        $terlaksanaList = $pengajuanList->filter(function ($p) use ($today) {
            if (in_array($p->status_pengerjaan, ['proses', 'selesai'])) {
                return true;
            }
            $keberangkatan = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
            return $keberangkatan && $keberangkatan <= $today;
        });

        $mendatangList = $pengajuanList->filter(function ($p) use ($today) {
            $keberangkatan = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
            return $keberangkatan && $keberangkatan > $today && $p->status_pengerjaan === 'belum_mulai';
        });

        // Filter invoice yang SUDAH TERLAKSANA (tidak menghitung realisasi biaya jika perbaikan masih di masa depan)
        $terlaksanaInvoiceList = $invoiceList->filter(function ($inv) use ($today, $terlaksanaList) {
            $tglInv = $inv->tanggal_invoice ? $inv->tanggal_invoice->format('Y-m-d') : null;
            if ($tglInv && $tglInv > $today) {
                return false;
            }
            if ($terlaksanaList->count() === 0) {
                return false;
            }
            return true;
        });

        $totalTerlaksana = $terlaksanaList->count();
        $totalMendatang  = $mendatangList->count();
        $totalBiaya      = (float) $terlaksanaInvoiceList->sum('total_biaya');

        $latestTerlaksana = $terlaksanaList->first();
        $latestTerlaksanaInvoice = $terlaksanaInvoiceList->first();

        $terakhirServisDate = $latestTerlaksana?->tanggal_mulai_pengerjaan
            ?? ($latestTerlaksana?->tanggal_keberangkatan
            ?? ($latestTerlaksana?->created_at
            ?? $latestTerlaksanaInvoice?->tanggal_invoice));

        $terakhirServis = $terakhirServisDate ? $terakhirServisDate->format('d/m/Y') : '—';
        $jadwalMendatang = $mendatangList->first()?->tanggal_keberangkatan?->format('d/m/Y');

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
                'total_pengajuan'  => $totalTerlaksana,
                'total_mendatang'  => $totalMendatang,
                'total_biaya'      => 'Rp ' . number_format($totalBiaya, 0, ',', '.'),
                'terakhir_servis'  => $terakhirServis,
                'jadwal_mendatang' => $jadwalMendatang,
            ],
            'pengajuan_history' => $pengajuanList->map(function ($p) use ($today) {
                $keberangkatan = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
                $isFuture = $keberangkatan && $keberangkatan > $today && $p->status_pengerjaan === 'belum_mulai';

                $tglFormat = ($p->tanggal_mulai_pengerjaan ?? $p->tanggal_keberangkatan)?->format('d/m/Y')
                    ?? ($p->created_at ? $p->created_at->format('d/m/Y') : '—');

                return [
                    'id'                => $p->id,
                    'created_at'        => $tglFormat,
                    'item_perbaikan'    => $p->item_perbaikan,
                    'nama_pemegang'     => $p->nama_pemegang,
                    'pos'               => $p->pos,
                    'status'            => $p->status,
                    'status_pengerjaan' => $isFuture ? 'jadwal_mendatang' : ($p->status_pengerjaan ?? 'belum_mulai'),
                    'is_future'         => $isFuture,
                    'jadwal_keberangkatan' => $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('d/m/Y') : null,
                    'progress_persen'   => $p->progress_persen ?? 0,
                    'progress_catatan'  => $p->progress_catatan ?? '—',
                ];
            }),
            'invoice_history' => $invoiceList->map(function ($inv) {
                return [
                    'id'              => $inv->id,
                    'nomor_invoice'   => $inv->nomor_invoice,
                    'tanggal_invoice' => $inv->tanggal_invoice ? $inv->tanggal_invoice->format('d/m/Y') : '—',
                    'total_biaya'     => 'Rp ' . number_format((float)$inv->total_biaya, 0, ',', '.'),
                    'status'          => $inv->status,
                ];
            })
        ]);
    }

    /**
     * Cetak Dokumen Formal Buku Servis Digital Armada Kendaraan Dinas.
     */
    public function cetakBukuServis($id)
    {
        $unit = Unit::findOrFail($id);
        $today = now()->format('Y-m-d');

        $unitLambungClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $unit->nomor_lambung ?? ''));
        $unitPlatClean    = $unit->plat_nomor ? strtolower(preg_replace('/[^A-Za-z0-9]/', '', $unit->plat_nomor)) : '';

        // Cari Pengajuan Perbaikan yang terkait dengan Unit ini
        $pengajuanList = \App\Models\Pengajuan::all()->filter(function ($p) use ($unit, $unitLambungClean, $unitPlatClean) {
            if (!empty($p->unit_id) && $p->unit_id == $unit->id) {
                return true;
            }
            $pLambungClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $p->nomor_lambung ?? ''));
            if ($unitLambungClean && str_contains($pLambungClean, $unitLambungClean)) {
                return true;
            }
            if ($unitPlatClean && str_contains($pLambungClean, $unitPlatClean)) {
                return true;
            }
            return false;
        })->sortByDesc('created_at')->values();

        // Cari Invoice / Realisasi Pembayaran yang terkait dengan Unit ini
        $invoiceList = \App\Models\Invoice::all()->filter(function ($inv) use ($unit, $unitLambungClean, $unitPlatClean) {
            if (!empty($inv->unit_id) && $inv->unit_id == $unit->id) {
                return true;
            }
            $invLambungClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $inv->no_lambung ?? ''));
            if ($unitLambungClean && str_contains($invLambungClean, $unitLambungClean)) {
                return true;
            }
            $invPolClean = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $inv->no_pol ?? ''));
            if ($unitPlatClean && str_contains($invPolClean, $unitPlatClean)) {
                return true;
            }
            return false;
        })->sortByDesc('tanggal_invoice')->values();

        // Pisahkan pengajuan yang SUDAH TERLAKSANA/BERJALAN vs JADWAL MENDATANG
        $terlaksanaList = $pengajuanList->filter(function ($p) use ($today) {
            if (in_array($p->status_pengerjaan, ['proses', 'selesai'])) {
                return true;
            }
            $keberangkatan = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
            return $keberangkatan && $keberangkatan <= $today;
        });

        $mendatangList = $pengajuanList->filter(function ($p) use ($today) {
            $keberangkatan = $p->tanggal_keberangkatan ? $p->tanggal_keberangkatan->format('Y-m-d') : null;
            return $keberangkatan && $keberangkatan > $today && $p->status_pengerjaan === 'belum_mulai';
        });

        $terlaksanaInvoiceList = $invoiceList->filter(function ($inv) use ($today, $terlaksanaList) {
            $tglInv = $inv->tanggal_invoice ? $inv->tanggal_invoice->format('Y-m-d') : null;
            if ($tglInv && $tglInv > $today) {
                return false;
            }
            if ($terlaksanaList->count() === 0) {
                return false;
            }
            return true;
        });

        $totalTerlaksana = $terlaksanaList->count();
        $totalMendatang  = $mendatangList->count();
        $totalBiaya      = (float) $terlaksanaInvoiceList->sum('total_biaya');

        $latestTerlaksana = $terlaksanaList->first();
        $latestTerlaksanaInvoice = $terlaksanaInvoiceList->first();

        $terakhirServisDate = $latestTerlaksana?->tanggal_mulai_pengerjaan
            ?? ($latestTerlaksana?->tanggal_keberangkatan
            ?? ($latestTerlaksana?->created_at
            ?? $latestTerlaksanaInvoice?->tanggal_invoice));

        $terakhirServis = $terakhirServisDate ? $terakhirServisDate->format('d/m/Y') : '—';
        $jadwalMendatang = $mendatangList->first()?->tanggal_keberangkatan?->format('d/m/Y');

        return view('admin.pemeliharaan.data-unit.cetak-buku-servis', compact(
            'unit',
            'pengajuanList',
            'invoiceList',
            'terlaksanaList',
            'mendatangList',
            'terlaksanaInvoiceList',
            'totalTerlaksana',
            'totalMendatang',
            'totalBiaya',
            'terakhirServis',
            'jadwalMendatang',
            'today'
        ));
    }
}
