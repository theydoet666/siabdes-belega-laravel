<?php

namespace App\Http\Middleware;

use App\Enums\PeranPengguna;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $profil = $user->profil;

        if (! $profil || ! $profil->aktif) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda tidak aktif atau tidak memiliki profil yang valid. Hubungi administrator.',
            ]);
        }

        // Jika tidak ada batasan peran spesifik, izinkan yang penting punya profil aktif
        if (empty($roles)) {
            return $next($request);
        }

        // Peran pengguna saat ini
        $userRole = $profil->peran instanceof PeranPengguna ? $profil->peran->value : (string) $profil->peran;

        // Admin selalu memiliki akses ke peran operator
        if ($userRole === PeranPengguna::ADMIN->value) {
            return $next($request);
        }

        // Cek apakah peran user cocok dengan salah satu peran yang diminta
        foreach ($roles as $role) {
            // Bisa berupa string yang dipisah koma jika diteruskan seperti role:admin,operator
            $allowedRoles = explode(',', $role);
            if (in_array($userRole, $allowedRoles, true)) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
    }
}
