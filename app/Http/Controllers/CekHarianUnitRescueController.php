<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use Illuminate\Http\Request;

class CekHarianUnitRescueController extends Controller
{
    /**
     * Daftar unit/kendaraan rescue (dummy, ganti dengan Model Unit::all() bila sudah tersedia).
     */
    protected function unitList()
    {
        return collect([
            (object) ['id' => 1, 'nama' => 'Rescue 01 - Ford Ranger'],
            (object) ['id' => 2, 'nama' => 'Rescue 02 - Mitsubishi Triton'],
            (object) ['id' => 3, 'nama' => 'Rescue 03 - Isuzu D-Max'],
        ]);
    }

    /**
     * Daftar label perlengkapan kendaraan rescue (harus sinkron dengan view form).
     */
    protected function perlengkapanLabels(): array
    {
        return [
            'ban_cadangan'                     => 'Ban Cadangan',
            'ban_belakang_kanan'                => 'Ban Mobil Belakang Kanan',
            'ban_belakang_kiri'                 => 'Ban Mobil Belakang Kiri',
            'ban_depan_kanan'                   => 'Ban Mobil Depan Kanan',
            'ban_depan_kiri'                     => 'Ban Mobil Depan Kiri',
            'dop_pelek'                          => 'Dop Pelek',
            'electric_winch'                     => 'Electric Winch',
            'handel_kanan_belakang'              => 'Handel Kanan Belakang',
            'handel_kanan_depan'                 => 'Handel Kanan Depan',
            'handel_kiri_belakang'               => 'Handel Kiri Belakang',
            'handel_kiri_depan'                  => 'Handel Kiri Depan',
            'kaca_spion_kanan'                   => 'Kaca Spion Kanan',
            'kaca_spion_kiri'                    => 'Kaca Spion Kiri',
            'lampu_kabut_kanan'                  => 'Lampu Kabut Kanan',
            'lampu_kabut_kiri'                   => 'Lampu Kabut Kiri',
            'lampu_parkir_kanan'                 => 'Lampu Parkir Kanan',
            'lampu_parkir_kiri'                  => 'Lampu Parkir Kiri',
            'lampu_penerangan'                   => 'Lampu Penerangan',
            'lampu_peringatan_belakang_kanan'    => 'Lampu Peringatan Belakang Kanan',
            'lampu_peringatan_belakang_kiri'     => 'Lampu Peringatan Belakang Kiri',
            'lampu_peringatan_depan_kanan'       => 'Lampu Peringatan Depan Kanan',
            'lampu_peringatan_depan_kiri'        => 'Lampu Peringatan Depan Kiri',
            'lampu_rem_kanan'                    => 'Lampu Rem Kanan',
            'lampu_rem_kiri'                     => 'Lampu Rem Kiri',
            'lampu_rotari_atas_belakang'         => 'Lampu Rotari Atas Belakang',
            'lampu_rotari_atas_depan'            => 'Lampu Rotari Atas Depan',
            'lampu_rotator_atas_belakang'        => 'Lampu Rotator Atas Belakang',
            'lampu_rotator_atas_depan'           => 'Lampu Rotator Atas Depan',
            'lampu_sein_belakang_kanan'          => 'Lampu Sein Belakang Kanan',
            'lampu_sein_belakang_kiri'           => 'Lampu Sein Belakang Kiri',
            'lampu_sein_depan_kanan'             => 'Lampu Sein Depan Kanan',
            'lampu_sein_depan_kiri'              => 'Lampu Sein Depan Kiri',
            'lampu_sorot_belakang'               => 'Lampu Sorot Belakang',
            'lampu_sorot_kanan_atas'             => 'Lampu Sorot Kanan Atas',
            'lampu_sorot_kanan_samping'          => 'Lampu Sorot Kanan Samping',
            'lampu_sorot_kiri_atas'              => 'Lampu Sorot Kiri Atas',
            'lampu_sorot_kiri_samping'           => 'Lampu Sorot Kiri Samping',
            'lampu_utama_depan_kanan'            => 'Lampu Utama Depan Kanan',
            'lampu_utama_depan_kiri'             => 'Lampu Utama Depan Kiri',
            'lighting_remote'                    => 'Lighting + Remote',
            'modulator_sirine'                   => 'Modulator Sirine',
            'plat_nomor_belakang'                => 'Plat Nomor Kendaraan Belakang',
            'plat_nomor_depan'                   => 'Plat Nomor Kendaraan Depan',
            'radio_pesawat_rig'                  => 'Radio Pesawat (RIG)',
            'radio_tape'                         => 'Radio Tape',
            'rolling_belakang'                   => 'Rolling Belakang',
            'rolling_kanan'                       => 'Rolling Kanan',
            'rolling_kiri'                        => 'Rolling Kiri',
            'sirine_tunggal'                      => 'Sirine Tunggal',
            'toa_sirine'                          => 'TOA Sirine',
            'wiper_kanan'                         => 'Wiper Kanan',
            'wiper_kiri'                          => 'Wiper Kiri',
        ];
    }

    /**
     * Menampilkan form wizard Cek Harian Unit Kendaraan Rescue.
     */
    public function index()
    {
        $unitList = $this->unitList();

        return view('auth.unit-rescue.cek-harian-unit-rescue', compact('unitList'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan rescue.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Step 1 - Identitas
            'nama_pemeriksa'  => 'required|string|max:255',
            'jabatan'         => 'required|string|max:255',
            'unit_id'         => 'required|integer',

            // Step 2 - Pemanasan & BBM
            'bukti_pemanasan' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'jenis_bbm'       => 'required|in:solar,bensin',
            'bukti_bbm'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',

            // Step 3 - Perlengkapan
            'perlengkapan'           => 'required|array',
            'perlengkapan.*.status'  => 'required|in:baik,rusak',
            'perlengkapan.*.catatan' => 'nullable|string',
        ], [
            'bukti_pemanasan.uploaded' => 'File Bukti Pemanasan gagal diunggah. Ukuran foto terlalu besar atau melebihi batas upload PHP server (Maks 10MB).',
            'bukti_pemanasan.max'      => 'Ukuran foto Bukti Pemanasan tidak boleh lebih dari 10 MB.',
            'bukti_bbm.uploaded'        => 'File Bukti Level BBM gagal diunggah. Ukuran foto terlalu besar atau melebihi batas upload PHP server (Maks 10MB).',
            'bukti_bbm.max'            => 'Ukuran foto Bukti Level BBM tidak boleh lebih dari 10 MB.',
        ]);

        // Upload bukti pemanasan
        $buktiPemanasanPath = $request->hasFile('bukti_pemanasan')
            ? $request->file('bukti_pemanasan')->store('cek-harian-unit-rescue', 'public')
            : null;

        // Upload bukti BBM
        $buktiBbmPath = $request->hasFile('bukti_bbm')
            ? $request->file('bukti_bbm')->store('cek-harian-unit-rescue', 'public')
            : null;

        // Susun perlengkapan lengkap dengan label, agar mudah ditampilkan di admin
        $labels = $this->perlengkapanLabels();
        $perlengkapan = [];
        $jumlahRusak = 0;
        foreach ($validated['perlengkapan'] as $key => $item) {
            $status = $item['status'] ?? 'baik';
            if ($status === 'rusak') {
                $jumlahRusak++;
            }
            $perlengkapan[$key] = [
                'label'   => $labels[$key] ?? $key,
                'status'  => $status,
                'catatan' => $item['catatan'] ?? null,
            ];
        }

        // Ambil nama unit terpilih untuk disimpan sebagai snapshot
        $unit = $this->unitList()->firstWhere('id', (int) $validated['unit_id']);

        CekHarianUnit::create([
            'user_id'        => auth()->id(),
            'kategori'       => 'rescue',
            'nama_pemeriksa' => $validated['nama_pemeriksa'],
            'jabatan'        => $validated['jabatan'],
            'unit_id'        => $validated['unit_id'],
            'unit_nama'      => $unit->nama ?? null,

            'bukti_pemanasan' => $buktiPemanasanPath,
            'jenis_bbm'       => $validated['jenis_bbm'],
            'bukti_bbm'       => $buktiBbmPath,

            'level_air'            => null,
            'kondisi_tangki_air'   => null,
            'kebocoran_tangki_air' => null,
            'tekanan_pompa'        => null,
            'selang_induk'         => null,
            'catatan_tangki_pompa' => null,
            'dokumentasi_tangki_pompa' => null,

            'perlengkapan' => $perlengkapan,
            'jumlah_rusak' => $jumlahRusak,
        ]);

        return redirect()
            ->route('unit-rescue.cek-harian-unit-rescue')
            ->with('success', 'Pemeriksaan unit kendaraan rescue berhasil disimpan.');
    }
}
