<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlokasiKebersihanUnit;
use App\Models\Unit;
use Illuminate\Http\Request;

class AlokasiKebersihanController extends Controller
{
    /**
     * Tampilkan Halaman Alokasi Peralatan Kebersihan Kendaraan SPI.
     */
    public function index(Request $request)
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $search = $request->query('search', '');

        $unitsQuery = Unit::query();
        if (!empty($search)) {
            $unitsQuery->where(function($q) use ($search) {
                $q->where('nomor_lambung', 'LIKE', "%{$search}%")
                  ->orWhere('plat_nomor', 'LIKE', "%{$search}%")
                  ->orWhere('merk_tipe', 'LIKE', "%{$search}%")
                  ->orWhere('pos', 'LIKE', "%{$search}%");
            });
        }
        $units = $unitsQuery->orderBy('nomor_lambung', 'asc')->get();

        // Pastikan setiap unit memiliki record alokasi default untuk tahun ini
        foreach ($units as $unit) {
            AlokasiKebersihanUnit::firstOrCreate(
                ['unit_id' => $unit->id, 'tahun' => $tahun],
                [
                    'sabun_cuci' => '12 Botol',
                    'lap_handuk' => '4 Pcs',
                    'kanebo'     => '6 Pcs',
                    'semir_ban'  => '6 Kaleng',
                    'sikat_ban'  => '2 Pcs',
                    'pengharum'  => '12 Pcs',
                    'catatan'    => 'Alokasi standar tahunan SPI',
                ]
            );
        }

        $alokasiList = AlokasiKebersihanUnit::with('unit')
            ->where('tahun', $tahun)
            ->whereIn('unit_id', $units->pluck('id'))
            ->get();

        $tahunTersedia = range(date('Y') - 2, date('Y') + 3);

        return view('admin.pemeliharaan.alokasi-kebersihan', compact('units', 'alokasiList', 'tahun', 'tahunTersedia', 'search'));
    }

    /**
     * Update Alokasi Peralatan Kebersihan Unit.
     */
    public function update(Request $request, int $id)
    {
        $alokasi = AlokasiKebersihanUnit::findOrFail($id);

        $validated = $request->validate([
            'sabun_cuci' => 'nullable|string|max:100',
            'lap_handuk' => 'nullable|string|max:100',
            'kanebo'     => 'nullable|string|max:100',
            'semir_ban'  => 'nullable|string|max:100',
            'sikat_ban'  => 'nullable|string|max:100',
            'pengharum'  => 'nullable|string|max:100',
            'catatan'    => 'nullable|string|max:500',
        ]);

        $alokasi->update($validated);

        return redirect()->back()->with('success', "Alokasi peralatan kebersihan unit '{$alokasi->unit->nomor_lambung}' berhasil diperbarui.");
    }
}