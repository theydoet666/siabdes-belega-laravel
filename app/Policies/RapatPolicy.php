<?php

namespace App\Policies;

use App\Enums\PeranPengguna;
use App\Models\Rapat;
use App\Models\User;

class RapatPolicy
{
    /**
     * Memeriksa apakah pengguna aktif di sistem.
     */
    protected function isUserAktif(User $user): bool
    {
        return $user->profil !== null && $user->profil->aktif;
    }

    /**
     * Memeriksa apakah pengguna adalah admin aktif.
     */
    protected function isAdmin(User $user): bool
    {
        return $this->isUserAktif($user) && $user->profil->peran === PeranPengguna::ADMIN;
    }

    /**
     * Memeriksa apakah pengguna adalah pembuat/pemilik rapat.
     */
    protected function isPemilik(User $user, Rapat $rapat): bool
    {
        return $this->isUserAktif($user) && $rapat->dibuat_oleh === $user->profil?->id;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->isUserAktif($user);
    }

    /**
     * Determine whether the user can view the model.
     * Dibatasi untuk admin atau pembuat rapat (dibuat_oleh).
     */
    public function view(User $user, Rapat $rapat): bool
    {
        return $this->isAdmin($user) || $this->isPemilik($user, $rapat);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isUserAktif($user);
    }

    /**
     * Determine whether the user can update the model.
     * Dibatasi untuk admin atau pembuat rapat (dibuat_oleh).
     */
    public function update(User $user, Rapat $rapat): bool
    {
        return $this->isAdmin($user) || $this->isPemilik($user, $rapat);
    }

    /**
     * Determine whether the user can duplicate the model.
     * Dibatasi untuk admin atau pembuat rapat (dibuat_oleh).
     */
    public function duplicate(User $user, Rapat $rapat): bool
    {
        return $this->isAdmin($user) || $this->isPemilik($user, $rapat);
    }

    /**
     * Determine whether the user can delete the model.
     * Khusus Administrator sesuai §4.2 Matriks Hak Akses.
     */
    public function delete(User $user, Rapat $rapat): bool
    {
        return $this->isAdmin($user);
    }
}
