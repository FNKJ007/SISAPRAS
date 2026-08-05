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
        // Jika sudah login, langsung redirect sesuai role
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    /**
     * Memproses data login dengan NIP + Password
     */
    public function login(Request $request)
    {
        // 1. RATE LIMITING (Mencegah Serangan Brute Force — maks 5x percobaan)
        $throttleKey = Str::transliterate(Str::lower($request->input('nip')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.');
        }

        // 2. VALIDASI INPUT
        $request->merge([
            'nip' => strip_tags($request->input('nip')),
        ]);

        $request->validate([
            'nip'      => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        // 3. PROSES AUTENTIKASI menggunakan NIP
        $credentials = [
            'nip'      => $request->input('nip'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {

            // Hapus log percobaan gagal
            RateLimiter::clear($throttleKey);

            // 4. MENCEGAH SESSION FIXATION
            $request->session()->regenerate();

            // 5. REDIRECT BERDASARKAN ROLE
            return $this->redirectByRole();
        }

        RateLimiter::hit($throttleKey);

        // 6. MENCEGAH USER ENUMERATION — pesan error umum
        return back()->withErrors([
            'nip' => 'NIP atau Password yang Anda masukkan tidak cocok.',
        ])->onlyInput('nip');
    }

    /**
     * Redirect ke halaman yang sesuai berdasarkan role
     */
    private function redirectByRole()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    /**
     * Memproses Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}