<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Kehadiran;
use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RetensiFotoService
{
    /**
     * Hapus seluruh foto fisik kehadiran untuk rapat tertentu dari storage,
     * kosongkan kolom foto_path di database, dan set foto_dihapus_pada.
     *
     * @param  string|null  $aktorId  Profil ID aktor yang melakukan aksi (opsional jika dari cron/CLI)
     * @return int Jumlah foto yang berhasil dihapus
     */
    public function hapusFotoRapat(string $rapatId, ?string $aktorId = null): int
    {
        $rapat = Rapat::with('kehadiran')->findOrFail($rapatId);

        $fotoDihapusCount = 0;

        DB::transaction(function () use ($rapat, $aktorId, &$fotoDihapusCount) {
            foreach ($rapat->kehadiran as $kehadiran) {
                if (! empty($kehadiran->foto_path)) {
                    // Hapus file fisik dari disk lokal (dan disk publik untuk berkas lama)
                    if (Storage::disk('local')->exists($kehadiran->foto_path)) {
                        Storage::disk('local')->delete($kehadiran->foto_path);
                    }
                    if (Storage::disk('public')->exists($kehadiran->foto_path)) {
                        Storage::disk('public')->delete($kehadiran->foto_path);
                    }

                    $kehadiran->foto_path = null;
                    $kehadiran->save();
                    $fotoDihapusCount++;
                }
            }

            // Update timestamp foto_dihapus_pada pada rapat
            $rapat->foto_dihapus_pada = Carbon::now();
            $rapat->save();

            // Tentukan aktor yang melakukan aksi
            $aktor = $aktorId ?? Auth::user()?->profil?->id ?? null;

            AuditLog::create([
                'aktor' => $aktor,
                'aksi' => "Pembersihan foto kehadiran (Retensi UU PDP) untuk rapat: '{$rapat->judul}' ({$fotoDihapusCount} foto dibersihkan)",
                'tabel_target' => 'rapat',
                'baris_id' => $rapat->id,
                'rincian' => [
                    'rapat_id' => $rapat->id,
                    'kode' => $rapat->kode,
                    'judul' => $rapat->judul,
                    'tanggal_rapat' => $rapat->tanggal?->format('Y-m-d'),
                    'retensi_hari' => $rapat->retensi_hari,
                    'jumlah_foto_dihapus' => $fotoDihapusCount,
                    'dieksekusi_pada' => Carbon::now('Asia/Makassar')->toIso8601String(),
                    'metode' => $aktor ? 'manual_admin' : 'otomatis_scheduler',
                ],
            ]);
        });

        Log::info("Retensi foto selesai untuk rapat [{$rapat->kode}]: {$fotoDihapusCount} foto dihapus.");

        return $fotoDihapusCount;
    }

    /**
     * Cari seluruh rapat yang sudah melewati batas masa retensi dan belum dibersihkan fotonya,
     * lalu jalankan pembersihan untuk masing-masing rapat.
     *
     * @return array{rapat_diproses: int, total_foto_dihapus: int, daftar_rapat: array}
     */
    public function bersihkanFotoKadaluarsa(): array
    {
        $sekarang = Carbon::today();

        // Ambil rapat yang belum dibersihkan
        $semuaRapat = Rapat::whereNull('foto_dihapus_pada')->get();

        $rapatKadaluarsa = $semuaRapat->filter(function (Rapat $rapat) {
            if (! $rapat->tanggal) {
                return false;
            }
            $batasRetensi = $rapat->tanggal->copy()->addDays($rapat->retensi_hari);

            return $batasRetensi->isPast();
        });

        $totalFotoDihapus = 0;
        $daftarRapatDiproses = [];

        foreach ($rapatKadaluarsa as $rapat) {
            $fotoDihapus = $this->hapusFotoRapat($rapat->id);
            $totalFotoDihapus += $fotoDihapus;
            $daftarRapatDiproses[] = [
                'id' => $rapat->id,
                'kode' => $rapat->kode,
                'judul' => $rapat->judul,
                'foto_dihapus' => $fotoDihapus,
            ];
        }

        return [
            'rapat_diproses' => $rapatKadaluarsa->count(),
            'total_foto_dihapus' => $totalFotoDihapus,
            'daftar_rapat' => $daftarRapatDiproses,
        ];
    }
}
