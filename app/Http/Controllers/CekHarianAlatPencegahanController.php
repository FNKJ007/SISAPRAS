<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;
use App\Traits\HandlesCekHarianAlat;

class CekHarianAlatPencegahanController extends Controller
{
    use HandlesCekHarianAlat;

    /**
     * Daftar unit/kendaraan pencegahan dari database Admin Data Unit.
     */
    protected function unitList()
    {
        return Unit::where('kategori', 'LIKE', 'pencegahan')
            ->orWhere('peruntukan', 'LIKE', 'pencegahan')
            ->orWhere('nomor_lambung', 'LIKE', 'PC-%')
            ->orderBy('nomor_lambung', 'asc')
            ->get();
    }

    /**
     * Menampilkan form Cek Harian Alat Pencegahan.
     */
    public function index()
    {
        $unitList = $this->unitList();
        $posList  = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        // Ambil data peralatan pencegahan dari database Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'LIKE', 'pencegahan')->orderBy('nama', 'asc')->get();
        if ($peralatanDb->isEmpty()) {
            $peralatanDb = Peralatan::orderBy('nama', 'asc')->take(10)->get();
        }

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-pencegahan.cek-harian-alat', compact('unitList', 'daftarAlat', 'posList'));
    }

    /**
     * Menyimpan hasil pemeriksaan alat pencegahan.
     */
    public function store(Request $request)
    {
        $cek = $this->storeCekHarianAlat($request, 'pencegahan');

        return redirect()
            ->route('alat-pencegahan.cek-harian-alat')
            ->with('success', 'Pemeriksaan alat pencegahan berhasil disimpan!')
            ->with('cek_id', $cek->id);
    }

    /**
     * Ekspor PDF Cek Harian Alat Pencegahan.
     */
    public function exportPdf(int $id)
    {
        return $this->exportCekHarianAlatPdf($id, 'pencegahan');
    }
}