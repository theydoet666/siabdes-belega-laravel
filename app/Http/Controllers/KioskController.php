<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            ->select('undangan.*')
            ->selectSub(
                Kehadiran::select('id')
                    ->whereColumn('undangan_id', 'undangan.id')
                    ->where('dibatalkan', false)
                    ->limit(1),
                'kehadiran_id'
            )
            ->withExists(['kehadiran as sudah_hadir' => function ($query) {
                $query->where('dibatalkan', false);
            }])
            ->orderBy('nama', 'asc')
            ->get();

        $urlRegistrasi = url('/r/' . $rapat->kode);
        $qrSvg = QrCode::size(240)->margin(1)->generate($urlRegistrasi);

        return view('kiosk.index', [
            'rapat' => $rapat,
            'undanganAwal' => $undanganAwal,
            'urlRegistrasi' => $urlRegistrasi,
            'qrSvg' => $qrSvg,
        ]);
    }

    /**
     * Mengambil data undangan terbaru untuk sinkronisasi/refresh di Kiosk.
     *
     * @param string $kode
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(string $kode): \Illuminate\Http\JsonResponse
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $undangan = Undangan::where('rapat_id', $rapat->id)
            ->select('undangan.*')
            ->selectSub(
                Kehadiran::select('id')
                    ->whereColumn('undangan_id', 'undangan.id')
                    ->where('dibatalkan', false)
                    ->limit(1),
                'kehadiran_id'
            )
            ->withExists(['kehadiran as sudah_hadir' => function ($query) {
                $query->where('dibatalkan', false);
            }])
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'rapat' => $rapat,
            'undangan' => $undangan,
            'total' => $undangan->count(),
            'total_hadir' => $undangan->where('sudah_hadir', true)->count(),
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
