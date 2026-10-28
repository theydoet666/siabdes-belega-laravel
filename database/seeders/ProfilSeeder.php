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
        $passwordAwal = env('ADMIN_INITIAL_PASSWORD');
        $isRandom = false;

        if (empty($passwordAwal)) {
            $passwordAwal = Str::password(16, true, true, false);
            $isRandom = true;
        }

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@belega.desa.id'],
            [
                'name' => 'Administrator Desa Belega',
                'password' => Hash::make($passwordAwal),
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

        if ($this->command) {
            if ($isRandom) {
                $this->command->warn('================================================================');
                $this->command->warn('PERINGATAN KEAMANAN: Password awal Administrator di-generate acak:');
                $this->command->warn('Password: '.$passwordAwal);
                $this->command->warn('Wajib catat dan segera ganti password ini saat login pertama!');
                $this->command->warn('================================================================');
            } else {
                $this->command->info('Administrator diinisialisasi menggunakan password dari ADMIN_INITIAL_PASSWORD.');
            }
        }
    }
}
