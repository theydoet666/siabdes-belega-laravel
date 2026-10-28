<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Services\CheckinService;
use App\Services\PencarianUndanganService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
            ->select('id', 'rapat_id', 'nama', 'jabatan', 'instansi', 'sumber', 'urutan')
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

        $urlRegistrasi = url('/r/'.$rapat->kode);
        $qrSvg = QrCode::size(240)->margin(1)->generate($urlRegistrasi);

        return view('kiosk.index', [
            'rapat' => $rapat,
            'undanganAwal' => $undanganAwal,
            'urlRegistrasi' => $urlRegistrasi,
            'qrSvg' => $qrSvg,
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
