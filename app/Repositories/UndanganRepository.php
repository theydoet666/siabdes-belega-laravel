<?php

namespace App\Repositories;

use App\Models\Undangan;
use Illuminate\Support\Collection;

class UndanganRepository
{
    /**
     * Mengambil kombinasi unik (nama, jabatan, instansi, hp) dari seluruh tabel undangan lintas rapat.
     * Dapat mengecualikan undangan yang sudah terdaftar pada rapat tertentu.
     */
    public function riwayatUnik(?string $cari = null, ?string $kecualiRapatId = null): Collection
    {
        $query = Undangan::select('nama', 'jabatan', 'instansi', 'hp')
            ->distinct();

        // Kecualikan nama-nama yang sudah terdaftar di rapat ini agar tidak ganda
        if (! empty($kecualiRapatId)) {
            $namaTerdaftar = Undangan::where('rapat_id', $kecualiRapatId)->pluck('nama')->toArray();
            if (! empty($namaTerdaftar)) {
                $query->whereNotIn('nama', $namaTerdaftar);
            }
        }

        if (! empty($cari)) {
            $keyword = '%'.trim($cari).'%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', $keyword)
                    ->orWhere('jabatan', 'like', $keyword)
                    ->orWhere('instansi', 'like', $keyword)
                    ->orWhere('hp', 'like', $keyword);
            });
        }

        return $query->orderBy('nama', 'asc')->get()->map(function ($item) {
            $item->kunci_unik = md5(trim((string) $item->nama).'|'.trim((string) $item->jabatan).'|'.trim((string) $item->instansi).'|'.trim((string) $item->hp));

            return $item;
        });
    }
}
