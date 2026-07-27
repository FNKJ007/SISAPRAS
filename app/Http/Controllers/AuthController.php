<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman form login
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Pastikan nama file view adalah login.blade.php
    }

    /**
     * Memproses data login
     */
    public function login(Request $request)
    {
        // 1. RATE LIMITING (Mencegah Serangan Brute Force)
        // Membatasi percobaan login berdasarkan Username dan IP Address (Maksimal 5x percobaan)
        $throttleKey = Str::transliterate(Str::lower($request->input('username')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.');
        }

        // 2. VALIDASI INPUT (Mencegah XSS & Memastikan Integritas Data)
        // Memastikan input berupa string murni dan membuang karakter tag HTML (strip_tags)
        $request->merge([
            'username' => strip_tags($request->input('username')),
        ]);

        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // 3. PROSES AUTENTIKASI (Aman dari SQL Injection)
        // Laravel Auth::attempt secara otomatis menggunakan PDO Parameter Binding
        if (Auth::attempt($credentials)) {
            
            // Hapus log percobaan gagal jika login berhasil
            RateLimiter::clear($throttleKey);

            // 4. MENCEGAH SESSION FIXATION
            // Wajib memperbarui ID Sesi setelah user berhasil login
            $request->session()->regenerate();

            // Redirect ke halaman dashboard (ubah 'dashboard' sesuai nama route tujuanmu)
            return redirect()->intended('dashboard');
        }

        // Catat percobaan login yang gagal untuk Rate Limiting
        RateLimiter::hit($throttleKey);

        // 5. MENCEGAH USER ENUMERATION
        // Jangan beri tahu penyerang apakah 'Username' atau 'Password' yang salah.
        // Gunakan pesan error umum.
        return back()->withErrors([
            'username' => 'Username atau Password yang Anda masukkan tidak cocok.',
        ])->onlyInput('username'); // Mengembalikan username ke form agar tidak perlu diketik ulang
    }

    /**
     * Memproses Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Menghapus dan menghancurkan sesi lama demi keamanan
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}