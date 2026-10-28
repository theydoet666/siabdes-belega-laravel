<?php

namespace App\Console\Commands;

use App\Services\RetensiFotoService;
use Illuminate\Console\Command;

class RetensiBersihkanFoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retensi:bersihkan-foto {--rapat= : ID rapat spesifik untuk dibersihkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan foto wajah kehadiran rapat yang telah melewati batas masa retensi privasi (UU No. 27/2022 PDP)';

    /**
     * Execute the console command.
     */
    public function handle(RetensiFotoService $retensiService): int
    {
        $this->info('Memulai pembersihan foto kehadiran retensi...');

        $spesifikRapatId = $this->option('rapat');

        if ($spesifikRapatId) {
            $this->line("Memproses rapat spesifik ID: {$spesifikRapatId}");
            try {
                $fotoDihapus = $retensiService->hapusFotoRapat($spesifikRapatId);
                $this->info("Berhasil! {$fotoDihapus} foto kehadiran dibersihkan untuk rapat tersebut.");

                return Command::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("Gagal membersihkan foto: {$e->getMessage()}");

                return Command::FAILURE;
            }
        }

        $hasil = $retensiService->bersihkanFotoKadaluarsa();

        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['Jumlah Rapat Kadaluarsa Diproses', $hasil['rapat_diproses']],
                ['Total Foto Fisik Dibersihkan', $hasil['total_foto_dihapus']],
            ]
        );

        if ($hasil['rapat_diproses'] > 0) {
            $this->info("Pembersihan otomatis retensi foto selesai. {$hasil['rapat_diproses']} rapat diproses, {$hasil['total_foto_dihapus']} foto dibersihkan.");
        } else {
            $this->line('Tidak ada rapat yang melewati batas retensi saat ini.');
        }

        return Command::SUCCESS;
    }
}
