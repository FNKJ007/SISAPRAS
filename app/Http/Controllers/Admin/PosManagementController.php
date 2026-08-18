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
        $statusFilter = $request->query('status', 'semua');
        $searchQuery  = $request->query('search', '');

        $query = Pos::orderBy('id', 'asc');

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['aktif', 'nonaktif'])) {
            $query->where('status', $statusFilter);
        }

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
            'total_personil' => $allPos->sum(fn($p) => $p->total_personil),
            'pemadam'        => $allPos->sum('personil_pemadam'),
            'rescue'         => $allPos->sum('personil_rescue'),
            'cc'             => $allPos->sum('personil_cc'),
            'total_unit'     => $allPos->sum(fn($p) => $p->total_unit),
            'truck_pancar'   => $allPos->sum('unit_truck_pancar'),
            'motor_roda3'    => $allPos->sum('unit_motor_roda3'),
            'motor_roda2'    => $allPos->sum('unit_motor_roda2'),
            'unit_pompa'     => $allPos->sum('unit_pompa'),
            'unit_rescue'    => $allPos->sum('unit_rescue'),
            'water_supply'   => $allPos->sum('unit_water_supply'),
            'unit_lainnya'   => $allPos->sum('unit_lainnya'),
        ];

        return view('admin.pemeliharaan.data-pos.index', compact('posList', 'kpi', 'statusFilter', 'searchQuery'));
    }

    /**
     * Simpan data pos baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'nama.required'             => 'Nama pos Damkar wajib diisi.',
            'personil_pemadam.required' => 'Jumlah personil pemadam wajib diisi.',
            'personil_pemadam.integer'  => 'Jumlah personil pemadam harus berupa angka bulat positif.',
            'personil_pemadam.min'      => 'Jumlah personil pemadam minimal 0.',
            'personil_rescue.required'  => 'Jumlah personil rescue wajib diisi.',
            'personil_rescue.integer'   => 'Jumlah personil rescue harus berupa angka bulat positif.',
            'personil_rescue.min'       => 'Jumlah personil rescue minimal 0.',
            'personil_cc.required'      => 'Jumlah personil command center wajib diisi.',
            'personil_cc.integer'       => 'Jumlah personil command center harus berupa angka bulat positif.',
            'personil_cc.min'          => 'Jumlah personil command center minimal 0.',
            'status.required'           => 'Status pos wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'              => 'required|string|max:255',
            'kode_pos'          => 'nullable|string|max:100',
            'personil_pemadam'  => 'required|integer|min:0',
            'personil_rescue'   => 'required|integer|min:0',
            'personil_cc'       => 'required|integer|min:0',
            'unit_truck_pancar' => 'nullable|integer|min:0',
            'unit_motor_roda3'  => 'nullable|integer|min:0',
            'unit_motor_roda2'  => 'nullable|integer|min:0',
            'unit_pompa'        => 'nullable|integer|min:0',
            'unit_rescue'       => 'nullable|integer|min:0',
            'unit_water_supply' => 'nullable|integer|min:0',
            'unit_lainnya'      => 'nullable|integer|min:0',
            'alamat'            => 'nullable|string|max:500',
            'wilayah'           => 'nullable|string|max:255',
            'telepon'           => 'nullable|string|max:100',
            'status'            => 'required|in:aktif,nonaktif',
            'catatan'           => 'nullable|string',
        ], $messages);

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
            'nama.required'             => 'Nama pos Damkar wajib diisi.',
            'personil_pemadam.required' => 'Jumlah personil pemadam wajib diisi.',
            'personil_pemadam.integer'  => 'Jumlah personil pemadam harus berupa angka bulat positif.',
            'personil_pemadam.min'      => 'Jumlah personil pemadam minimal 0.',
            'personil_rescue.required'  => 'Jumlah personil rescue wajib diisi.',
            'personil_rescue.integer'   => 'Jumlah personil rescue harus berupa angka bulat positif.',
            'personil_rescue.min'       => 'Jumlah personil rescue minimal 0.',
            'personil_cc.required'      => 'Jumlah personil command center wajib diisi.',
            'personil_cc.integer'       => 'Jumlah personil command center harus berupa angka bulat positif.',
            'personil_cc.min'          => 'Jumlah personil command center minimal 0.',
            'status.required'           => 'Status pos wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'              => 'required|string|max:255',
            'kode_pos'          => 'nullable|string|max:100',
            'personil_pemadam'  => 'required|integer|min:0',
            'personil_rescue'   => 'required|integer|min:0',
            'personil_cc'       => 'required|integer|min:0',
            'unit_truck_pancar' => 'nullable|integer|min:0',
            'unit_motor_roda3'  => 'nullable|integer|min:0',
            'unit_motor_roda2'  => 'nullable|integer|min:0',
            'unit_pompa'        => 'nullable|integer|min:0',
            'unit_rescue'       => 'nullable|integer|min:0',
            'unit_water_supply' => 'nullable|integer|min:0',
            'unit_lainnya'      => 'nullable|integer|min:0',
            'alamat'            => 'nullable|string|max:500',
            'wilayah'           => 'nullable|string|max:255',
            'telepon'           => 'nullable|string|max:100',
            'status'            => 'required|in:aktif,nonaktif',
            'catatan'           => 'nullable|string',
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
