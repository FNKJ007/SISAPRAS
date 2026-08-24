<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use Illuminate\Http\Request;

class PosManagementController extends Controller
{
    /**
     * Halaman Utama Data Pos (CRUD Index).
     */
    public function index(Request $request)
    {
        $searchQuery = $request->query('search', '');

        $query = Pos::orderBy('id', 'asc');

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('kode_pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('wilayah', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('alamat', 'LIKE', "%{$searchQuery}%");
            });
        }

        $posList = $query->paginate(15)->withQueryString();

        $allPos = Pos::all();

        $kpi = [
            'total_pos'      => $allPos->count(),
            'total_unit'     => $allPos->sum(fn($p) => $p->total_unit),
            'truck_pancar'   => $allPos->sum('unit_truck_pancar'),
            'motor_roda3'    => $allPos->sum('unit_motor_roda3'),
            'motor_roda2'    => $allPos->sum('unit_motor_roda2'),
            'unit_pompa'     => $allPos->sum('unit_pompa'),
            'unit_rescue'    => $allPos->sum('unit_rescue'),
            'water_supply'   => $allPos->sum('unit_water_supply'),
            'unit_lainnya'   => $allPos->sum('unit_lainnya'),
        ];

        return view('admin.pemeliharaan.data-pos.index', compact('posList', 'kpi', 'searchQuery'));
    }

    /**
     * Simpan data pos baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'nama.required' => 'Nama pos Damkar wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'kode_pos'  => 'nullable|string|max:100',
            'alamat'    => 'nullable|string|max:500',
            'wilayah'   => 'nullable|string|max:255',
            'telepon'   => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',
        ], $messages);

        $validated['status'] = 'aktif';

        Pos::create($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-pos')
            ->with('success', "Data pos '{$validated['nama']}' berhasil ditambahkan.");
    }

    /**
     * Update data pos.
     */
    public function update(Request $request, $id)
    {
        $pos = Pos::findOrFail($id);

        $messages = [
            'nama.required' => 'Nama pos Damkar wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'kode_pos'  => 'nullable|string|max:100',
            'alamat'    => 'nullable|string|max:500',
            'wilayah'   => 'nullable|string|max:255',
            'telepon'   => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',
        ], $messages);

        $pos->update($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-pos')
            ->with('success', "Data pos '{$pos->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data pos.
     */
    public function destroy($id)
    {
        $pos = Pos::findOrFail($id);
        $nama = $pos->nama;
        $pos->delete();

        return redirect()
            ->route('admin.pemeliharaan.data-pos')
            ->with('success', "Data pos '{$nama}' berhasil dihapus.");
    }
}
