<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use App\Models\Regu;
use App\Models\User;
use Illuminate\Http\Request;

class ReguManagementController extends Controller
{
    /**
     * Halaman Utama Data Master Regu (CRUD Index).
     */
    public function index(Request $request)
    {
        $posFilter    = $request->query('pos', 'semua');
        $bidangFilter = $request->query('bidang', 'semua');
        $statusFilter = $request->query('status', 'semua');
        $searchQuery  = $request->query('search', '');

        $query = Regu::orderBy('pos', 'asc')->orderBy('bidang', 'asc')->orderBy('nama', 'asc');

        if ($posFilter !== 'semua') {
            $query->where('pos', $posFilter);
        }

        if ($bidangFilter !== 'semua') {
            $query->where('bidang', $bidangFilter);
        }

        if ($statusFilter !== 'semua' && in_array($statusFilter, ['aktif', 'nonaktif'])) {
            $query->where('status', $statusFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('nama', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('bidang', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('danru', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nip_danru', 'LIKE', "%{$searchQuery}%");
            });
        }

        $reguList = $query->paginate(15)->withQueryString();

        $allRegu = Regu::all();
        $posList = Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get();

        $danruList = User::where(function($q) {
                $q->where('jabatan', 'LIKE', '%Danru%')
                  ->orWhere('jabatan', 'LIKE', '%Komandan%');
            })
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'nip', 'jabatan', 'pos', 'bidang', 'regu']);

        if ($danruList->isEmpty()) {
            $danruList = User::orderBy('name', 'asc')->get(['id', 'name', 'nip', 'jabatan', 'pos', 'bidang', 'regu']);
        }

        $allPegawai = User::orderBy('name', 'asc')->get(['id', 'name', 'nip', 'jabatan', 'pos', 'bidang', 'regu']);

        $bidangOptions = ['Pemadam', 'Rescue', 'Pencegahan', 'Command Center'];

        $kpi = [
            'total_regu'       => $allRegu->count(),
            'regu_aktif'       => $allRegu->where('status', 'aktif')->count(),
            'regu_pemadam'     => $allRegu->where('bidang', 'Pemadam')->count(),
            'regu_rescue'      => $allRegu->where('bidang', 'Rescue')->count(),
            'regu_pencegahan'  => $allRegu->where('bidang', 'Pencegahan')->count(),
            'regu_cc'          => $allRegu->where('bidang', 'Command Center')->count(),
        ];

        return view('admin.pemeliharaan.data-regu.index', compact(
            'reguList',
            'posList',
            'danruList',
            'allPegawai',
            'bidangOptions',
            'kpi',
            'posFilter',
            'bidangFilter',
            'statusFilter',
            'searchQuery'
        ));
    }

    /**
     * Simpan data regu baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'nama.required'   => 'Nama regu wajib diisi (contoh: Regu 1).',
            'pos.required'    => 'Pos penempatan regu wajib dipilih.',
            'bidang.required' => 'Bidang tugas regu wajib dipilih.',
            'status.required' => 'Status regu wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'pos'       => 'required|string|max:255',
            'bidang'    => 'required|string|max:100',
            'danru'     => 'nullable|string|max:255',
            'nip_danru' => 'nullable|string|max:100',
            'status'    => 'required|in:aktif,nonaktif',
            'catatan'   => 'nullable|string',
        ], $messages);

        Regu::create($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-regu')
            ->with('success', "Data '{$validated['nama']}' di {$validated['pos']} ({$validated['bidang']}) berhasil ditambahkan.");
    }

    /**
     * Update data regu.
     */
    public function update(Request $request, $id)
    {
        $regu = Regu::findOrFail($id);

        $messages = [
            'nama.required'   => 'Nama regu wajib diisi.',
            'pos.required'    => 'Pos penempatan regu wajib dipilih.',
            'bidang.required' => 'Bidang tugas regu wajib dipilih.',
            'status.required' => 'Status regu wajib diisi.',
        ];

        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'pos'       => 'required|string|max:255',
            'bidang'    => 'required|string|max:100',
            'danru'     => 'nullable|string|max:255',
            'nip_danru' => 'nullable|string|max:100',
            'status'    => 'required|in:aktif,nonaktif',
            'catatan'   => 'nullable|string',
        ], $messages);

        $regu->update($validated);

        return redirect()
            ->route('admin.pemeliharaan.data-regu')
            ->with('success', "Data '{$regu->nama}' ({$regu->pos}) berhasil diperbarui.");
    }

    /**
     * Hapus data regu.
     */
    public function destroy($id)
    {
        $regu = Regu::findOrFail($id);
        $nama = "{$regu->nama} - {$regu->pos} ({$regu->bidang})";
        $regu->delete();

        return redirect()
            ->route('admin.pemeliharaan.data-regu')
            ->with('success', "Data regu '{$nama}' berhasil dihapus.");
    }
}