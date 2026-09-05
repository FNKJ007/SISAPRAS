<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pos;
use App\Models\Regu;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            $query->where('bidang', 'ILIKE', $bidangFilter);
        }

        if ($posFilter !== 'semua') {
            $query->where('pos', 'ILIKE', $posFilter);
        }

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('nip', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('jabatan', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('bidang', 'ILIKE', "%{$searchQuery}%")
                  ->orWhere('pos', 'ILIKE', "%{$searchQuery}%");
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
            return Pos::where('status', 'aktif')->orderBy('nama', 'asc')->get(['id', 'nama']);
        });

        $allReguList = CacheService::rememberList('active_regu_list_data', function () {
            return Regu::where('status', 'aktif')->orderBy('pos', 'asc')->orderBy('nama', 'asc')->get(['id', 'nama', 'pos', 'bidang', 'danru', 'nip_danru'])->toArray();
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
            'allReguList',
            'existingBidangList',
            'existingJabatanList'
        ));
    }

    /**
     * Simpan data pegawai baru beserta pembuatan akun otomatis.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required'     => 'Nama lengkap pegawai wajib diisi.',
            'nip.required'      => 'NIP pegawai wajib diisi.',
            'nip.unique'        => 'NIP ini sudah terdaftar di data pegawai lain. Silakan gunakan NIP yang berbeda.',
            'password.required' => 'Password akun login pegawai wajib diisi.',
            'password.min'      => 'Password akun login minimal terdiri dari 6 karakter.',
            'jabatan.required'  => 'Jabatan pegawai wajib diisi.',
        ];

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|string|max:50|unique:users,nip',
            'password' => 'required|string|min:6',
            'jabatan'  => 'required|string|max:255',
            'bidang'   => 'nullable|string|max:255',
            'pos'      => 'nullable|string|max:255',
            'regu'     => 'nullable|string|max:50',
            'regu_id'  => 'nullable',
            'no_hp'    => 'nullable|string|max:30',
        ], $messages);

        // Generate email unik berbasis NIP
        $cleanNip = preg_replace('/[^0-9]/', '', $validated['nip']);
        $uniqueEmail = !empty($cleanNip) ? "{$cleanNip}@disdamkar.go.id" : 'pegawai_' . Str::random(8) . '@disdamkar.go.id';
        if (User::where('email', $uniqueEmail)->exists()) {
            $uniqueEmail = 'pegawai_' . time() . '_' . Str::random(4) . '@disdamkar.go.id';
        }

        $this->resolveReguData($validated);

        User::create([
            'name'        => $validated['name'],
            'nip'         => trim($validated['nip']),
            'jabatan'     => $validated['jabatan'],
            'bidang'      => $validated['bidang'] ?? 'Sarana Dan Informasi',
            'pos'         => $validated['pos'] ?? 'Soreang (MAKO)',
            'regu'        => $validated['regu'] ?? null,
            'regu_id'     => $validated['regu_id'] ?? null,
            'no_hp'       => $validated['no_hp'] ?? null,
            'email'       => $uniqueEmail,
            'password'    => Hash::make($validated['password']),
            'role'        => 'user',
            'has_account' => true,
            'status'      => 'aktif',
        ]);
        CacheService::invalidate(['user', 'regu']);

        return redirect()
            ->route('admin.pemeliharaan.data-pegawai')
            ->with('success', "Data pegawai '{$validated['name']}' berhasil ditambahkan dan akun login langsung siap digunakan.");
    }

    /**
     * Update data pegawai.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $messages = [
            'name.required'     => 'Nama lengkap pegawai wajib diisi.',
            'nip.required'      => 'NIP pegawai wajib diisi.',
            'nip.unique'        => 'NIP ini sudah terdaftar di data pegawai lain. Silakan gunakan NIP yang berbeda.',
            'jabatan.required'  => 'Jabatan pegawai wajib diisi.',
        ];

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => ['required', 'string', 'max:50', Rule::unique('users')->ignore($id)],
            'jabatan'  => 'required|string|max:255',
            'bidang'   => 'nullable|string|max:255',
            'pos'      => 'nullable|string|max:255',
            'regu'     => 'nullable|string|max:50',
            'regu_id'  => 'nullable',
            'no_hp'    => 'nullable|string|max:30',
        ], $messages);

        $this->resolveReguData($validated, $user);

        $user->update([
            'name'        => $validated['name'],
            'nip'         => trim($validated['nip']),
            'jabatan'     => $validated['jabatan'],
            'bidang'      => $validated['bidang'] ?? $user->bidang,
            'pos'         => $validated['pos'] ?? $user->pos,
            'regu'        => $validated['regu'] ?? $user->regu,
            'regu_id'     => $validated['regu_id'] ?? $user->regu_id,
            'no_hp'       => $validated['no_hp'] ?? $user->no_hp,
        ]);
        CacheService::invalidate(['user', 'regu']);

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
        CacheService::invalidate(['user', 'regu']);

        return redirect()
            ->route('admin.pemeliharaan.data-pegawai')
            ->with('success', "Data pegawai '{$name}' berhasil dihapus.");
    }

    /**
     * Resolusi dan sinkronisasi regu_id dan nama regu secara akurat.
     */
    protected function resolveReguData(array &$validated, ?User $user = null): void
    {
        if (!empty($validated['regu_id'])) {
            $regu = Regu::find($validated['regu_id']);
            if ($regu) {
                $validated['regu']    = $regu->nama;
                $validated['regu_id'] = $regu->id;
                if (empty($validated['pos']) && !empty($regu->pos)) {
                    $validated['pos'] = $regu->pos;
                }
                return;
            }
        }

        $pos      = $validated['pos'] ?? ($user->pos ?? null);
        $reguName = $validated['regu'] ?? ($user->regu ?? null);
        $bidang   = $validated['bidang'] ?? ($user->bidang ?? null);

        if (!empty($pos) && !empty($reguName)) {
            $pClean = strtolower(preg_replace('/[^a-z0-9]/', '', $pos));
            $rClean = strtolower(preg_replace('/[^a-z0-9]/', '', $reguName));
            $bClean = strtolower(trim($bidang ?? ''));

            $allRegus = Regu::all();
            $match = $allRegus->first(function ($r) use ($pClean, $rClean, $bClean) {
                $rp = strtolower(preg_replace('/[^a-z0-9]/', '', $r->pos ?? ''));
                $rn = strtolower(preg_replace('/[^a-z0-9]/', '', $r->nama ?? ''));
                $rb = strtolower(trim($r->bidang ?? ''));
                $posMatch = $rp && $pClean && (str_contains($rp, $pClean) || str_contains($pClean, $rp));
                $reguMatch = $rn === $rClean;
                $bidangMatch = !empty($bClean) && !empty($rb) && (str_contains($bClean, $rb) || str_contains($rb, $bClean));
                return $posMatch && $reguMatch && $bidangMatch;
            });

            if (!$match) {
                $match = $allRegus->first(function ($r) use ($pClean, $rClean) {
                    $rp = strtolower(preg_replace('/[^a-z0-9]/', '', $r->pos ?? ''));
                    $rn = strtolower(preg_replace('/[^a-z0-9]/', '', $r->nama ?? ''));
                    return $rp && $pClean && (str_contains($rp, $pClean) || str_contains($pClean, $rp)) && $rn === $rClean;
                });
            }

            if ($match) {
                $validated['regu_id'] = $match->id;
                $validated['regu']    = $match->nama;
            }
        }
    }
}