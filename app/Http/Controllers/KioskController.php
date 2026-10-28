<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Rapat;
use App\Models\Undangan;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KioskController extends Controller
{
    /**
     * Menampilkan layar utama kiosk tablet absensi peserta rapat.
     */
    public function index(Request $request, string $kode): View
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();

        if (! $request->session()->has('kiosk_token_'.$rapat->id)) {
            $request->session()->put('kiosk_token_'.$rapat->id, (string) Str::uuid());
        }

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
     * Mengambil data undangan terbaru untuk sinkronisasi/refresh di Kiosk.
     */
    public function data(string $kode): JsonResponse
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $undangan = Undangan::where('rapat_id', $rapat->id)
            ->select('id', 'rapat_id', 'nama', 'jabatan', 'instansi')
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
            'rapat' => [
                'id' => $rapat->id,
                'judul' => $rapat->judul,
                'tanggal' => $rapat->tanggal instanceof CarbonInterface ? $rapat->tanggal->format('Y-m-d') : (string) $rapat->tanggal,
                'status' => $rapat->status instanceof \BackedEnum ? $rapat->status->value : (string) $rapat->status,
            ],
            'undangan' => $undangan->map(fn ($u) => [
                'id' => $u->id,
                'rapat_id' => $u->rapat_id,
                'nama' => $u->nama,
                'jabatan' => $u->jabatan,
                'instansi' => $u->instansi,
                'sudah_hadir' => (bool) $u->sudah_hadir,
                'kehadiran_id' => $u->kehadiran_id,
            ]),
            'total' => $undangan->count(),
            'total_hadir' => $undangan->where('sudah_hadir', true)->count(),
        ]);
    }

    /**
     * Mengunci kembali meja kiosk ke mode PIN.
     *
     * @return RedirectResponse
     */
    public function kunci(Request $request, string $kode)
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();
        $request->session()->forget('kiosk_unlocked_'.$rapat->id);
        $request->session()->forget('kiosk_token_'.$rapat->id);

        return redirect()->route('kiosk.index', ['kode' => $rapat->kode]);
    }
}
