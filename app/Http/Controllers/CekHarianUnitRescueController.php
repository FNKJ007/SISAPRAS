<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

class CekHarianUnitRescueController extends Controller
{
    /**
     * Daftar unit/kendaraan rescue dari database Admin Data Unit.
     */
    protected function unitList()
    {
        $units = Unit::where('kategori', 'rescue')->orderBy('nomor_lambung', 'asc')->get();

        if ($units->isEmpty()) {
            return collect([
                (object) ['id' => 1, 'nama' => 'R-01 - HINO', 'nomor_lambung' => 'R-01', 'plat_nomor' => 'D 9933 V', 'pos' => 'SOREANG'],
            ]);
        }

        return $units;
    }

    /**
     * Daftar label perlengkapan kendaraan rescue.
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
        $posList  = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        return view('auth.unit-rescue.cek-harian-unit-rescue', compact('unitList', 'posList'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan rescue.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemeriksa'  => 'required|string|max:255',
            'jabatan'         => 'required|string|max:255',
            'unit_id'         => 'required|integer',
            'pos'             => 'nullable|string|max:255',
            'tanggal'         => 'required|date',
            'kondisi'         => 'required|array',
            'keterangan'      => 'nullable|array',
            'catatan'         => 'nullable|string',
        ]);

        $unitObj  = Unit::find($validated['unit_id']);
        $unitNama = $unitObj ? "{$unitObj->nomor_lambung} ({$unitObj->plat_nomor})" : ("Unit #" . $validated['unit_id']);

        $perlengkapan = [];
        $totalBaik = 0;
        $totalPerbaikan = 0;
        $totalRusak = 0;

        foreach ($this->perlengkapanLabels() as $key => $label) {
            $st  = $validated['kondisi'][$key] ?? 'baik';
            $ket = $validated['keterangan'][$key] ?? null;

            if ($st === 'baik') {
                $totalBaik++;
            } elseif ($st === 'perbaikan') {
                $totalPerbaikan++;
            } else {
                $totalRusak++;
            }

            $perlengkapan[$key] = [
                'label'      => $label,
                'status'     => $st,
                'keterangan' => $ket,
            ];
        }

        CekHarianUnit::create([
            'user_id'            => auth()->id() ?? 1,
            'unit_id'            => $validated['unit_id'],
            'unit_nama'          => $unitNama,
            'kategori_unit'      => 'rescue',
            'nama_pemeriksa'     => $validated['nama_pemeriksa'],
            'jabatan'            => $validated['jabatan'],
            'pos'                => $validated['pos'] ?? ($unitObj ? $unitObj->pos : null),
            'tanggal_pemeriksaan' => $validated['tanggal'],
            'perlengkapan'       => $perlengkapan,
            'total_baik'         => $totalBaik,
            'total_perbaikan'    => $totalPerbaikan,
            'total_rusak'        => $totalRusak,
            'catatan'            => $validated['catatan'] ?? null,
        ]);

        return redirect()
            ->route('unit-rescue.cek-harian-unit-rescue')
            ->with('success', "Pemeriksaan harian unit Rescue '{$unitNama}' berhasil disimpan!");
    }
}
