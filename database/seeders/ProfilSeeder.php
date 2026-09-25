<?php

namespace Database\Seeders;

use App\Enums\PeranPengguna;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@belega.desa.id'],
            [
                'name' => 'Administrator Desa Belega',
                'password' => Hash::make('admin123'),
            ]
        );

        Profil::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'id' => (string) Str::uuid(),
                'nama' => 'Administrator Desa Belega',
                'email' => 'admin@belega.desa.id',
                'peran' => PeranPengguna::ADMIN,
                'aktif' => true,
            ]
        );
    }
}
