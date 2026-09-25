<?php

namespace App\Http\Middleware;

use App\Models\Rapat;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PastikanKioskTerkunciPin
{
    /**
     * Handle an incoming request for kiosk mode route /kiosk/{kode}.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $kode = $request->route('kode');
        $rapat = Rapat::where('kode', $kode)->first();

        if (!$rapat) {
            abort(404, "Rapat dengan kode '{$kode}' tidak ditemukan.");
        }

        // Jika rapat tidak memiliki PIN, langsung izinkan
        if (empty($rapat->pin_kiosk)) {
            return $next($request);
        }

        $sessionKey = 'kiosk_unlocked_' . $rapat->id;

        // Cek jika sudah di-unlock dalam sesi browser tablet ini
        if ($request->session()->get($sessionKey) === true) {
            return $next($request);
        }

        // Cek jika operator mengirim form verifikasi PIN
        if ($request->isMethod('POST') && $request->has('pin_kiosk_input')) {
            $inputPin = trim((string) $request->input('pin_kiosk_input'));

            if (Hash::check($inputPin, $rapat->pin_kiosk)) {
                $request->session()->put($sessionKey, true);
                return redirect()->route('kiosk.index', ['kode' => $rapat->kode]);
            }

            return response()->view('kiosk.kunci_pin', [
                'rapat' => $rapat,
                'pesanError' => 'PIN Kiosk yang Anda masukkan salah. Silakan coba lagi.',
            ]);
        }

        // Render layar kunci PIN
        return response()->view('kiosk.kunci_pin', [
            'rapat' => $rapat,
            'pesanError' => null,
        ]);
    }
}
