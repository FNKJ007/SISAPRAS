<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BidangMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Membatasi akses rute berdasarkan bidang pengguna.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$bidangs
     */
    public function handle(Request $request, Closure $next, ?string ...$bidangs): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 1. Izinkan jika mode simulasi admin aktif ("Lihat Sebagai User")
        if ($request->session()->get('admin_viewing_as_user')) {
            return $next($request);
        }

        $user = Auth::user();

        // 2. Izinkan jika bidang user kosong / null (kompatibilitas untuk user yang belum di-assign bidang)
        if (empty($user->bidang) || trim((string) $user->bidang) === '') {
            return $next($request);
        }

        $userBidang = strtolower(trim((string) $user->bidang));

        // 3. Izinkan jika bidang user mengandung 'sarana prasarana' atau 'spi' (SPI memonitor semua bidang)
        if (str_contains($userBidang, 'sarana prasarana') || str_contains($userBidang, 'spi')) {
            return $next($request);
        }

        // Jika tidak ada parameter bidang yang ditentukan pada rute, izinkan akses
        if (empty($bidangs)) {
            return $next($request);
        }

        // 4. Periksa apakah bidang user cocok dengan salah satu parameter bidang yang diizinkan
        foreach ($bidangs as $allowed) {
            if (!$allowed) {
                continue;
            }

            $allowedLower = strtolower(trim($allowed));
            $allowedWithSpace = str_replace('_', ' ', $allowedLower);
            $allowedWithUnderscore = str_replace(' ', '_', $allowedLower);

            if (
                str_contains($userBidang, $allowedLower) ||
                str_contains($userBidang, $allowedWithSpace) ||
                str_contains($userBidang, $allowedWithUnderscore) ||
                str_contains($allowedLower, $userBidang) ||
                str_contains($allowedWithSpace, $userBidang)
            ) {
                return $next($request);
            }
        }

        // 5. Tolak akses jika tidak memenuhi kriteria di atas
        return redirect()->back()->with('error', 'Anda tidak memiliki akses ke menu ini. Silakan hubungi administrator.');
    }
}
