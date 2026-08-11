<?php

namespace App\Traits;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use Illuminate\Http\Request;

trait HandlesCekHarianUnit
{
    /**
     * Helper terpusat untuk memproses & menyimpan Cek Harian Unit (Pemadam / Rescue).
     */
    protected function storeCekHarianUnit(Request $request, string $kategori, array $perlengkapanLabels): CekHarianUnit
    {
        $validated = $request->validate([
            'nama_pemeriksa'             => 'required|string|max:255',
            'jabatan'                    => 'required|string|max:255',
            'unit_id'                    => 'required|integer',
            'pos'                        => 'required|string|max:255',
            'tanggal'                    => 'nullable|date',
            'jenis_bbm'                  => 'nullable|string|max:50',
            'bukti_pemanasan'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
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
        ]);

        $unitObj  = Unit::find($validated['unit_id']);
        $unitNama = $unitObj ? "{$unitObj->nomor_lambung} ({$unitObj->plat_nomor})" : ("Unit #" . $validated['unit_id']);

        // Handle File Uploads
        $buktiPemanasanPath = null;
        if ($request->hasFile('bukti_pemanasan')) {
            $buktiPemanasanPath = $request->file('bukti_pemanasan')->store('cek-unit/pemanasan', 'public');
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
