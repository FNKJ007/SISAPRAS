<?php

namespace App\Traits;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

trait HandlesCekHarianUnit
{
    /**
     * Generate & unduh PDF hasil Cek Harian Unit (Pemadam / Rescue).
     */
    protected function exportCekHarianUnitPdf(int $id, string $kategori)
    {
        try {
            $record = CekHarianUnit::where('kategori', $kategori)->findOrFail($id);

            // increase limits for PDF generation
            if (function_exists('set_time_limit')) set_time_limit(120);
            @ini_set('memory_limit', '512M');

            // Enable remote if needed and prepare embedded images as data-URIs
            Pdf::setOptions(["isRemoteEnabled" => true, "isHtml5ParserEnabled" => true]);

        $toDataUri = function (?string $path) {
            if (!$path) {
                return null;
            }

            $full = storage_path('app/public/' . $path);
            if (!file_exists($full)) {
                return null;
            }

            $type = mime_content_type($full) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($full));
            return 'data:' . $type . ';base64,' . $data;
        };

        $buktiPemanasanData = $toDataUri($record->bukti_pemanasan);
        $buktiBbmData      = $toDataUri($record->bukti_bbm);
        $buktiPencucianData = $toDataUri($record->bukti_pencucian ?? null);

        $dokTangkiData = [];
        foreach ($record->dokumentasi_tangki_pompa ?? [] as $p) {
            $d = $toDataUri($p);
            if ($d) {
                $dokTangkiData[] = $d;
            }
        }

            $pdf = Pdf::loadView('pdf.cek-harian-unit', [
            'record'               => $record,
            'judul'                => $kategori === 'rescue'
                ? 'Hasil Cek Harian Unit Kendaraan Rescue'
                : 'Hasil Cek Harian Unit Kendaraan Pemadam',
            'bukti_pemanasan_data' => $buktiPemanasanData,
            'bukti_bbm_data'       => $buktiBbmData,
            'bukti_pencucian_data' => $buktiPencucianData,
            'dok_tangki_data'      => $dokTangkiData,
            ])->setPaper('a4', 'portrait');

            $namaFile = 'cek-harian-unit-' . $kategori . '-' . str_replace([' ', '/'], '-', $record->unit_nama) . '-' . $record->tanggal_pemeriksaan . '.pdf';

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
            'bukti_pemanasan.image'            => 'Foto bukti pemanasan harus berupa file gambar (JPG/PNG/WebP).',
            'bukti_pemanasan.max'              => 'Ukuran foto bukti pemanasan tidak boleh melebihi 10 MB.',
            'bukti_pencucian.image'            => 'Foto bukti pencucian harus berupa file gambar (JPG/PNG/WebP).',
            'bukti_pencucian.max'              => 'Ukuran foto bukti pencucian tidak boleh melebihi 10 MB.',
            'bukti_bbm.image'                  => 'Foto bukti BBM harus berupa file gambar (JPG/PNG/WebP).',
            'bukti_bbm.max'                    => 'Ukuran foto bukti BBM tidak boleh melebihi 10 MB.',
            'dokumentasi_tangki_pompa.*.image' => 'Foto dokumentasi tangki/pompa harus berupa file gambar.',
            'dokumentasi_tangki_pompa.*.max'   => 'Ukuran foto dokumentasi tangki/pompa maksimal 10 MB per file.',
        ];

        $validated = $request->validate([
            'nama_pemeriksa'             => 'required|string|max:255',
            'jabatan'                    => 'required|string|max:255',
            'unit_id'                    => 'required|integer',
            'pos'                        => 'required|string|max:255',
            'tanggal'                    => 'nullable|date',
            'jenis_bbm'                  => 'nullable|string|max:50',
            'bukti_pemanasan'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'bukti_pencucian'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'bukti_bbm'                  => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'level_air'                  => 'nullable|string|max:100',
            'kondisi_tangki_air'         => 'nullable|string|max:100',
            'kebocoran_tangki_air'       => 'nullable|string|max:100',
            'tekanan_pompa'              => 'nullable|string|max:100',
            'selang_induk'               => 'nullable|string|max:100',
            'catatan_tangki_pompa'       => 'nullable|string',
            'dokumentasi_tangki_pompa'   => 'nullable|array',
            'dokumentasi_tangki_pompa.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'perlengkapan'               => 'nullable|array',
            'catatan'                    => 'nullable|string',
        ], $messages);

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

        return CekHarianUnit::create([
            'user_id'                  => auth()->id(),
            'kategori'                 => $kategori,
            'unit_id'                  => $validated['unit_id'],
            'unit_nama'                => $unitNama,
            'nama_pemeriksa'           => $validated['nama_pemeriksa'],
            'jabatan'                  => $validated['jabatan'],
            'pos'                      => $validated['pos'] ?? ($unitObj ? $unitObj->pos : null),
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
}
