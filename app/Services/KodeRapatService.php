<?php

namespace App\Services;

use App\Models\Rapat;
use Illuminate\Support\Str;

class KodeRapatService
{
    /**
     * Character set 32 karakter bebas ambigu:
     * Alfanumerik uppercase tanpa karakter I, O, 0, 1.
     */
    public const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Menghasilkan kode rapat unik 4 karakter.
     *
     * @param int $panjang
     * @param int $maxPercobaan
     * @return string
     * @throws \RuntimeException jika gagal menghasilkan kode unik setelah batas percobaan
     */
    public function generate(int $panjang = 4, int $maxPercobaan = 50): string
    {
        $charsetLen = strlen(self::CHARSET);

        for ($percobaan = 0; $percobaan < $maxPercobaan; $percobaan++) {
            $kode = '';
            for ($i = 0; $i < $panjang; $i++) {
                $randomIndex = random_int(0, $charsetLen - 1);
                $kode .= self::CHARSET[$randomIndex];
            }

            // Pastikan belum digunakan di tabel rapat
            if (!Rapat::where('kode', $kode)->exists()) {
                return $kode;
            }
        }

        // Fallback jika terjadi kolisi berulang (sangat kecil kemungkinannya: 32^4 = 1.048.576 kombinasi)
        throw new \RuntimeException('Gagal menghasilkan kode rapat unik. Silakan coba lagi.');
    }

    /**
     * Memvalidasi format kode rapat (4 karakter dalam set yang diizinkan).
     *
     * @param string $kode
     * @return bool
     */
    public function isValid(string $kode): bool
    {
        return (bool) preg_match('/^[A-HJ-NP-Z2-9]{4}$/', strtoupper(trim($kode)));
    }
}
