<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peralatan;
use App\Services\CacheService;
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
            $query->where('kategori', 'ILIKE', str_replace('_', ' ', $kategoriFilter));
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('catatan', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $peralatanList = $query->paginate(12)->withQueryString();

        $kpi = CacheService::rememberStats('peralatan_kpi', function () {
            $kpiRaw = Peralatan::selectRaw("
                count(*) as total,
                count(case when lower(kategori) like '%pemadam%' then 1 end) as pemadam,
                count(case when lower(kategori) like '%rescue%' then 1 end) as rescue,
                count(case when lower(kategori) like '%pencegahan%' then 1 end) as pencegahan,
                count(case when lower(kategori) like '%command%' then 1 end) as command_center
            ")->first();

            return [
                'total'          => (int) ($kpiRaw->total ?? 0),
                'pemadam'        => (int) ($kpiRaw->pemadam ?? 0),
                'rescue'         => (int) ($kpiRaw->rescue ?? 0),
                'pencegahan'     => (int) ($kpiRaw->pencegahan ?? 0),
                'command_center' => (int) ($kpiRaw->command_center ?? 0),
            ];
        });

        $existingKategoriList = CacheService::rememberStats('peralatan_categories', function () {
            $list = Peralatan::whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->distinct()
                ->pluck('kategori')
                ->map(fn($v) => ucwords(strtolower(str_replace('_', ' ', trim($v)))))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            return empty($list) ? ['Pemadam', 'Rescue', 'Pencegahan', 'Command Center'] : $list;
        });

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
        CacheService::invalidate('peralatan');

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
        CacheService::invalidate('peralatan');

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
        CacheService::invalidate('peralatan');

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
        Peralatan::where('kategori', 'ILIKE', $value)->update(['kategori' => null]);
        CacheService::invalidate('peralatan');

        return response()->json([
            'success' => true,
            'message' => "Opsi riwayat '{$value}' berhasil dihapus dari data peralatan."
        ]);
    }
}
