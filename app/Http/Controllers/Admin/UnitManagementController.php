<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitManagementController extends Controller
{
    /**
     * Halaman Utama Data Unit (CRUD Index).
     */
    public function index(Request $request)
    {
        $kategoriFilter = $request->query('kategori', 'semua');
        $statusFilter   = $request->query('status', 'semua');
        $searchQuery    = $request->query('search', '');

        $query = Unit::orderBy('id', 'asc');

        if ($kategoriFilter !== 'semua' && in_array($kategoriFilter, ['pemadam', 'rescue'])) {
            $query->where('kategori', $kategoriFilter);
        }

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['aktif', 'perbaikan', 'nonaktif'])) {
            $query->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nomor_lambung', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('plat_nomor', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('no_rangka_mesin', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('merk_tipe', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('jenis_peruntukan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_1', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_2', 'LIKE', "%{$searchQuery}%");
            });
        }

        $unitList = $query->paginate(15)->withQueryString();

        $kpi = [
            'total'     => Unit::count(),
            'pemadam'   => Unit::where('kategori', 'pemadam')->count(),
            'rescue'    => Unit::where('kategori', 'rescue')->count(),
            'aktif'     => Unit::where('status', 'aktif')->count(),
            'perbaikan' => Unit::where('status', 'perbaikan')->count(),
        ];

        return view('admin.pemeliharaan.data-unit.index', compact('unitList', 'kpi', 'kategoriFilter', 'statusFilter', 'searchQuery'));
    }

    /**
     * Simpan data unit baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kategori'         => 'required|in:pemadam,rescue',
            'nomor_lambung'    => 'nullable|string|max:100',
            'plat_nomor'       => 'nullable|string|max:100',
            'no_rangka_mesin'  => 'nullable|string|max:100',
            'merk_tipe'        => 'nullable|string|max:255',
            'tahun_pembuatan'  => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'cc'               => 'nullable|string|max:50',
            'jenis_peruntukan' => 'nullable|string|max:255',
            'pos'              => 'nullable|string|max:255',
            'pengemudi_1'      => 'nullable|string|max:255',
            'pengemudi_2'      => 'nullable|string|max:255',
            'status'           => 'required|in:aktif,perbaikan,nonaktif',
            'catatan'          => 'nullable|string',
        ]);

        Unit::create($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$validated['nama']}' berhasil ditambahkan.");
    }

    /**
     * Update data unit.
     */
    public function update(Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kategori'         => 'required|in:pemadam,rescue',
            'nomor_lambung'    => 'nullable|string|max:100',
            'plat_nomor'       => 'nullable|string|max:100',
            'no_rangka_mesin'  => 'nullable|string|max:100',
            'merk_tipe'        => 'nullable|string|max:255',
            'tahun_pembuatan'  => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'cc'               => 'nullable|string|max:50',
            'jenis_peruntukan' => 'nullable|string|max:255',
            'pos'              => 'nullable|string|max:255',
            'pengemudi_1'      => 'nullable|string|max:255',
            'pengemudi_2'      => 'nullable|string|max:255',
            'status'           => 'required|in:aktif,perbaikan,nonaktif',
            'catatan'          => 'nullable|string',
        ]);

        $unit->update($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$unit->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data unit.
     */
    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $nama = $unit->nama;
        $unit->delete();

        return redirect()
            ->route('admin.pemeliharaan.data-unit')
            ->with('success', "Data unit '{$nama}' berhasil dihapus.");
    }
}
