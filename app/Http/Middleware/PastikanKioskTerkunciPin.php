<?php

namespace App\Http\Middleware;

use App\Models\Rapat;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PastikanKioskTerkunciPin
{
    /**
     * Handle an incoming request for kiosk mode route /kiosk/{kode}.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $kode = $request->route('kode');
        $rapat = Rapat::where('kode', $kode)->first();

        if (! $rapat) {
            abort(404, "Rapat dengan kode '{$kode}' tidak ditemukan.");
        }

        $sessionKey = 'kiosk_unlocked_'.$rapat->id;

        // Jika rapat tidak memiliki PIN, langsung izinkan
        if (empty($rapat->pin_kiosk)) {
            $request->session()->put($sessionKey, true);
            if (! $request->session()->has('kiosk_token_'.$rapat->id)) {
                $request->session()->put('kiosk_token_'.$rapat->id, (string) Str::uuid());
            }

            return $next($request);
        }

        // Cek jika sudah di-unlock dalam sesi browser tablet ini
        if ($request->session()->get($sessionKey) === true) {
            return $next($request);
        }

        $throttleKey = 'kiosk-pin|'.$request->ip().'|'.$rapat->id;

        // Cek jika melebihi batas percobaan PIN (maksimal 5 percobaan gagal)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $detik = RateLimiter::availableIn($throttleKey);

            return response()->view('kiosk.kunci_pin', [
                'rapat' => $rapat,
                'pesanError' => "Terlalu banyak percobaan memasukkan PIN. Silakan coba lagi dalam {$detik} detik.",
            ], 429);
        }

        // Cek jika operator mengirim form verifikasi PIN
        if ($request->isMethod('POST') && $request->has('pin_kiosk_input')) {
            $inputPin = trim((string) $request->input('pin_kiosk_input'));

            if (Hash::check($inputPin, $rapat->pin_kiosk)) {
                RateLimiter::clear($throttleKey);
                $request->session()->put($sessionKey, true);
                $token = $request->session()->get('kiosk_token_'.$rapat->id) ?: (string) Str::uuid();
                $request->session()->put('kiosk_token_'.$rapat->id, $token);
                Cache::put('kiosk_token_'.$rapat->id, $token, now()->addDay());

                return redirect()->route('kiosk.index', ['kode' => $rapat->kode]);
            }

            // Catat percobaan gagal ke RateLimiter (blokir selama 60 detik setelah 5 kegagalan)
            RateLimiter::hit($throttleKey, 60);

            return response()->view('kiosk.kunci_pin', [
                'rapat' => $rapat,
                'pesanError' => 'PIN Kiosk yang Anda masukkan salah. Silakan coba lagi.',
            ]);
        }

        // Jika sudah terkunci dan mengakses rute kunci, langsung arahkan ke form PIN
        if ($request->routeIs('kiosk.kunci')) {
            return redirect()->route('kiosk.index', ['kode' => $rapat->kode]);
        }

        // Endpoint data / AJAX harus ditolak dengan 403 bila belum dibuka PIN
        if ($request->expectsJson() || $request->routeIs('kiosk.data')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Meja Kiosk terkunci PIN. Silakan buka kunci PIN terlebih dahulu.',
            ], 403);
        }

        // Render layar kunci PIN
        return response()->view('kiosk.kunci_pin', [
            'rapat' => $rapat,
            'pesanError' => null,
        ]);
    }
}
