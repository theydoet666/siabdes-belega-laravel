<?php

namespace App\Repositories;

use App\Models\Undangan;
use Illuminate\Support\Collection;

class UndanganRepository
{
    /**
     * Mengambil kombinasi unik (nama, jabatan, instansi, hp) dari seluruh tabel undangan lintas rapat.
     * Setara RPC ambil_riwayat_undangan_unik() lama.
     *
     * @param string|null $cari
     * @return Collection
     */
    public function riwayatUnik(?string $cari = null): Collection
    {
        $query = Undangan::select('nama', 'jabatan', 'instansi', 'hp')
            ->distinct();

        if (!empty($cari)) {
            $keyword = '%' . trim($cari) . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', $keyword)
                  ->orWhere('jabatan', 'like', $keyword)
                  ->orWhere('instansi', 'like', $keyword)
                  ->orWhere('hp', 'like', $keyword);
            });
        }

        return $query->orderBy('nama', 'asc')->get();
    }
}
