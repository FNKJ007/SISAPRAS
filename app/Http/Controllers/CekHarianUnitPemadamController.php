<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use Illuminate\Http\Request;

class CekHarianUnitPemadamController extends Controller
{
    /**
     * Daftar unit/kendaraan pemadam (dummy, ganti dengan Model Unit::all() bila sudah tersedia).
     */
    protected function unitList()
    {
        return collect([
            (object) ['id' => 1, 'nama' => 'Damkar 01 - Toyota Dyna'],
            (object) ['id' => 2, 'nama' => 'Damkar 02 - Hino Ranger'],
            (object) ['id' => 3, 'nama' => 'Damkar 03 - Isuzu Elf'],
        ]);
    }

    /**
     * Daftar label perlengkapan kendaraan (harus sinkron dengan view form).
     */
    protected function perlengkapanLabels(): array
    {
        return [
            'engine_starter'             => 'Engine Starter',
            'rem_tangan'                 => 'Rem Tangan',
            'rem_kaki'                   => 'Rem Kaki',
            'kelistrikan'                => 'Kelistrikan',
            'klakson'                    => 'Klakson',
            'sirine_tunggal'             => 'Sirine Tunggal',
            'sirine'                     => 'Sirine',
            'speedometer'                => 'Speedometer',
            'dashboard_camera'           => 'Dashboard Camera',
            'gps_tracker'                => 'GPS Tracker',
            'flasher_sein_kanan_kiri'    => 'Flasher Sein Kanan-Kiri',
            'spion_dalam'                => 'Spion Dalam',
            'rig'                        => 'RIG',
            'speaker'                    => 'Speaker',
            'megaphone_toa'              => 'Megaphone (TOA)',
            'oli_power_steering'         => 'Oli Power Steering',
            'air_radiator'               => 'Air Radiator',
            'minyak_rem'                 => 'Minyak Rem',
            'oli_mesin'                  => 'Oli Mesin',
            'air_wiper'                  => 'Air Wiper',
            'ac'                         => 'AC',
            'kebersihan_bagian_dalam'    => 'Kebersihan Bagian Dalam',
            'lampu_depan_dim_kanan'      => 'Lampu Depan (Dim) Kanan',
            'lampu_depan_dim_kiri'       => 'Lampu Depan (Dim) Kiri',
            'lampu_belakang_kanan'       => 'Lampu Belakang Kanan',
            'lampu_belakang_kiri'        => 'Lampu Belakang Kiri',
            'lampu_belakang_hazard'      => 'Lampu Belakang Hazard',
            'lampu_sein_depan_kanan'     => 'Lampu Sein Depan Kanan',
            'lampu_sein_depan_kiri'      => 'Lampu Sein Depan Kiri',
            'lampu_sein_belakang_kanan'  => 'Lampu Sein Belakang Kanan',
            'lampu_sein_belakang_kiri'   => 'Lampu Sein Belakang Kiri',
            'spion_kanan'                => 'Spion Kanan',
            'spion_kiri'                 => 'Spion Kiri',
            'wiper'                      => 'Wiper',
            'winch'                      => 'Winch',
            'ban_depan_kanan'            => 'Ban Depan Kanan',
            'ban_depan_kiri'             => 'Ban Depan Kiri',
            'ban_belakang_kanan'         => 'Ban Belakang Kanan',
            'ban_belakang_kiri'          => 'Ban Belakang Kiri',
            'ban_cadangan'               => 'Ban Cadangan',
            'lampu_rotary'               => 'Lampu Rotary',
            'lampu_rem_kanan'            => 'Lampu Rem Kanan',
            'lampu_rem_kiri'             => 'Lampu Rem Kiri',
            'pintu_kompartemen_kanan'    => 'Pintu Kompartemen Kanan',
            'pintu_kompartemen_kiri'     => 'Pintu Kompartemen Kiri',
            'pintu_kompartemen_belakang' => 'Pintu Kompartemen Belakang',
            'ganjal_ban'                 => 'Ganjal Ban',
            'dongkrak'                   => 'Dongkrak',
            'kabin'                      => 'Kabin',
            'body_unit'                  => 'Body Unit',
            'kunci_kunci'                => 'Kunci-Kunci',
            'kebersihan_bagian_luar'     => 'Kebersihan Bagian Luar',
        ];
    }

    /**
     * Menampilkan form wizard Cek Harian Unit Kendaraan Pemadam.
     */
    public function index()
    {
        $unitList = $this->unitList();

        return view('auth.unit-pemadam.cek-harian-unit', compact('unitList'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan pemadam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Step 1 - Identitas
            'nama_pemeriksa'   => 'required|string|max:255',
            'jabatan'          => 'required|string|max:255',
            'unit_id'          => 'required|integer',

            // Step 2 - Pemanasan & BBM
            'bukti_pemanasan'  => 'nullable|image|max:2048',
            'jenis_bbm'        => 'required|in:solar,bensin',
            'bukti_bbm'        => 'nullable|image|max:2048',

            // Step 3 - Tangki & Pompa
            'level_air'               => 'required|in:penuh,3_4,1_2,kosong',
            'kondisi_tangki_air'          => 'required|in:baik,perlu_perhatian,rusak',
            'kebocoran_tangki_air'        => 'required|in:ada,tidak_ada',
            'tekanan_pompa'           => 'required|in:baik,kurang,tidak_ada',
            'selang_induk'            => 'required|in:baik,rusak',
            'catatan_tangki_pompa'    => 'nullable|string',
            'dokumentasi_tangki_pompa'   => 'nullable|array|max:3',
            'dokumentasi_tangki_pompa.*' => 'image|max:2048',

            // Step 4 - Perlengkapan
            'perlengkapan'                    => 'required|array',
            'perlengkapan.*.status'           => 'required|in:baik,rusak',
            'perlengkapan.*.catatan'          => 'nullable|string',
        ]);

        // Upload bukti pemanasan
        $buktiPemanasanPath = $request->hasFile('bukti_pemanasan')
            ? $request->file('bukti_pemanasan')->store('cek-harian-unit', 'public')
            : null;

        // Upload bukti BBM
        $buktiBbmPath = $request->hasFile('bukti_bbm')
            ? $request->file('bukti_bbm')->store('cek-harian-unit', 'public')
            : null;

        // Upload dokumentasi tangki & pompa (maks 3 foto)
        $dokumentasiTangkiPompaPaths = [];
        foreach ($request->file('dokumentasi_tangki_pompa', []) as $foto) {
            $dokumentasiTangkiPompaPaths[] = $foto->store('cek-harian-unit', 'public');
        }

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
            'nama_pemeriksa' => $validated['nama_pemeriksa'],
            'jabatan'        => $validated['jabatan'],
            'unit_id'        => $validated['unit_id'],
            'unit_nama'      => $unit->nama ?? null,
            'shift'          => $validated['shift'],

            'bukti_pemanasan'  => $buktiPemanasanPath,
            'jenis_bbm'        => $validated['jenis_bbm'],
            'level_bbm'        => $validated['level_bbm'],
            'jumlah_bbm_liter' => $validated['jumlah_bbm_liter'] ?? null,
            'bukti_bbm'        => $buktiBbmPath,

            'level_air'                => $validated['level_air'],
            'kondisi_tangki_air'           => $validated['kondisi_tangki_air'],
            'kebocoran_tangki_air'         => $validated['kebocoran_tangki_air'],
            'tekanan_pompa'            => $validated['tekanan_pompa'],
            'selang_induk'             => $validated['selang_induk'],
            'catatan_tangki_pompa'     => $validated['catatan_tangki_pompa'] ?? null,
            'dokumentasi_tangki_pompa' => $dokumentasiTangkiPompaPaths,

            'perlengkapan' => $perlengkapan,
            'jumlah_rusak' => $jumlahRusak,
        ]);

        return redirect()
            ->route('unit-pemadam.cek-harian-unit')
            ->with('success', 'Pemeriksaan unit kendaraan pemadam berhasil disimpan.');
    }
}
