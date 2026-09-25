<?php

namespace App\Services;

use App\Enums\PeranPengguna;
use App\Models\AuditLog;
use App\Models\Profil;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OperatorService
{
    /**
     * Menambahkan operator baru ke tabel users dan profil secara atomik.
     *
     * @param string $email
     * @param string $password
     * @param string $nama
     * @param PeranPengguna $peran
     * @param Profil|null $aktor
     * @return Profil
     */
    public function tambahOperator(
        string $email,
        string $password,
        string $nama,
        PeranPengguna $peran,
        ?Profil $aktor = null
    ): Profil {
        return DB::transaction(function () use ($email, $password, $nama, $peran, $aktor) {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $profil = Profil::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'nama' => $nama,
                'email' => $email,
                'peran' => $peran,
                'aktif' => true,
            ]);

            // Catat ke audit log
            AuditLog::create([
                'aktor' => $aktor?->id ?? auth()->user()?->profil?->id,
                'aksi' => "Menambahkan operator baru: {$nama} ({$email})",
                'tabel_target' => 'profil',
                'baris_id' => $profil->id,
                'rincian' => [
                    'nama' => $nama,
                    'email' => $email,
                    'peran' => $peran->value,
                    'aktif' => true,
                ],
            ]);

            return $profil;
        });
    }

    /**
     * Mengubah peran operator dan mencatat ke audit log.
     *
     * @param Profil $profil
     * @param PeranPengguna $peranBaru
     * @param Profil|null $aktor
     * @return bool
     */
    public function ubahPeran(Profil $profil, PeranPengguna $peranBaru, ?Profil $aktor = null): bool
    {
        if ($profil->peran === $peranBaru) {
            return false;
        }

        $peranLama = $profil->peran;

        return DB::transaction(function () use ($profil, $peranBaru, $peranLama, $aktor) {
            $profil->peran = $peranBaru;
            $profil->save();

            AuditLog::create([
                'aktor' => $aktor?->id ?? auth()->user()?->profil?->id,
                'aksi' => "Mengubah peran {$profil->nama} dari {$peranLama->value} menjadi {$peranBaru->value}",
                'tabel_target' => 'profil',
                'baris_id' => $profil->id,
                'rincian' => [
                    'peran_lama' => $peranLama->value,
                    'peran_baru' => $peranBaru->value,
                ],
            ]);

            return true;
        });
    }

    /**
     * Mengubah status aktif/nonaktif akun operator dan mencatat ke audit log.
     *
     * @param Profil $profil
     * @param Profil|null $aktor
     * @return bool
     */
    public function toggleStatus(Profil $profil, ?Profil $aktor = null): bool
    {
        return DB::transaction(function () use ($profil, $aktor) {
            $statusLama = $profil->aktif;
            $statusBatu = !$statusLama;
            $profil->aktif = $statusBatu;
            $profil->save();

            $labelAksi = $statusBatu ? 'Mengaktifkan' : 'Menonaktifkan';

            AuditLog::create([
                'aktor' => $aktor?->id ?? auth()->user()?->profil?->id,
                'aksi' => "{$labelAksi} akun operator: {$profil->nama}",
                'tabel_target' => 'profil',
                'baris_id' => $profil->id,
                'rincian' => [
                    'aktif_lama' => $statusLama,
                    'aktif_baru' => $statusBatu,
                ],
            ]);

            return true;
        });
    }
}
