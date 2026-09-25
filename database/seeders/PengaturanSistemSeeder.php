<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use Illuminate\Database\Seeder;

class PengaturanSistemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PengaturanSistem::updateOrCreate(
            ['id' => 1],
            [
                'nama_sistem' => 'SIABDES Belega',
                'subjudul_sistem' => 'Sistem Absensi Rapat Kantor Desa Belega',
                'nama_desa' => 'Pemerintah Desa Belega',
                'alamat_desa' => 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581',
                'kecamatan' => 'Kecamatan Blahbatuh',
                'kabupaten' => 'Kabupaten Gianyar',
                'provinsi' => 'Bali',
                'logo_url' => null,
                'favicon_url' => null,
                'diperbarui_oleh' => null,
            ]
        );
    }
}
