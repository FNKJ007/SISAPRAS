<?php

namespace App\Http\Controllers;

use App\Models\CekHarianUnit;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

class CekHarianUnitPemadamController extends Controller
{
    /**
     * Daftar unit/kendaraan pemadam dari database Admin Data Unit.
     */
    protected function unitList()
    {
        $units = Unit::where('kategori', 'pemadam')->orderBy('nomor_lambung', 'asc')->get();

        if ($units->isEmpty()) {
            return collect([
                (object) ['id' => 1, 'nama' => 'P-01 - HINO (4X4)', 'nomor_lambung' => 'P-01', 'plat_nomor' => 'D 8518 V', 'pos' => 'SOREANG'],
            ]);
        }

        return $units;
    }

    /**
     * Daftar label perlengkapan kendaraan.
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
        $posList = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        return view('auth.unit-pemadam.cek-harian-unit', compact('unitList', 'posList'));
    }

    /**
     * Menyimpan hasil pemeriksaan unit kendaraan pemadam.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemeriksa'   => 'required|string|max:255',
            'jabatan'          => 'required|string|max:255',
            'unit_id'          => 'required|integer',
            'pos'              => 'nullable|string|max:255',
            'tanggal'          => 'required|date',
            'kondisi'          => 'required|array',
            'keterangan'       => 'nullable|array',
            'catatan'          => 'nullable|string',
        ]);

        $unitObj = Unit::find($validated['unit_id']);
        $unitNama = $unitObj ? "{$unitObj->nomor_lambung} ({$unitObj->plat_nomor})" : ("Unit #" . $validated['unit_id']);

        $perlengkapan = [];
        $totalBaik = 0;
        $totalPerbaikan = 0;
        $totalRusak = 0;

        foreach ($this->perlengkapanLabels() as $key => $label) {
            $st = $validated['kondisi'][$key] ?? 'baik';
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
            'kategori_unit'      => 'pemadam',
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
            ->route('unit-pemadam.cek-harian-unit')
            ->with('success', "Pemeriksaan harian unit Pemadam '{$unitNama}' berhasil disimpan!");
    }
}
