<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Pos;
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

        if ($kategoriFilter !== 'semua') {
            $query->where('kategori', 'LIKE', $kategoriFilter);
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
                  ->orWhere('jenis_kendaraan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('peruntukan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('jenis_peruntukan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_1', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pengemudi_2', 'LIKE', "%{$searchQuery}%");
            });
        }

        $unitList = $query->paginate(15)->withQueryString();

        $kpi = [
            'total'     => Unit::count(),
            'pemadam'   => Unit::where('kategori', 'LIKE', 'pemadam')->count(),
            'rescue'    => Unit::where('kategori', 'LIKE', 'rescue')->count(),
            'aktif'     => Unit::where('status', 'aktif')->count(),
            'perbaikan' => Unit::where('status', 'perbaikan')->count(),
        ];

        $posList = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        $existingJenisList = Unit::whereNotNull('jenis_kendaraan')
            ->where('jenis_kendaraan', '!=', '')
            ->get()
            ->pluck('jenis_kendaraan')
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $existingPeruntukanList = Unit::whereNotNull('peruntukan')
            ->where('peruntukan', '!=', '')
            ->get()
            ->pluck('peruntukan')
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $existingKategoriList = Unit::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->get()
            ->pluck('kategori')
            ->map(fn($v) => ucwords(strtolower(str_replace('_', ' ', trim($v)))))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingKategoriList)) {
            $existingKategoriList = ['Pemadam', 'Rescue'];
        }

        return view('admin.pemeliharaan.data-unit.index', compact(
            'unitList',
            'kpi',
            'kategoriFilter',
            'statusFilter',
            'searchQuery',
            'posList',
            'existingJenisList',
            'existingPeruntukanList',
            'existingKategoriList'
        ));
    }

    /**
     * Simpan data unit baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'             => 'required|string|max:255',
            'kategori'         => 'required|string|max:100',
            'nomor_lambung'    => 'nullable|string|max:100',
            'plat_nomor'       => 'nullable|string|max:100',
            'no_rangka_mesin'  => 'nullable|string|max:100',
            'merk_tipe'        => 'nullable|string|max:255',
            'tahun_pembuatan'  => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'cc'               => 'nullable|string|max:50',
            'jenis_kendaraan'  => 'nullable|string|max:100',
            'peruntukan'       => 'nullable|string|max:100',
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
            'kategori'         => 'required|string|max:100',
            'nomor_lambung'    => 'nullable|string|max:100',
            'plat_nomor'       => 'nullable|string|max:100',
            'no_rangka_mesin'  => 'nullable|string|max:100',
            'merk_tipe'        => 'nullable|string|max:255',
            'tahun_pembuatan'  => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'cc'               => 'nullable|string|max:50',
            'jenis_kendaraan'  => 'nullable|string|max:100',
            'peruntukan'       => 'nullable|string|max:100',
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

    /**
     * Hapus Opsi Riwayat (Typo/Kesalahan) dari Database Data Unit.
     */
    public function removeHistoryOption(Request $request)
    {
        $request->validate([
            'type'  => 'required|string|in:jenis_kendaraan,peruntukan,kategori',
            'value' => 'required|string',
        ]);

        $type  = $request->input('type');
        $value = trim($request->input('value'));

        if ($type === 'jenis_kendaraan') {
            Unit::where('jenis_kendaraan', 'LIKE', $value)->update(['jenis_kendaraan' => null]);
        } elseif ($type === 'peruntukan') {
            Unit::where('peruntukan', 'LIKE', $value)->update(['peruntukan' => null]);
        } elseif ($type === 'kategori') {
            Unit::where('kategori', 'LIKE', $value)->update(['kategori' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => "Opsi riwayat '{$value}' berhasil dihapus dari data unit."
        ]);
    }
}
