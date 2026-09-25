<?php

namespace App\Http\Controllers;

use App\Models\Rapat;
use App\Models\Undangan;
use App\Services\CheckinService;
use App\Services\PencarianUndanganService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrasiMandiriController extends Controller
{
    public function __construct(
        protected PencarianUndanganService $pencarianService,
        protected CheckinService $checkinService
    ) {}

    /**
     * Menampilkan antarmuka registrasi mandiri via ponsel pintar peserta (/r/{kode}).
     */
    public function index(string $kode): View
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $undanganAwal = Undangan::where('rapat_id', $rapat->id)
            ->select(['id', 'rapat_id', 'nama', 'jabatan', 'instansi', 'sumber'])
            ->orderBy('nama', 'asc')
            ->get();

        return view('kiosk.index', [
            'rapat' => $rapat,
            'undanganAwal' => $undanganAwal,
        ]);
    }

    /**
     * Endpoint pencarian real-time via AJAX/fetch untuk ponsel peserta.
     */
    public function cari(Request $request, string $kode): JsonResponse
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $kueri = (string) $request->input('q', '');

        $hasil = $this->pencarianService->cari($rapat->id, $kueri);

        return response()->json($hasil);
    }
}
