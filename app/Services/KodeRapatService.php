<?php

namespace App\Services;

use App\Models\Rapat;

class KodeRapatService
{
    /**
     * Character set 32 karakter bebas ambigu:
     * Alfanumerik uppercase tanpa karakter I, O, 0, 1.
     */
    public const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Menghasilkan kode rapat unik 8 karakter.
     *
     * @throws \RuntimeException jika gagal menghasilkan kode unik setelah batas percobaan
     */
    public function generate(int $panjang = 8, int $maxPercobaan = 50): string
    {
        $charsetLen = strlen(self::CHARSET);

        for ($percobaan = 0; $percobaan < $maxPercobaan; $percobaan++) {
            $kode = '';
            for ($i = 0; $i < $panjang; $i++) {
                $randomIndex = random_int(0, $charsetLen - 1);
                $kode .= self::CHARSET[$randomIndex];
            }

            // Pastikan belum digunakan di tabel rapat
            if (! Rapat::where('kode', $kode)->exists()) {
                return $kode;
            }
        }

        // Fallback jika terjadi kolisi berulang (32^8 = 1.099.511.627.776 kombinasi)
        throw new \RuntimeException('Gagal menghasilkan kode rapat unik. Silakan coba lagi.');
    }

    /**
     * Memvalidasi format kode rapat (4-8 karakter dalam set yang diizinkan untuk kompatibilitas data lama).
     */
    public function isValid(string $kode): bool
    {
        return (bool) preg_match('/^[A-HJ-NP-Z2-9]{4,8}$/', strtoupper(trim($kode)));
    }
}
