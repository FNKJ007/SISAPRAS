<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $searchQuery = $request->query('search', '');

        $query = User::orderBy('name', 'asc');

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

        $allUsers = User::all();

        $kpi = [
            'total_pegawai' => $allUsers->count(),
            'pejabat'       => $allUsers->filter(fn($u) => preg_match('/(kepala|kabid|kasi|sekretaris|kadis)/i', (string) $u->jabatan))->count(),
            'danru'         => $allUsers->filter(fn($u) => preg_match('/(danru|komandan)/i', (string) $u->jabatan))->count(),
            'petugas'       => $allUsers->filter(fn($u) => !preg_match('/(kepala|kabid|kasi|sekretaris|kadis|danru|komandan)/i', (string) $u->jabatan))->count(),
        ];

        return view('admin.pemeliharaan.data-pegawai.index', compact('pegawaiList', 'kpi', 'searchQuery'));
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
        ], $messages);

        // Buat username / email unik dari nama / NIP
        $cleanNip = preg_replace('/[^0-9]/', '', $validated['nip']);
        $uniqueEmail = !empty($cleanNip) ? "{$cleanNip}@disdamkar.go.id" : 'pegawai_' . Str::random(8) . '@disdamkar.go.id';

        // Cek jika email sudah ada
        if (User::where('email', $uniqueEmail)->exists()) {
            $uniqueEmail = 'pegawai_' . time() . '_' . Str::random(4) . '@disdamkar.go.id';
        }

        User::create([
            'name'     => $validated['name'],
            'nip'      => $validated['nip'],
            'jabatan'  => $validated['jabatan'],
            'bidang'   => $validated['bidang'] ?? 'Sarana Prasarana Dan Informasi',
            'pos'      => $validated['pos'] ?? 'Soreang (MAKO)',
            'regu'     => $validated['regu'] ?? null,
            'email'    => $uniqueEmail,
            'password' => Hash::make('password123'),
            'role'     => 'user',
            'status'   => 'aktif',
        ]);

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
        ], $messages);

        $user->update($validated);

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

        return redirect()
            ->route('admin.pemeliharaan.data-pegawai')
            ->with('success', "Data pegawai '{$name}' berhasil dihapus.");
    }
}