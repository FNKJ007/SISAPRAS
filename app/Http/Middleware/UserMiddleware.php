<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     * Hanya mengizinkan user non-admin (role = 'user') mengakses halaman ini.
     * Admin yang mencoba mengakses akan diarahkan ke halaman admin dashboard,
     * KECUALI admin yang sedang dalam mode "Lihat Sebagai User" via session flag.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Izinkan admin yang sedang dalam mode "Lihat Sebagai User"
        if (Auth::user()->isAdmin() && $request->session()->get('admin_viewing_as_user')) {
            return $next($request);
        }

        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Admin tidak dapat mengakses halaman user.');
        }

        return $next($request);
    }
}
