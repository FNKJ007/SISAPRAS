<?php

namespace App\Traits;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\OptimizesPdfImages;

trait HandlesCekHarianAlat
{
    use OptimizesPdfImages;

    /**
     * Generate & unduh PDF hasil Cek Harian Alat (Pemadam / Rescue).
     */
    protected function exportCekHarianAlatPdf(int $id, string $kategori)
    {
        try {
            $record = CekHarianAlat::where('kategori', $kategori)->findOrFail($id);

            if (function_exists('set_time_limit')) {
                @set_time_limit(300);
            }
            @ini_set('max_execution_time', 300);
            @ini_set('memory_limit', '512M');

            Pdf::setOptions(["isRemoteEnabled" => false, "isHtml5ParserEnabled" => true]);

            $pemeriksaNip = $record->user->nip ?? \App\Models\User::where('name', $record->nama_pemeriksa)->value('nip') ?? '';

            $danruNip = '';
            if (!empty($record->nama_danru)) {
                $danruNip = \App\Models\User::where('name', $record->nama_danru)->value('nip')
                    ?? \App\Models\User::where('name', 'LIKE', '%' . $record->nama_danru . '%')->value('nip') ?? '';
            }

            $kabidNip = '';
            $kabidLabel = match($kategori) {
                'rescue'         => 'Kepala Bidang Penyelamatan',
                'pencegahan'     => 'Kepala Bidang Pencegahan Kebakaran',
                'command_center' => 'Kepala Bidang Sarana, Prasarana Dan Informasi',
                default          => 'Kepala Bidang Pemadaman',
            };
            if (!empty($record->nama_kabid)) {
                $kabidUser = \App\Models\User::where('name', $record->nama_kabid)->first()
                    ?? \App\Models\User::where('name', 'LIKE', '%' . $record->nama_kabid . '%')->first();
                if ($kabidUser) {
                    $kabidNip = $kabidUser->nip ?? '';
                    if (!empty($kabidUser->jabatan)) {
                        $kabidLabel = $kabidUser->jabatan;
                    }
                }
            }

            $pdf = Pdf::loadView('pdf.cek-harian-alat', [
                'record'        => $record,
                'pemeriksa_nip' => $pemeriksaNip,
                'danru_nip'     => $danruNip,
                'kabid_nip'     => $kabidNip,
                'kabid_label'   => $kabidLabel,
                'judul'         => match($kategori) {
                    'rescue'         => 'Hasil Cek Harian Alat Rescue',
                    'pencegahan'     => 'Hasil Cek Harian Alat Pencegahan',
                    'command_center' => 'Hasil Cek Peralatan Command Center',
                    default          => 'Hasil Cek Harian Alat Pemadam',
                },
                'foto_umum_data' => $fotoUmumData,
            ])->setPaper('a4', 'portrait');

            $namaFile = 'cek-harian-alat-' . $kategori . '-' . $record->tanggal_pemeriksaan->format('Y-m-d') . '-' . $record->id . '.pdf';

            return $pdf->download($namaFile);
        } catch (\Throwable $e) {
            \Log::error('Export CekHarianAlat PDF failed: ' . $e->getMessage(), ['id' => $id, 'kategori' => $kategori]);
            return redirect()->back()->with('error', 'Gagal membuat PDF: ' . $e->getMessage());
        }
    }
    /**
     * Helper terpusat untuk memproses & menyimpan Cek Harian Alat (Pemadam / Rescue / Command Center).
     */
    protected function storeCekHarianAlat(Request $request, string $kategori, ?string $customUnitNama = null): CekHarianAlat
    {
        $messages = [
            'nama_pemeriksa.required'      => 'Nama petugas pemeriksa wajib diisi.',
            'jabatan.required'             => 'Jabatan petugas pemeriksa wajib diisi.',
            'tanggal_pemeriksaan.required' => 'Tanggal pemeriksaan wajib diisi.',
            'alat.required'                => 'Daftar peralatan yang diperiksa wajib diisi.',
            'foto_umum.required'           => 'Foto dokumentasi pemeriksaan alat wajib dilampirkan.',
            'foto_umum.uploaded'           => 'File foto dokumentasi gagal diunggah. Ukuran foto terlalu besar (Maks 10MB).',
            'foto_umum.image'              => 'File foto dokumentasi harus berupa gambar (JPG/PNG/WebP).',
            'foto_umum.max'                => 'Ukuran foto dokumentasi tidak boleh lebih dari 10 MB.',
        ];

        $validated = $request->validate([
            'nama_pemeriksa'      => 'required|string|max:255',
            'jabatan'             => 'required|string|max:255',
            'pos'                 => 'nullable|string|max:255',
            'nama_danru'          => 'nullable|string|max:255',
            'nama_kabid'          => 'nullable|string|max:255',
            'unit_id'             => 'nullable|integer',
            'tanggal_pemeriksaan' => 'required|date',

            'alat'                => 'required|array|min:1',
            'alat.*.id'           => 'required|integer',
            'alat.*.jumlah_baik'  => 'nullable|integer|min:0',
            'alat.*.jumlah_rusak' => 'nullable|integer|min:0',
            'alat.*.nomor_rusak'  => 'nullable|string|max:500',

            'catatan_umum'        => 'nullable|string',
            'foto_umum'           => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ], $messages);

        $unitId = $validated['unit_id'] ?? null;
        $unitNama = null;
        $unitObj  = null;

        if ($customUnitNama) {
            $unitNama = $customUnitNama;
        } elseif ($unitId) {
            $unitObj  = Unit::find($unitId);
            $unitNama = $unitObj ? "{$unitObj->nomor_lambung} ({$unitObj->plat_nomor})" : ("Unit #" . $unitId);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_umum')) {
            $fotoPath = $request->file('foto_umum')->store('cek-harian-alat', 'public');
        }

        $processedAlat = [];
        $totalBaik = 0;
        $totalRusak = 0;

        foreach ($validated['alat'] as $itemData) {
            $alatObj  = Peralatan::find($itemData['id']);
            $namaAlat = $alatObj ? $alatObj->nama : ("Alat #" . $itemData['id']);

            $baik  = (int) ($itemData['jumlah_baik'] ?? 0);
            $rusak = (int) ($itemData['jumlah_rusak'] ?? 0);

            $totalBaik += $baik;
            $totalRusak += $rusak;

            $processedAlat[] = [
                'id'           => $itemData['id'],
                'nama'         => $namaAlat,
                'jumlah_baik'  => $baik,
                'jumlah_rusak' => $rusak,
                'nomor_rusak'  => $itemData['nomor_rusak'] ?? null,
            ];
        }

        return CekHarianAlat::create([
            'user_id'             => auth()->id(),
            'kategori'            => $kategori,
            'unit_id'             => $unitId,
            'unit_nama'           => $unitNama,
            'nama_pemeriksa'      => $validated['nama_pemeriksa'],
            'jabatan'             => $validated['jabatan'],
            'pos'                 => $validated['pos'] ?? ($unitObj ? $unitObj->pos : null),
            'nama_danru'          => $validated['nama_danru'] ?? null,
            'nama_kabid'          => $validated['nama_kabid'] ?? null,
            'tanggal_pemeriksaan' => $validated['tanggal_pemeriksaan'],
            'alat'                => $processedAlat,
            'total_baik'          => $totalBaik,
            'total_rusak'         => $totalRusak,
            'catatan_umum'        => $validated['catatan_umum'] ?? null,
            'foto_umum'           => $fotoPath,
        ]);
    }
}
