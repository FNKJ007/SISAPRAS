<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

use App\Traits\HandlesCekHarianUnit;
use App\Traits\HandlesOfficialsData;

class CekHarianUnitRescueController extends Controller
{
    use HandlesCekHarianUnit, HandlesOfficialsData;

    /**
     * Daftar unit/kendaraan rescue dari database Admin Data Unit sesuai Pos pengguna.
     */
    protected function unitList()
    {
        $currentUser = auth()->user();
        $allUnits = Unit::where('kategori', 'ILIKE', 'rescue')
            ->orWhere('peruntukan', 'ILIKE', 'rescue')
            ->orWhere('nomor_lambung', 'ILIKE', 'R-%')
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
        ];
    }

    /**
     * Menampilkan form wizard Cek Harian Unit Kendaraan Rescue.
     */
    public function index()
    {
        $allUnits = Unit::where('kategori', 'ILIKE', 'rescue')
            ->orWhere('peruntukan', 'ILIKE', 'rescue')
            ->orWhere('nomor_lambung', 'ILIKE', 'R-%')
            ->orderBy('nomor_lambung', 'asc')
            ->get();
        $unitList = $this->unitList();
        $posList  = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();
        $officials = $this->getOfficialsData('rescue');

        return view('auth.unit-rescue.cek-harian-unit-rescue', array_merge(
            compact('unitList', 'allUnits', 'posList'),
            $officials
        ));
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

    /**
     * Menampilkan halaman riwayat pengecekan unit & alat rescue untuk user.
     */
    public function riwayat(Request $request)
    {
        $data = $this->getRiwayatData($request, 'rescue');
        return view('auth.unit-rescue.riwayat', $data);
    }
}
