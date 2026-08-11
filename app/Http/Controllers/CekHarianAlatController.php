<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

use App\Traits\HandlesCekHarianAlat;

class CekHarianAlatController extends Controller
{
    use HandlesCekHarianAlat;

    /**
     * Daftar unit/kendaraan pemadam dari database Admin Data Unit.
     */
    protected function unitList()
    {
        return Unit::where('kategori', 'pemadam')->orderBy('nomor_lambung', 'asc')->get();
    }

    /**
     * Menampilkan form Cek Harian Alat Pemadam.
     */
    public function index()
    {
        $unitList = $this->unitList();
        $posList  = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        // Ambil data peralatan pemadam langsung dari Panel Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'pemadam')->orderBy('nama', 'asc')->get();

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-pemadam.cek-harian-alat', compact('unitList', 'daftarAlat', 'posList'));
    }

    /**
     * Menyimpan hasil pemeriksaan alat pemadam.
     */
    public function store(Request $request)
    {
        $record = $this->storeCekHarianAlat($request, 'pemadam');

        return redirect()
            ->route('alat-pemadam.cek-harian-alat')
            ->with('success', "Pemeriksaan harian alat Pemadam berhasil disimpan!");
    }
}
