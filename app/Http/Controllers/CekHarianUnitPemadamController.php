<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CekHarianUnitPemadamController extends Controller
{
    /**
     * Menampilkan form Cek Harian Unit Kendaraan Operasional.
     */
    public function index()
    {
        // NOTE: data dropdown di bawah masih statis (contoh). Ganti dengan
        // data dari database begitu tabel master-nya sudah ada, misalnya:
        //   $bidangList = Bidang::pluck('nama_bidang', 'id');
        

        $unitList = [
            'k_01' => 'K-01 / D 8507 V (KOMANDO KADIS)',
            'mp_01' => 'MP-01 / D 3296 Z',
            'mp_02' => 'MP-02 / D 3297 Z',
            'mp_03' => 'MP-03 / D 5560 V',
            'mp_04' => 'MP-04 / D 5561 V',
            'p_01' => 'P-01 / D 8518 V',
            'p_02' => 'P-02 / D 9923 Z',
            'p_03' => 'P-03 / NKR81-7000441',
            'p_04' => 'P-04 / D 9429 V',
            'p_05' => 'P-05 / D 9921 V',
            'p_06' => 'P-06 / D 9932 V',
            'p_07' => 'P-07 / D 9914 V',
            'p_08' => 'P-08 / D 9060 V',
            'p_09' => 'P-09 / D 9920 Z',
            'p_10' => 'P-10 / D 8559 V',
            'p_11' => 'P-11 / D 9958 Y',
            'p_12' => 'P-12 / D 9957 Y',
            'pc_01' => 'PC-01 / D 8508 V (INSPEKSI)',
            'pc_02' => 'PC-02 / UNIT JEPANG PENCEGAHAN',
            'r_01' => 'R-01 / D 9933 V',
            'r_02' => 'R-02 / NKR816-7000009',
            'r_03' => 'R-03 / NKR71G-7403639',
            'r_04' => 'R-04 / D 9964 Y',
            's_01' => 'S-01 / D 8517 V',
            's_02' => 'S-02 / D 8516 V',
        ];

        $posList = [
            'baleendah' => 'Baleendah',
            'cicalengka' => 'Cicalengka',
            'cileunyi' => 'Cileunyi',
            'ciparay' => 'Ciparay',
            'majalaya' => 'Majalaya',
            'pacira' => 'Pacira',
            'tki' => 'TKI',
            'pangalengan' => 'Pangalengan',
            'soreang' => 'Soreang',
        ];

        $reguList = [
            'regu_1' => 'Regu 1',
            'regu_2' => 'Regu 2',
        ];

        // Pilihan kondisi generik yang dipakai di semua dropdown pengecekan
        $kondisiOptions = [
            'baik'        => 'Baik',
            'rusak'       => 'Rusak',
        ];

        $levelairOptions = [
            'penuh'        => 'Penuh',
            'setengah'     => 'Setengah',
            'kosong'       => 'Kosong',
        ];

        // ===== Bagian: Tangki & Pompa =====
        $leverairItems = $this->toFields([
            'Level Air'
        ]);
        $tangkiPompaItems = $this->toFields([
             'Kondisi Tangki',
            'Kebocoran Tangki', 'Tekanan Pompa',
            'Pengisian Pompa', 'Selang Induk',
        ]);

        // ===== Bagian: Bagian Dalam Unit =====
        $bagianDalamItems = $this->toFields([
            'Engine Stater', 'Rem Tangan',
            'Rem Kaki', 'Kelistrikan',
            'Klakson', 'Sirine Tunggal',
            'Sirine', 'Speedometer',
            'Dashboard Camera', 'GPS Tracker',
            'Flasher Sein Kanan-Kiri', 'Spion Dalam',
            'RIG', 'Speaker',
            'Megaphone (Toa)', 'Oli Power Stearing',
            'Air Radiator', 'Minyak Rem',
            'Oli Mesin', 'Air Wiper',
            'AC', 'Kebersihan Bagian Dalam',
        ]);


        // ===== Bagian: Bagian Luar Unit =====
        $bagianLuarItems = $this->toFields([
            'Lampu Depan (Dim) Kanan', 'Lampu Depan (Dim) Kiri',
            'Lampu Belakang Kanan', 'Lampu Belakang Kiri',
            'Lampu Belakang Hazard', 'Lampu Sein Depan Kanan',
            'Lampu Sein Depan Kiri', 'Lampu Sein Belakang Kiri',
            'Lampu Sein Belakang Kiri', 'Spion Kanan',
            'Spion Kiri', 'Wiper',
            'Winch', 'Ban Depan Kanan',
            'Ban Depan Kiri', 'Ban Belakang Kanan',
            'Ban Belakang Kiri', 'Ban Cadangan',
            'Lampu Rotary', 'Lampu Rem Kanan',
            'Lampu Rem Kiri', 'Pintu Kompartemen Kanan',
            'Pintu Kompartemen Kiri', 'Pintu Kompartemen Belakang',
            'Ganjal Ban', 'Dongkrak',
            'Kabin', 'Body Unit',
            'Kunci-Kunci', 'Kebersihan Bagian Luar',
        ]);

        return view('unit-pemadam.cek-harian-unit', compact(
            'posList',
            'reguList',
            'unitList',
            'kondisiOptions',
            'levelairOptions',
            'leverairItems',
            'tangkiPompaItems',
            'bagianDalamItems',
            'bagianLuarItems'
        ));
    }

    /**
     * Menyimpan data cek harian yang dikirim dari form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pemeriksa' => 'required|string|max:255',
            'jabatan'        => 'required|string|max:255',
            'unit'           => 'required|string',
            'pos'            => 'required|string',
            'regu'           => 'required|string',
            'komandan_regu' => 'required|string|max:255',
            // Field dropdown item pengecekan & upload foto sengaja tidak
            // divalidasi ketat satu-satu di sini supaya kodenya ringkas.
            // Silakan tambahkan validasi per field begitu struktur tabel
            // penyimpanannya sudah difinalkan.
        ]);

        // TODO: simpan $validated + input lain (item pengecekan & file
        // upload) ke tabel terkait, contoh:
        // CekHarianUnit::create($validated);
        // Untuk file: $request->file('foto_pemanasan')[0]->store('cek-harian');

        return redirect()
            ->route('unit-pemadam.cek-harian-unit')
            ->with('success', 'Data cek harian unit berhasil dikirim.');
    }

    /**
     * Helper: ubah daftar label jadi array [key => label], key berupa
     * slug (snake_case). Kalau ada label yang sama persis (duplikat),
     * otomatis diberi akhiran _2, _3, dst supaya key tetap unik.
     */
    private function toFields(array $labels): array
    {
        $result = [];
        $counts = [];

        foreach ($labels as $label) {
            $key = Str::slug($label, '_');

            if (isset($counts[$key])) {
                $counts[$key]++;
                $key .= '_' . $counts[$key];
            } else {
                $counts[$key] = 1;
            }

            $result[$key] = $label;
        }

        return $result;
    }
}
