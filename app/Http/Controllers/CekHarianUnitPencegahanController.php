<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;
use App\Traits\HandlesCekHarianUnit;

class CekHarianUnitPencegahanController extends Controller
{
    use HandlesCekHarianUnit;

    /**
     * Daftar unit/kendaraan pencegahan dari database Admin Data Unit sesuai Pos pengguna.
     */
    protected function unitList()
    {
        $currentUser = auth()->user();
        $allUnits = Unit::where('kategori', 'LIKE', 'pencegahan')
            ->orWhere('peruntukan', 'LIKE', 'pencegahan')
            ->orWhere('nomor_lambung', 'LIKE', 'PC-%')
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
     * Daftar label perlengkapan kendaraan pencegahan.
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
            'lampu_sorot'                        => 'Lampu Sorot',
            'lampu_utama_dekat_kanan'            => 'Lampu Utama Dekat Kanan',
            'lampu_utama_dekat_kiri'             => 'Lampu Utama Dekat Kiri',
            'lampu_utama_jauh_kanan'             => 'Lampu Utama Jauh Kanan',
            'lampu_utama_jauh_kiri'              => 'Lampu Utama Jauh Kiri',
            'pengeras_suara'                     => 'Pengeras Suara',
            'sirine'                             => 'Sirine',
            'tabung_pemadam_apar'                => 'Tabung Pemadam (APAR)',
            'tangga'                             => 'Tangga',
            'wiper'                              => 'Wiper',
        ];
    }

    /**
     * Menampilkan form Cek Harian Unit Kendaraan Pencegahan.
     */
    public function index()
    {
        $allUnits = Unit::where('kategori', 'LIKE', 'pencegahan')
            ->orWhere('peruntukan', 'LIKE', 'pencegahan')
            ->orWhere('nomor_lambung', 'LIKE', 'PC-%')
            ->orderBy('nomor_lambung', 'asc')
            ->get();
        $unitList           = $this->unitList();
        $posList            = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();
        $perlengkapanLabels = $this->perlengkapanLabels();

        return view('auth.unit-pencegahan.cek-harian-unit', compact('unitList', 'allUnits', 'posList', 'perlengkapanLabels'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan pencegahan.
     */
    public function store(Request $request)
    {
        $cek = $this->storeCekHarianUnit($request, 'pencegahan', $this->perlengkapanLabels());

        return redirect()
            ->route('unit-pencegahan.cek-harian-unit')
            ->with('success', "Pemeriksaan unit '{$cek->unit_nama}' berhasil disimpan!")
            ->with('cek_id', $cek->id);
    }

    /**
     * Ekspor PDF Cek Harian Unit Pencegahan.
     */
    public function exportPdf(int $id)
    {
        return $this->exportCekHarianUnitPdf($id, 'pencegahan');
    }
}