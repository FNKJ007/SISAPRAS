<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Simpan / Generate Akun Pengguna Baru.
     */
    public function store(Request $request)
    {
        $nip = trim((string) $request->input('nip'));
        $existingUser = !empty($nip) ? User::where('nip', $nip)->first() : null;

        $messages = [
            'nip.required'        => 'NIP pengguna wajib diisi.',
            'nip.unique'          => 'NIP ini sudah terdaftar untuk pengguna lain.',
            'name.required'       => 'Nama lengkap pengguna wajib diisi.',
            'email.email'         => 'Format alamat email tidak valid (contoh: user@gmail.com).',
            'email.unique'        => 'Alamat email ini sudah terdaftar pada akun lain.',
            'password.required'   => 'Kata sandi / Password wajib diisi.',
            'password.min'        => 'Kata sandi / Password minimal terdiri dari 6 karakter.',
            'role.required'       => 'Peran akun (Admin / User) wajib dipilih.',
            'status.required'     => 'Status akun (Aktif / Non-Aktif) wajib dipilih.',
        ];

        if ($existingUser) {
            // Pegawai sudah terdaftar di Data Pegawai -> Update akun login
            $validated = $request->validate([
                'nip'      => 'required|string|max:50',
                'name'     => 'required|string|max:255',
                'email'    => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($existingUser->id)],
                'password' => 'required|string|min:6',
                'role'     => 'required|in:admin,user',
                'jabatan'  => 'nullable|string|max:100',
                'bidang'   => 'nullable|string|max:100',
                'pos'      => 'nullable|string|max:100',
                'regu'     => 'nullable|string|max:50',
                'no_hp'    => 'nullable|string|max:30',
                'status'   => 'required|in:aktif,nonaktif',
            ], $messages);

            $validated['nip'] = trim($validated['nip']);
            if (empty($validated['email'])) {
                $validated['email'] = null;
            }
            $validated['password'] = Hash::make($validated['password']);
            $validated['has_account'] = true;

            $existingUser->update($validated);

            return redirect()
                ->route('admin.pengaturan')
                ->with('success', "Akun login untuk pegawai '{$existingUser->name}' (NIP: {$existingUser->nip}) berhasil diaktifkan / diperbarui.");
        }

        // Pegawai baru -> Buat akun dan simpan ke data pegawai
        $validated = $request->validate([
            'nip'      => 'required|string|max:50|unique:users,nip',
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,user',
            'jabatan'  => 'nullable|string|max:100',
            'bidang'   => 'nullable|string|max:100',
            'pos'      => 'nullable|string|max:100',
            'regu'     => 'nullable|string|max:50',
            'no_hp'    => 'nullable|string|max:30',
            'status'   => 'required|in:aktif,nonaktif',
        ], $messages);

        $validated['nip'] = trim($validated['nip']);

        if (empty($validated['email'])) {
            $validated['email'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['has_account'] = true;

        User::create($validated);

        return redirect()
            ->route('admin.pengaturan')
            ->with('success', "Akun pengguna baru '{$validated['name']}' (NIP: {$validated['nip']}) berhasil dibuat dan tersimpan di data pegawai.");
    }

    /**
     * Update Data Akun Pengguna.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $messages = [
            'nip.required'        => 'NIP pengguna wajib diisi.',
            'nip.unique'          => 'NIP ini sudah terdaftar untuk pengguna lain.',
            'name.required'       => 'Nama lengkap pengguna wajib diisi.',
            'email.email'         => 'Format alamat email tidak valid (contoh: user@gmail.com).',
            'email.unique'        => 'Alamat email ini sudah terdaftar pada akun lain.',
            'role.required'       => 'Peran akun (Admin / User) wajib dipilih.',
            'status.required'     => 'Status akun (Aktif / Non-Aktif) wajib dipilih.',
        ];

        $validated = $request->validate([
            'nip'      => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'name'     => 'required|string|max:255',
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'     => 'required|in:admin,user',
            'jabatan'  => 'nullable|string|max:100',
            'bidang'   => 'nullable|string|max:100',
            'pos'      => 'nullable|string|max:100',
            'regu'     => 'nullable|string|max:50',
            'no_hp'    => 'nullable|string|max:30',
            'status'   => 'required|in:aktif,nonaktif',
        ], $messages);

        $validated['nip'] = trim($validated['nip']);
        if (empty($validated['email'])) {
            $validated['email'] = null;
        }
        $validated['has_account'] = true;

        $user->update($validated);

        return redirect()
            ->route('admin.pengaturan')
            ->with('success', "Data akun '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Reset Password Akun Pengguna secara cepat.
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $messages = [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min'      => 'Password baru minimal terdiri dari 6 karakter.',
        ];

        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ], $messages);

        $user->update([
            'password'    => Hash::make($validated['new_password']),
            'has_account' => true,
        ]);

        return redirect()
            ->route('admin.pengaturan')
            ->with('success', "Password akun '{$user->name}' berhasil di-reset.");
    }

    /**
     * Toggle Status Akun (Aktif / Nonaktif).
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.pengaturan')
                ->with('error', 'Anda tidak dapat menonaktifkan akun sendiri yang sedang digunakan.');
        }

        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        $statusText = $newStatus === 'aktif' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->route('admin.pengaturan')
            ->with('success', "Akun '{$user->name}' berhasil {$statusText}.");
    }

    /**
     * Hapus Akun Pengguna.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.pengaturan')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.pengaturan')
            ->with('success', "Akun pengguna '{$name}' berhasil dihapus.");
    }

    /**
     * Hapus Opsi Riwayat (Typo/Kesalahan) dari Database.
     */
    public function removeHistoryOption(Request $request)
    {
        $request->validate([
            'type'  => 'required|string|in:jabatan,bidang,regu',
            'value' => 'required|string',
        ]);

        $type  = $request->input('type');
        $value = trim($request->input('value'));

        if ($type === 'jabatan') {
            User::where('jabatan', 'LIKE', $value)->update(['jabatan' => null]);
        } elseif ($type === 'bidang') {
            User::where('bidang', 'LIKE', $value)->update(['bidang' => null]);
        } elseif ($type === 'regu') {
            User::where('regu', 'LIKE', $value)->update(['regu' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => "Opsi riwayat '{$value}' berhasil dihapus dari sistem."
        ]);
    }
}
