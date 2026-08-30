<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use App\Models\Regu;
use App\Models\User;
use App\Services\CacheService;
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
                $q->where('nama', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('bidang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('danru', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nip_danru', 'ILIKE', "%{$searchQuery}%");
            });
        }

        $reguList = $query->paginate(15)->withQueryString();

        $posList = CacheService::rememberList('active_pos_objects', function () {
            return Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get(['id', 'nama']);
        });

        $danruList = CacheService::rememberList('danru_list', function () {
            $list = User::where(function ($q) {
                    $q->where('jabatan', 'ILIKE', '%Danru%')
                      ->orWhere('jabatan', 'ILIKE', '%Komandan%');
                })
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'nip', 'jabatan', 'pos', 'bidang', 'regu']);

            return $list->isEmpty()
                ? User::orderBy('name', 'asc')->get(['id', 'name', 'nip', 'jabatan', 'pos', 'bidang', 'regu'])
                : $list;
        });

        $bidangOptions = ['Pemadam', 'Rescue', 'Pencegahan', 'Command Center'];
        
        $kpi = CacheService::rememberStats('regu_kpi', function () {
            $kpiRaw = Regu::selectRaw("
                count(*) as total_regu,
                count(case when status = 'aktif' then 1 end) as regu_aktif,
                count(case when lower(bidang) like '%pemadam%' then 1 end) as regu_pemadam,
                count(case when lower(bidang) like '%rescue%' then 1 end) as regu_rescue,
                count(case when lower(bidang) like '%pencegahan%' then 1 end) as regu_pencegahan,
                count(case when lower(bidang) like '%command%' then 1 end) as regu_cc
            ")->first();

            return [
                'total_regu'       => (int) ($kpiRaw->total_regu ?? 0),
                'regu_aktif'       => (int) ($kpiRaw->regu_aktif ?? 0),
                'regu_pemadam'     => (int) ($kpiRaw->regu_pemadam ?? 0),
                'regu_rescue'      => (int) ($kpiRaw->regu_rescue ?? 0),
                'regu_pencegahan'  => (int) ($kpiRaw->regu_pencegahan ?? 0),
                'regu_cc'          => (int) ($kpiRaw->regu_cc ?? 0),
            ];
        });

        return view('admin.pemeliharaan.data-regu.index', compact(
            'reguList',
            'posList',
            'danruList',
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
            'nama'           => 'required|string|max:100',
            'pos'            => 'required|string|max:255',
            'bidang'         => 'required|string|max:100',
            'danru'          => 'nullable|string|max:255',
            'nip_danru'      => 'nullable|string|max:100',
            'danru_user_id'  => 'nullable|integer',
            'status'         => 'required|in:aktif,nonaktif',
            'catatan'        => 'nullable|string',
        ], $messages);

        // Auto-resolve danru_user_id from NIP if not provided
        if (empty($validated['danru_user_id']) && !empty($validated['nip_danru'])) {
            $cleanNip = preg_replace('/\s+/', '', trim($validated['nip_danru']));
            $danruUser = User::whereRaw("REPLACE(nip, ' ', '') = ?", [$cleanNip])->first();
            if ($danruUser) {
                $validated['danru_user_id'] = $danruUser->id;
            }
        }

        Regu::create($validated);
        CacheService::invalidate('regu');

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
            'nama'           => 'required|string|max:100',
            'pos'            => 'required|string|max:255',
            'bidang'         => 'required|string|max:100',
            'danru'          => 'nullable|string|max:255',
            'nip_danru'      => 'nullable|string|max:100',
            'danru_user_id'  => 'nullable|integer',
            'status'         => 'required|in:aktif,nonaktif',
            'catatan'        => 'nullable|string',
        ], $messages);

        // Auto-resolve danru_user_id from NIP if not provided
        if (empty($validated['danru_user_id']) && !empty($validated['nip_danru'])) {
            $cleanNip = preg_replace('/\s+/', '', trim($validated['nip_danru']));
            $danruUser = User::whereRaw("REPLACE(nip, ' ', '') = ?", [$cleanNip])->first();
            if ($danruUser) {
                $validated['danru_user_id'] = $danruUser->id;
            }
        }

        $regu->update($validated);
        CacheService::invalidate('regu');

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
        CacheService::invalidate('regu');

        return redirect()
            ->route('admin.pemeliharaan.data-regu')
            ->with('success', "Data regu '{$nama}' berhasil dihapus.");
    }
}