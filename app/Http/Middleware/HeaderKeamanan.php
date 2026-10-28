<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeaderKeamanan
{
    /**
     * Menambahkan header keamanan HTTP standar pada setiap respons aplikasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=()');

        // Content Security Policy dasar yang aman namun kompatibel dengan Alpine.js, Livewire, SVG QR, canvas data-URI, dan Vite Dev Server
        if (! $response->headers->has('Content-Security-Policy')) {
            $allowedSchemes = app()->environment('local', 'testing') ? 'http: https: ws: wss:' : 'https:';

            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: {$allowedSchemes}; frame-ancestors 'self';"
            );
        }

        return $response;
    }
}
