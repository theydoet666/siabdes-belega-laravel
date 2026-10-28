<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BerkasKehadiranController extends Controller
{
    /**
     * Menampilkan berkas foto wajah atau tanda tangan kehadiran secara terproteksi.
     */
    public function show(Request $request, string $jenis, string $id): Response
    {
        // Otorisasi: Hanya boleh diakses oleh operator/admin login ATAU melalui temporary signed URL
        $isOperator = Auth::check() && Auth::user()->profil?->aktif && in_array(Auth::user()->profil?->peran?->value ?? '', ['admin', 'operator'], true);
        $isSigned = $request->hasValidSignature();

        if (! $isOperator && ! $isSigned) {
            abort(403, 'Akses ke berkas ditolak.');
        }

        if (! in_array($jenis, ['foto', 'ttd'], true)) {
            abort(404, 'Jenis berkas tidak valid.');
        }

        $kehadiran = Kehadiran::findOrFail($id);
        $path = $jenis === 'foto' ? $kehadiran->foto_path : $kehadiran->ttd_path;

        if (! $path) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        // Cari file di disk local (privat) terlebih dahulu, lalu disk public sebagai fallback
        $disk = null;
        if (Storage::disk('local')->exists($path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = 'public';
        }

        if (! $disk) {
            abort(404, 'Berkas fisik tidak ditemukan di penyimpanan server.');
        }

        $mime = str_ends_with($path, '.png') ? 'image/png' : 'image/jpeg';

        return response(Storage::disk($disk)->get($path), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
