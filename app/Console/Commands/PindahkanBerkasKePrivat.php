<?php

namespace App\Console\Commands;

use App\Models\Kehadiran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PindahkanBerkasKePrivat extends Command
{
    /**
     * Nama dan signature dari perintah console.
     *
     * @var string
     */
    protected $signature = 'berkas:pindahkan-ke-privat';

    /**
     * Deskripsi perintah console.
     *
     * @var string
     */
    protected $description = 'Memindahkan berkas foto dan tanda tangan kehadiran dari disk public ke disk private (local) demi kepatuhan UU PDP No. 27/2022.';

    /**
     * Eksekusi perintah console.
     */
    public function handle(): int
    {
        $this->info('Memulai pemindahan berkas kehadiran dari disk public ke disk private...');

        $kehadiranList = Kehadiran::whereNotNull('foto_path')
            ->orWhereNotNull('ttd_path')
            ->get();

        $jumlahFoto = 0;
        $jumlahTtd = 0;

        foreach ($kehadiranList as $kehadiran) {
            // Pindahkan foto jika ada di disk public
            if (! empty($kehadiran->foto_path) && Storage::disk('public')->exists($kehadiran->foto_path)) {
                $konten = Storage::disk('public')->get($kehadiran->foto_path);
                Storage::disk('local')->put($kehadiran->foto_path, $konten);
                Storage::disk('public')->delete($kehadiran->foto_path);
                $jumlahFoto++;
            }

            // Pindahkan ttd jika ada di disk public
            if (! empty($kehadiran->ttd_path) && Storage::disk('public')->exists($kehadiran->ttd_path)) {
                $konten = Storage::disk('public')->get($kehadiran->ttd_path);
                Storage::disk('local')->put($kehadiran->ttd_path, $konten);
                Storage::disk('public')->delete($kehadiran->ttd_path);
                $jumlahTtd++;
            }
        }

        $this->info("Berhasil memindahkan {$jumlahFoto} foto wajah dan {$jumlahTtd} tanda tangan ke disk privat.");

        return Command::SUCCESS;
    }
}
