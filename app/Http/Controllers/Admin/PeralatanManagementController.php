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
        $searchQuery    = $request->query('search', '');

        $query = Peralatan::orderBy('kategori', 'asc')->orderBy('nama', 'asc');

        if ($kategoriFilter !== 'semua') {
            $query->where('kategori', 'LIKE', str_replace('_', ' ', $kategoriFilter));
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
            'pemadam'        => Peralatan::where('kategori', 'LIKE', 'pemadam')->count(),
            'rescue'         => Peralatan::where('kategori', 'LIKE', 'rescue')->count(),
            'pencegahan'     => Peralatan::where('kategori', 'LIKE', 'pencegahan')->count(),
            'command_center' => Peralatan::where('kategori', 'LIKE', '%command%')->count(),
        ];

        $existingKategoriList = Peralatan::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->get()
            ->pluck('kategori')
            ->map(fn($v) => ucwords(strtolower(str_replace('_', ' ', trim($v)))))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingKategoriList)) {
            $existingKategoriList = ['Pemadam', 'Rescue', 'Pencegahan', 'Command Center'];
        }

        return view('admin.pemeliharaan.data-peralatan.index', compact(
            'peralatanList',
            'kpi',
            'kategoriFilter',
            'searchQuery',
            'existingKategoriList'
        ));
    }

    /**
     * Simpan data peralatan baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'nama.required'     => 'Nama peralatan wajib diisi.',
            'kategori.required' => 'Kategori peralatan wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'catatan'  => 'nullable|string',
        ], $messages);

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

        $messages = [
            'nama.required'     => 'Nama peralatan wajib diisi.',
            'kategori.required' => 'Kategori peralatan wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'     => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'catatan'  => 'nullable|string',
        ], $messages);

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

    /**
     * Hapus Opsi Riwayat (Typo/Kesalahan) dari Database Data Peralatan.
     */
    public function removeHistoryOption(Request $request)
    {
        $request->validate([
            'type'  => 'required|string|in:kategori',
            'value' => 'required|string',
        ]);

        $value = trim($request->input('value'));
        Peralatan::where('kategori', 'LIKE', $value)->update(['kategori' => null]);

        return response()->json([
            'success' => true,
            'message' => "Opsi riwayat '{$value}' berhasil dihapus dari data peralatan."
        ]);
    }
}
