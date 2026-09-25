<?php

namespace App\Http\Controllers;

use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KioskController extends Controller
{
    /**
     * Menampilkan layar utama kiosk tablet absensi peserta rapat.
     *
     * @param string $kode
     * @return View
     */
    public function index(string $kode): View
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $undanganAwal = Undangan::where('rapat_id', $rapat->id)
            ->orderBy('nama', 'asc')
            ->get();

        return view('kiosk.index', [
            'rapat' => $rapat,
            'undanganAwal' => $undanganAwal,
        ]);
    }

    /**
     * Mengunci kembali meja kiosk ke mode PIN.
     *
     * @param Request $request
     * @param string $kode
     * @return \Illuminate\Http\RedirectResponse
     */
    public function kunci(Request $request, string $kode)
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $request->session()->forget('kiosk_unlocked_' . $rapat->id);

        return redirect()->route('kiosk.index', ['kode' => $rapat->kode]);
    }
}
