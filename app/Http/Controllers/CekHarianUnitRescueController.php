<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

use App\Traits\HandlesCekHarianUnit;

class CekHarianUnitRescueController extends Controller
{
    use HandlesCekHarianUnit;

    /**
     * Daftar unit/kendaraan rescue dari database Admin Data Unit sesuai Pos pengguna.
     */
    protected function unitList()
    {
        $currentUser = auth()->user();
        $allUnits = Unit::where('kategori', 'LIKE', 'rescue')
            ->orWhere('peruntukan', 'LIKE', 'rescue')
            ->orWhere('nomor_lambung', 'LIKE', 'R-%')
            ->orderBy('nomor_lambung', 'asc')
            ->get();

        if ($currentUser && $currentUser->pos) {
            $userPosClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser->pos));
            $posUnits = $allUnits->filter(function ($u) use ($userPosClean) {
                $posClean = strtolower(preg_replace('/[^a-z0-9]/', '', $u->pos ?? ''));
                return $posClean && (str_contains($posClean, $userPosClean) || str_contains($userPosClean, $posClean));
            })->values();

            if ($posUnits->isNotEmpty()) {
                return $posUnits;
            }
        }

        return $allUnits;
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
        $allUnits = Unit::where('kategori', 'LIKE', 'rescue')
            ->orWhere('peruntukan', 'LIKE', 'rescue')
            ->orWhere('nomor_lambung', 'LIKE', 'R-%')
            ->orderBy('nomor_lambung', 'asc')
            ->get();
        $unitList = $this->unitList();
        $posList  = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        return view('auth.unit-rescue.cek-harian-unit-rescue', compact('unitList', 'allUnits', 'posList'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan rescue.
     */
    public function store(Request $request)
    {
        $record = $this->storeCekHarianUnit($request, 'rescue', $this->perlengkapanLabels());

        return redirect()
            ->route('unit-rescue.cek-harian-unit-rescue')
            ->with('success', "Pemeriksaan harian unit Rescue '{$record->unit_nama}' berhasil disimpan!")
            ->with('cek_id', $record->id);
    }

    /**
     * Mengunduh PDF hasil pemeriksaan unit rescue.
     */
    public function exportPdf($id)
    {
        return $this->exportCekHarianUnitPdf((int) $id, 'rescue');
    }
}
