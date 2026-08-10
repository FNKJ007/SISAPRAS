<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peralatan;
use Illuminate\Http\Request;

class PeralatanManagementController extends Controller
{
    /**
     * Halaman Utama Data Peralatan (CRUD Index).
     */
    public function index(Request $request)
    {
        $kategoriFilter = $request->query('kategori', 'semua');
        $statusFilter   = $request->query('status', 'semua');
        $searchQuery    = $request->query('search', '');

        $query = Peralatan::latest();

        if ($kategoriFilter !== 'semua' && in_array($kategoriFilter, ['pemadam', 'rescue', 'command_center'])) {
            $query->where('kategori', $kategoriFilter);
        }

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['baik', 'perlu_perhatian', 'rusak'])) {
            $query->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('catatan', 'LIKE', "%{$searchQuery}%");
            });
        }

        $peralatanList = $query->paginate(12)->withQueryString();

        $kpi = [
            'total'          => Peralatan::count(),
            'pemadam'        => Peralatan::where('kategori', 'pemadam')->count(),
            'rescue'         => Peralatan::where('kategori', 'rescue')->count(),
            'command_center' => Peralatan::where('kategori', 'command_center')->count(),
            'baik'           => Peralatan::where('status', 'baik')->count(),
            'rusak'          => Peralatan::where('status', 'rusak')->count(),
        ];

        return view('admin.pemeliharaan.data-peralatan.index', compact('peralatanList', 'kpi', 'kategoriFilter', 'statusFilter', 'searchQuery'));
    }

    /**
     * Simpan data peralatan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'kategori'     => 'required|in:pemadam,rescue,command_center',
            'jumlah_total' => 'required|integer|min:0',
            'status'       => 'required|in:baik,perlu_perhatian,rusak',
            'catatan'      => 'nullable|string',
        ]);

        Peralatan::create($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-peralatan')
            ->with('success', "Data peralatan '{$validated['nama']}' berhasil ditambahkan.");
    }

    /**
     * Update data peralatan.
     */
    public function update(Request $request, $id)
    {
        $peralatan = Peralatan::findOrFail($id);

        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'kategori'     => 'required|in:pemadam,rescue,command_center',
            'jumlah_total' => 'required|integer|min:0',
            'status'       => 'required|in:baik,perlu_perhatian,rusak',
            'catatan'      => 'nullable|string',
        ]);

        $peralatan->update($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-peralatan')
            ->with('success', "Data peralatan '{$peralatan->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data peralatan.
     */
    public function destroy($id)
    {
        $peralatan = Peralatan::findOrFail($id);
        $nama = $peralatan->nama;
        $peralatan->delete();

        return redirect()
            ->route('admin.pemeliharaan.data-peralatan')
            ->with('success', "Data peralatan '{$nama}' berhasil dihapus.");
    }
}
