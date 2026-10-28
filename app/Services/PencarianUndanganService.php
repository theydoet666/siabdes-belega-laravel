<?php

namespace App\Services;

use App\Models\Undangan;
use Illuminate\Support\Collection;

class PencarianUndanganService
{
    /**
     * Mencari undangan rapat berdasarkan kata kunci token / fuzzy matching.
     * Tidak mengembalikan kolom hp secara publik demi privasi PDP.
     */
    public function cari(string $rapatId, string $kueri, int $limit = 8): Collection
    {
        $kueriBersih = $this->normalisasi($kueri);
        if (strlen($kueriBersih) < 2) {
            return collect();
        }

        $tokens = array_filter(explode(' ', $kueriBersih), fn ($t) => strlen($t) > 0);

        $query = Undangan::where('rapat_id', $rapatId)
            ->select(['id', 'rapat_id', 'nama', 'jabatan', 'instansi', 'sumber', 'urutan']);

        foreach ($tokens as $token) {
            $wildcard = '%'.$token.'%';
            $query->where(function ($q) use ($wildcard) {
                $q->where('nama', 'like', $wildcard)
                    ->orWhere('jabatan', 'like', $wildcard)
                    ->orWhere('instansi', 'like', $wildcard);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Normalisasi teks pencarian.
     */
    public function normalisasi(string $str): string
    {
        $bersih = preg_replace('/[^a-zA-Z0-9 ]/u', ' ', $str);
        $bersih = preg_replace('/\s+/', ' ', $bersih);

        return mb_strtolower(trim($bersih));
    }
}
