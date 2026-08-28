<?php

namespace App\Http\Controllers;

use App\Models\CekHarianAlat;
use App\Models\Peralatan;
use App\Models\Pos;
use Illuminate\Http\Request;

use App\Traits\HandlesCekHarianAlat;
use App\Traits\HandlesOfficialsData;

class CekAlatCcController extends Controller
{
    use HandlesCekHarianAlat, HandlesOfficialsData;

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
        $posList = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();
        $officials = $this->getOfficialsData('command center');

        // Ambil data peralatan Command Center dari database Admin Data Peralatan (Urut A-Z)
        $peralatanDb = Peralatan::where('kategori', 'ILIKE', '%command%')->orderBy('nama', 'asc')->get();

        $daftarAlat = $peralatanDb->map(function ($item) {
            return (object) [
                'id'           => $item->id,
                'nama'         => $item->nama,
                'jumlah_total' => $item->jumlah_total,
                'jumlah_baik'  => 0,
                'jumlah_rusak' => 0,
            ];
        });

        return view('auth.alat-cc.cek-alat-cc', array_merge(
            compact('reguList', 'posList', 'daftarAlat'),
            $officials
        ));
    }

    /**
     * Menyimpan hasil pemeriksaan alat Command Center.
     */
    public function store(Request $request)
    {
        $record = $this->storeCekHarianAlat($request, 'command_center');

        return redirect()
            ->route('alat-cc.cek-alat-cc')
            ->with('success', "Pemeriksaan harian alat Command Center berhasil disimpan!")
            ->with('cek_id', $record->id);
    }

    /**
     * Export PDF hasil pemeriksaan alat Command Center.
     */
    public function exportPdf(int $id)
    {
        return $this->exportCekHarianAlatPdf($id, 'command_center');
    }

    /**
     * Menampilkan riwayat pemeriksaan alat Command Center untuk user.
     */
    public function riwayat(Request $request)
    {
        $tab         = 'alat';
        $searchQuery = $request->query('search', '');
        $tanggal     = $request->query('tanggal', '');

        $alatQuery = CekHarianAlat::where('user_id', auth()->id())
            ->where('kategori', 'command_center');

        if (!empty($searchQuery)) {
            $alatQuery->where(function ($q) use ($searchQuery) {
                $q->where('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_pemeriksa', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_danru', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nama_kabid', 'ILIKE', "%{$searchQuery}%");
            });
        }

        if (!empty($tanggal)) {
            $alatQuery->where(function ($q) use ($tanggal) {
                $q->whereDate('tanggal_pemeriksaan', $tanggal)
                  ->orWhereDate('created_at', $tanggal);
            });
        }

        $cekAlatList = $alatQuery->latest('tanggal_pemeriksaan')
            ->latest('id')
            ->paginate(10, ['*'], 'alat_page')
            ->withQueryString();

        return view('auth.alat-cc.riwayat', compact('cekAlatList', 'tab', 'searchQuery', 'tanggal'));
    }
}