<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Pos;
use Illuminate\Http\Request;

use App\Traits\HandlesCekHarianAlat;

class CekAlatCcController extends Controller
{
    use HandlesCekHarianAlat;

    /**
     * Menampilkan form Cek Harian Alat Command Center.
     */
    public function index()
    {
        $reguList = [
            'Regu 1',
            'Regu 2',
            'Regu 3',
            'Regu 4',
        ];

        // Ambil data peralatan Command Center dari database Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'command_center')->orderBy('nama', 'asc')->get();

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-cc.cek-alat-cc', compact('reguList', 'daftarAlat'));
    }

    /**
     * Menyimpan hasil pemeriksaan alat Command Center.
     */
    public function store(Request $request)
    {
        $record = $this->storeCekHarianAlat($request, 'command_center');

        return redirect()
            ->route('alat-cc.cek-alat-cc')
            ->with('success', "Pemeriksaan harian alat Command Center berhasil disimpan!");
    }
}