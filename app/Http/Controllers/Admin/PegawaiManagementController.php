<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PegawaiManagementController extends Controller
{
    /**
     * Halaman Utama Data Master Pegawai (CRUD Index).
     */
    public function index(Request $request)
    {
        $searchQuery  = $request->query('search', '');
        $bidangFilter = $request->query('bidang', 'semua');
        $posFilter    = $request->query('pos', 'semua');

        $query = User::orderBy('name', 'asc');

        if ($bidangFilter !== 'semua') {
            $query->where('bidang', 'LIKE', $bidangFilter);
        }

        if ($posFilter !== 'semua') {
            $query->where('pos', 'LIKE', $posFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('nip', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('jabatan', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('bidang', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'LIKE', "%{$searchQuery}%");
            });
        }

        $pegawaiList = $query->paginate(15)->withQueryString();

        $kpi = CacheService::rememberStats('pegawai_kpi', function () {
            $allUsers = User::select(['id', 'jabatan'])->get();
            return [
                'total_pegawai' => $allUsers->count(),
                'pejabat'       => $allUsers->filter(fn($u) => preg_match('/(kepala|kabid|kasi|sekretaris|kadis|kasubag|subbag|sub\s*bagian)/i', (string) $u->jabatan))->count(),
                'danru'         => $allUsers->filter(fn($u) => preg_match('/(danru|komandan)/i', (string) $u->jabatan))->count(),
                'petugas'       => $allUsers->filter(fn($u) => !preg_match('/(kepala|kabid|kasi|sekretaris|kadis|kasubag|subbag|sub\s*bagian|danru|komandan)/i', (string) $u->jabatan))->count(),
            ];
        });

        $posList = CacheService::rememberList('active_pos_objects', function () {
            return \App\Models\Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get(['id', 'nama']);
        });

        $metaData = CacheService::rememberStats('pegawai_meta', function () {
            $existingBidangList = User::whereNotNull('bidang')
                ->where('bidang', '!=', '')
                ->distinct()
                ->pluck('bidang')
                ->map(fn($v) => trim($v))
                ->filter(fn($v) => !empty($v))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            $existingJabatanList = User::whereNotNull('jabatan')
                ->where('jabatan', '!=', '')
                ->distinct()
                ->pluck('jabatan')
                ->map(fn($v) => trim($v))
                ->filter(fn($v) => !empty($v))
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            return compact('existingBidangList', 'existingJabatanList');
        });

        $existingBidangList = $metaData['existingBidangList'];
        $existingJabatanList = $metaData['existingJabatanList'];

        return view('admin.pemeliharaan.data-pegawai.index', compact(
            'pegawaiList',
            'kpi',
            'searchQuery',
            'bidangFilter',
            'posFilter',
            'posList',
            'existingBidangList',
            'existingJabatanList'
        ));
    }

    /**
     * Simpan data pegawai baru.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required'    => 'Nama lengkap pegawai wajib diisi.',
            'nip.required'     => 'NIP pegawai wajib diisi.',
            'jabatan.required' => 'Jabatan pegawai wajib diisi.',
        ];

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'nip'     => 'required|string|max:50',
            'jabatan' => 'required|string|max:255',
            'bidang'  => 'nullable|string|max:255',
            'pos'     => 'nullable|string|max:255',
            'regu'    => 'nullable|string|max:50',
            'no_hp'   => 'nullable|string|max:30',
        ], $messages);

        // Generate email unik berbasis NIP
        $cleanNip = preg_replace('/[^0-9]/', '', $validated['nip']);
        $uniqueEmail = !empty($cleanNip) ? "{$cleanNip}@disdamkar.go.id" : 'pegawai_' . Str::random(8) . '@disdamkar.go.id';
        if (User::where('email', $uniqueEmail)->exists()) {
            $uniqueEmail = 'pegawai_' . time() . '_' . Str::random(4) . '@disdamkar.go.id';
        }

        User::create([
            'name'        => $validated['name'],
            'nip'         => $validated['nip'],
            'jabatan'     => $validated['jabatan'],
            'bidang'      => $validated['bidang'] ?? 'Sarana Prasarana Dan Informasi',
            'pos'         => $validated['pos'] ?? 'Soreang (MAKO)',
            'regu'        => $validated['regu'] ?? null,
            'no_hp'       => $validated['no_hp'] ?? null,
            'email'       => $uniqueEmail,
            'password'    => Hash::make(Str::random(32)),
            'role'        => 'user',
            'has_account' => false,
            'status'      => 'aktif',
        ]);
        CacheService::invalidate('user');

        return redirect()
            ->route('admin.pemeliharaan.data-pegawai')
            ->with('success', "Data pegawai '{$validated['name']}' berhasil ditambahkan.");
    }

    /**
     * Update data pegawai.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $messages = [
            'name.required'    => 'Nama lengkap pegawai wajib diisi.',
            'nip.required'     => 'NIP pegawai wajib diisi.',
            'jabatan.required' => 'Jabatan pegawai wajib diisi.',
        ];

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'nip'     => 'required|string|max:50',
            'jabatan' => 'required|string|max:255',
            'bidang'  => 'nullable|string|max:255',
            'pos'     => 'nullable|string|max:255',
            'regu'    => 'nullable|string|max:50',
            'no_hp'   => 'nullable|string|max:30',
        ], $messages);

        $user->update([
            'name'    => $validated['name'],
            'nip'     => $validated['nip'],
            'jabatan' => $validated['jabatan'],
            'bidang'  => $validated['bidang'] ?? $user->bidang,
            'pos'     => $validated['pos'] ?? $user->pos,
            'regu'    => $validated['regu'] ?? $user->regu,
            'no_hp'   => $validated['no_hp'] ?? $user->no_hp,
        ]);
        CacheService::invalidate('user');

        return redirect()
            ->route('admin.pemeliharaan.data-pegawai')
            ->with('success', "Data pegawai '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Hapus data pegawai.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();
        CacheService::invalidate('user');

        return redirect()
            ->route('admin.pemeliharaan.data-pegawai')
            ->with('success', "Data pegawai '{$name}' berhasil dihapus.");
    }
}