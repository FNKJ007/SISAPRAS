<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Unit;
use App\Models\Pos;
use Illuminate\Http\Request;

use App\Traits\HandlesCekHarianAlat;

class CekHarianAlatRescueController extends Controller
{
    use HandlesCekHarianAlat;

    /**
     * Daftar unit/kendaraan rescue dari database Admin Data Unit.
     */
    protected function unitList()
    {
        return Unit::where('kategori', 'LIKE', 'rescue')->orderBy('nomor_lambung', 'asc')->get();
    }

    /**
     * Menampilkan form Cek Harian Alat Rescue.
     */
    public function index()
    {
        $unitList = $this->unitList();
        $posList  = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        // Ambil data peralatan rescue dari database Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'LIKE', 'rescue')->orderBy('nama', 'asc')->get();

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-rescue.cek-harian-alat-rescue', compact('unitList', 'daftarAlat', 'posList'));
    }

    /**
     * Menyimpan hasil pemeriksaan alat rescue.
     */
    public function store(Request $request)
    {
        $record = $this->storeCekHarianAlat($request, 'rescue');

        return redirect()
            ->route('alat-rescue.cek-harian-alat')
            ->with('success', "Pemeriksaan harian alat Rescue berhasil disimpan!")
            ->with('cek_id', $record->id);
    }

    /**
     * Mengunduh PDF hasil pemeriksaan alat rescue.
     */
    public function exportPdf($id)
    {
        return $this->exportCekHarianAlatPdf((int) $id, 'rescue');
    }
}
