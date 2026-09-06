<?php

namespace App\Traits;

use App\Models\CekHarianUnit;
use App\Models\CekHarianAlat;
use App\Models\Pengajuan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\OptimizesPdfImages;

trait HandlesCekHarianUnit
{
    use OptimizesPdfImages;

    /**
     * Generate & unduh PDF hasil Cek Harian Unit (Pemadam / Rescue / Pencegahan).
     */
    protected function exportCekHarianUnitPdf(int $id, string $kategori)
    {
        try {
            $record = CekHarianUnit::find($id);
            if (!$record) {
                return redirect()->back()->with('error', 'Data pemeriksaan unit tidak ditemukan.');
            }

            $kategori = !empty($record->kategori) ? strtolower($record->kategori) : $kategori;

            if (function_exists('set_time_limit')) {
                @set_time_limit(300);
            }
            @ini_set('max_execution_time', 300);
            @ini_set('memory_limit', '512M');

            Pdf::setOptions(["isRemoteEnabled" => false, "isHtml5ParserEnabled" => true]);

            $buktiPemanasanData = $this->imageToDataUri($record->bukti_pemanasan);
            $buktiBbmData       = $this->imageToDataUri($record->bukti_bbm);
            $buktiPencucianData = $this->imageToDataUri($record->bukti_pencucian ?? null);

            $dokTangkiData = [];
            foreach ($record->dokumentasi_tangki_pompa ?? [] as $p) {
                $d = $this->imageToDataUri($p);
                if ($d) {
                    $dokTangkiData[] = $d;
                }
            }

            $logoData = null;
            $logoPath = public_path('images/logo-damkar.png');
            if (file_exists($logoPath)) {
                $logoData = 'data:' . (mime_content_type($logoPath) ?: 'image/png') . ';base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pemeriksaNip = $record->user->nip ?? \App\Models\User::where('name', $record->nama_pemeriksa)->value('nip') ?? '';

            $danruNip = '';
            if (!empty($record->nama_danru)) {
                $danruNip = \App\Models\User::where('name', $record->nama_danru)->value('nip')
                    ?? \App\Models\User::where('name', 'ILIKE', '%' . $record->nama_danru . '%')->value('nip') ?? '';
            }

            $kabidNip = '';
            $kabidLabel = match($kategori) {
                'rescue'     => 'Kepala Bidang Penyelamatan',
                'pencegahan' => 'Kepala Bidang Pencegahan Kebakaran',
                default      => 'Kepala Bidang Pemadaman',
            };
            if (!empty($record->nama_kabid)) {
                $kabidUser = \App\Models\User::where('name', $record->nama_kabid)->first()
                    ?? \App\Models\User::where('name', 'ILIKE', '%' . $record->nama_kabid . '%')->first();
                if ($kabidUser) {
                    $kabidNip = $kabidUser->nip ?? '';
                    if (!empty($kabidUser->jabatan)) {
                        $kabidLabel = $kabidUser->jabatan;
                    }
                }
            }

            $pdf = Pdf::loadView('pdf.cek-harian-unit', [
                'record'               => $record,
                'unit'                 => $record->unit,
                'kategori'             => $kategori,
                'logo_data'            => $logoData,
                'pemeriksa_nip'        => $pemeriksaNip,
                'danru_nip'            => $danruNip,
                'kabid_nip'            => $kabidNip,
                'kabid_label'          => $kabidLabel,
                'judul'                => match($kategori) {
                    'rescue'     => 'Hasil Cek Harian Unit Kendaraan Rescue',
                    'pencegahan' => 'Hasil Cek Harian Unit Kendaraan Pencegahan',
                    default      => 'Hasil Cek Harian Unit Kendaraan Pemadam',
                },
                'bukti_pemanasan_data' => $buktiPemanasanData,
                'bukti_bbm_data'       => $buktiBbmData,
                'bukti_pencucian_data' => $buktiPencucianData,
                'dok_tangki_data'      => $dokTangkiData,
            ])->setPaper('a4', 'portrait');

            $tglStr = $record->tanggal_pemeriksaan instanceof \Carbon\Carbon
                ? $record->tanggal_pemeriksaan->format('Y-m-d')
                : substr(str_replace('/', '-', (string)$record->tanggal_pemeriksaan), 0, 10);

            $unitClean = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string)($record->unit_nama ?? 'unit'));
            $namaFile = "cek-harian-unit-{$kategori}-{$unitClean}-{$tglStr}-{$record->id}.pdf";

            return $pdf->download($namaFile);
        } catch (\Throwable $e) {
            \Log::error('Export CekHarianUnit PDF failed: ' . $e->getMessage(), ['id' => $id, 'kategori' => $kategori]);
            return redirect()->back()->with('error', 'Gagal membuat PDF: ' . $e->getMessage());
        }
    }
    /**
     * Helper terpusat untuk memproses & menyimpan Cek Harian Unit (Pemadam / Rescue).
     */
    protected function storeCekHarianUnit(Request $request, string $kategori, array $perlengkapanLabels): CekHarianUnit
    {
        $messages = [
            'nama_pemeriksa.required'          => 'Nama petugas pemeriksa wajib diisi.',
            'jabatan.required'                 => 'Jabatan petugas pemeriksa wajib diisi.',
            'unit_id.required'                 => 'Silakan pilih unit kendaraan yang diperiksa.',
            'pos.required'                     => 'Silakan pilih pos tempat pemeriksaan.',
            'jenis_bbm.required'               => 'Silakan pilih jenis BBM kendaraan.',
            'bukti_pemanasan.required'         => 'Foto bukti pemanasan kendaraan wajib dilampirkan.',
            'bukti_pemanasan.image'            => 'Foto bukti pemanasan harus berupa file gambar (JPG/PNG/WebP).',
            'bukti_pemanasan.mimes'            => 'Format foto bukti pemanasan harus berupa JPG, PNG, atau WebP.',
            'bukti_pemanasan.max'              => 'Ukuran foto bukti pemanasan tidak boleh melebihi 10 MB.',
            'bukti_pemanasan.uploaded'         => 'Foto bukti pemanasan gagal diunggah (pastikan file < 10MB dan konfigurasi server sesuai).',
            'bukti_bbm.required'               => 'Foto bukti level BBM wajib dilampirkan.',
            'bukti_bbm.image'                  => 'Foto bukti BBM harus berupa file gambar (JPG/PNG/WebP).',
            'bukti_bbm.mimes'                  => 'Format foto bukti BBM harus berupa JPG, PNG, atau WebP.',
            'bukti_bbm.max'                    => 'Ukuran foto bukti BBM tidak boleh melebihi 10 MB.',
            'bukti_bbm.uploaded'               => 'Foto bukti level BBM gagal diunggah (pastikan file < 10MB dan konfigurasi server sesuai).',
            'bukti_pencucian.required'         => 'Foto kegiatan pasukan membersihkan unit wajib dilampirkan.',
            'bukti_pencucian.image'            => 'Foto bukti pencucian harus berupa file gambar (JPG/PNG/WebP).',
            'bukti_pencucian.mimes'            => 'Format foto bukti pencucian harus berupa JPG, PNG, atau WebP.',
            'bukti_pencucian.max'              => 'Ukuran foto bukti pencucian tidak boleh melebihi 10 MB.',
            'bukti_pencucian.uploaded'         => 'Foto bukti pencucian gagal diunggah (pastikan file < 10MB dan konfigurasi server sesuai).',
            'kebersihan_unit.required'          => 'Silakan pilih kondisi kebersihan unit.',
            'level_air.required'                => 'Silakan pilih level air tangki.',
            'kondisi_tangki_air.required'       => 'Silakan pilih kondisi tangki air.',
            'kebocoran_tangki_air.required'     => 'Silakan pilih kondisi kebocoran tangki air.',
            'tekanan_pompa.required'            => 'Silakan pilih kondisi tekanan pompa.',
            'selang_induk.required'             => 'Silakan pilih kondisi selang induk.',
            'dokumentasi_tangki_pompa.required' => 'Foto dokumentasi pengecekan tangki dan pompa wajib dilampirkan.',
            'dokumentasi_tangki_pompa.min'      => 'Foto dokumentasi tangki dan pompa wajib dilampirkan minimal 1 foto.',
            'dokumentasi_tangki_pompa.max'      => 'Foto dokumentasi tangki dan pompa maksimal 3 foto.',
            'dokumentasi_tangki_pompa.*.image'  => 'Foto dokumentasi tangki/pompa harus berupa file gambar (JPG/PNG/WebP).',
            'dokumentasi_tangki_pompa.*.mimes'  => 'Format foto dokumentasi tangki/pompa harus berupa JPG, PNG, atau WebP.',
            'dokumentasi_tangki_pompa.*.max'    => 'Ukuran foto dokumentasi tangki/pompa maksimal 10 MB per file.',
            'dokumentasi_tangki_pompa.*.uploaded'=> 'Salah satu foto dokumentasi tangki/pompa gagal diunggah (pastikan file < 10MB).',
        ];

        $isPemadam = strtolower($kategori) === 'pemadam';

        $rules = [
            'nama_pemeriksa'             => 'required|string|max:255',
            'jabatan'                    => 'required|string|max:255',
            'pos'                        => 'required|string|max:255',
            'nama_danru'                 => 'nullable|string|max:255',
            'nama_kabid'                 => 'nullable|string|max:255',
            'unit_id'                    => 'required|integer',
            'kebersihan_unit'            => 'required|string|in:bersih,tidak_bersih',
            'tanggal'                    => 'nullable|date',
            'jenis_bbm'                  => 'required|string|max:50',
            'bukti_pemanasan'            => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'bukti_pencucian'            => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'bukti_bbm'                  => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'level_air'                  => $isPemadam ? 'required|string|max:100' : 'nullable|string|max:100',
            'kondisi_tangki_air'         => $isPemadam ? 'required|string|max:100' : 'nullable|string|max:100',
            'kebocoran_tangki_air'       => $isPemadam ? 'required|string|max:100' : 'nullable|string|max:100',
            'tekanan_pompa'              => $isPemadam ? 'required|string|max:100' : 'nullable|string|max:100',
            'selang_induk'               => $isPemadam ? 'required|string|max:100' : 'nullable|string|max:100',
            'catatan_tangki_pompa'       => 'nullable|string',
            'dokumentasi_tangki_pompa'   => $isPemadam ? 'required|array|min:1|max:3' : 'nullable|array',
            'dokumentasi_tangki_pompa.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'perlengkapan'               => 'nullable|array',
            'catatan'                    => 'nullable|string',
        ];

        $validated = $request->validate($rules, $messages);

        $unitObj  = Unit::find($validated['unit_id']);
        $unitNama = $unitObj ? "{$unitObj->nomor_lambung} ({$unitObj->plat_nomor})" : ("Unit #" . $validated['unit_id']);

        // Handle File Uploads
        $buktiPemanasanPath = null;
        if ($request->hasFile('bukti_pemanasan')) {
            $buktiPemanasanPath = $request->file('bukti_pemanasan')->store('cek-unit/pemanasan', 'public');
        }

        $buktiPencucianPath = null;
        if ($request->hasFile('bukti_pencucian')) {
            $buktiPencucianPath = $request->file('bukti_pencucian')->store('cek-unit/pencucian', 'public');
        }

        $buktiBbmPath = null;
        if ($request->hasFile('bukti_bbm')) {
            $buktiBbmPath = $request->file('bukti_bbm')->store('cek-unit/bbm', 'public');
        }

        $dokTangkiPaths = [];
        if ($request->hasFile('dokumentasi_tangki_pompa')) {
            foreach ($request->file('dokumentasi_tangki_pompa') as $file) {
                $dokTangkiPaths[] = $file->store('cek-unit/tangki-pompa', 'public');
            }
        }

        // Process Perlengkapan
        $perlengkapanData = $request->input('perlengkapan', []);
        $processedPerlengkapan = [];
        $jumlahRusak = 0;

        foreach ($perlengkapanLabels as $key => $label) {
            $itemStatus  = $perlengkapanData[$key]['status'] ?? 'baik';
            $itemCatatan = $perlengkapanData[$key]['catatan'] ?? null;

            if ($itemStatus === 'rusak') {
                $jumlahRusak++;
            }

            $processedPerlengkapan[$key] = [
                'label'   => $label,
                'status'  => $itemStatus,
                'catatan' => $itemCatatan,
            ];
        }

        if ($request->filled('catatan_kebersihan_unit')) {
            $processedPerlengkapan['kebersihan_unit'] = [
                'label'   => 'Kondisi Kebersihan Unit',
                'status'  => $validated['kebersihan_unit'] ?? 'bersih',
                'catatan' => $request->input('catatan_kebersihan_unit'),
            ];
        }

        return CekHarianUnit::create([
            'user_id'                  => auth()->id(),
            'kategori'                 => $kategori,
            'unit_id'                  => $validated['unit_id'],
            'unit_nama'                => $unitNama,
            'kebersihan_unit'          => $validated['kebersihan_unit'] ?? 'bersih',
            'nama_pemeriksa'           => $validated['nama_pemeriksa'],
            'jabatan'                  => $validated['jabatan'],
            'pos'                      => $validated['pos'] ?? ($unitObj ? $unitObj->pos : null),
            'nama_danru'               => $validated['nama_danru'] ?? null,
            'nama_kabid'               => $validated['nama_kabid'] ?? null,
            'tanggal_pemeriksaan'      => $validated['tanggal'] ?? date('Y-m-d'),
            'bukti_pemanasan'          => $buktiPemanasanPath,
            'bukti_pencucian'          => $buktiPencucianPath,
            'jenis_bbm'                => $validated['jenis_bbm'] ?? 'solar',
            'bukti_bbm'                => $buktiBbmPath,
            'level_air'                => $validated['level_air'] ?? null,
            'kondisi_tangki_air'       => $validated['kondisi_tangki_air'] ?? null,
            'kebocoran_tangki_air'     => $validated['kebocoran_tangki_air'] ?? null,
            'tekanan_pompa'            => $validated['tekanan_pompa'] ?? null,
            'selang_induk'             => $validated['selang_induk'] ?? null,
            'catatan_tangki_pompa'     => $validated['catatan_tangki_pompa'] ?? null,
            'dokumentasi_tangki_pompa' => $dokTangkiPaths,
            'perlengkapan'             => $processedPerlengkapan,
            'jumlah_rusak'             => $jumlahRusak,
        ]);
    }

    /**
     * Mengambil query riwayat Cek Harian Unit & Cek Harian Alat untuk halaman Riwayat User.
     */
    protected function getRiwayatData(Request $request, string $kategori): array
    {
        $tab         = $request->query('tab', 'unit'); // 'unit' atau 'alat'
        $searchQuery = $request->query('search', '');
        $tanggal     = $request->query('tanggal', '');

        // ===== Hasil Cek Harian Unit Kendaraan =====
        $unitQuery = CekHarianUnit::where('user_id', auth()->id())
            ->where(function ($q) use ($kategori) {
            $q->where('kategori', $kategori);
            if ($kategori === 'pemadam') {
                $q->orWhereNull('kategori');
            }
        });

        if (!empty($searchQuery)) {
            $unitQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_danru', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_kabid', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
            });
        }

        if (!empty($tanggal)) {
            $unitQuery->where(function ($q) use ($tanggal) {
                $q->whereDate('tanggal_pemeriksaan', $tanggal)
                  ->orWhereDate('created_at', $tanggal);
            });
        }

        $cekUnitList = $unitQuery->latest('tanggal_pemeriksaan')
            ->latest('id')
            ->paginate(10, ['*'], 'unit_page')
            ->withQueryString();

        // ===== Hasil Cek Harian Alat =====
        $alatQuery = CekHarianAlat::where('user_id', auth()->id())
            ->where(function ($q) use ($kategori) {
            $q->where('kategori', $kategori);
            if ($kategori === 'pemadam') {
                $q->orWhereNull('kategori');
            }
        });

        if (!empty($searchQuery)) {
            $alatQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_danru', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_kabid', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('unit_nama', 'ILIKE', "%{$searchQuery}%");
            });
        }

        if (!empty($tanggal)) {
            $alatQuery->where(function ($q) use ($tanggal) {
                $q->whereDate('tanggal_pemeriksaan', $tanggal)
                  ->orWhereDate('created_at', $tanggal);
            });
        }

        $cekAlatList = $alatQuery->latest('tanggal_pemeriksaan')
            ->latest('id')
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        // ===== Hasil Riwayat Pengajuan Pemeliharaan =====
        $pengajuanQuery = Pengajuan::where(function ($q) {
            $q->where('user_id', auth()->id());
            if (auth()->user() && auth()->user()->nip) {
                $cleanNip = preg_replace('/\s+/', '', auth()->user()->nip);
                $q->orWhereRaw("REPLACE(nip_pemegang, ' ', '') = ?", [$cleanNip]);
            }
        })->where(function ($q) use ($kategori) {
            $q->where('bidang', 'ILIKE', "%{$kategori}%");
            if ($kategori === 'pemadam') {
                $q->orWhereNull('bidang');
            }
        });

        if (!empty($searchQuery)) {
            $pengajuanQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemegang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_komandan_regu', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_kepala_bidang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nomor_lambung', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('jenis_kendaraan', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('item_perbaikan', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('kode_verifikasi', 'ILIKE', "%{$searchQuery}%");
            });
        }

        if (!empty($tanggal)) {
            $pengajuanQuery->where(function ($q) use ($tanggal) {
                $q->whereDate('created_at', $tanggal)
                  ->orWhereDate('tanggal_keberangkatan', $tanggal);
            });
        }

        $pengajuanList = $pengajuanQuery->with('items')
            ->latest('id')
            ->paginate(10, ['*'], 'pengajuan_page')
            ->withQueryString();

        return compact('cekUnitList', 'cekAlatList', 'pengajuanList', 'tab', 'searchQuery', 'tanggal');
    }
}
