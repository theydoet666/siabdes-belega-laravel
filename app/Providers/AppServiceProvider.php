<?php

namespace App\Providers;

use App\Enums\PeranPengguna;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Carbon\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id', 'ind');

        Gate::define('admin', function (User $user): bool {
            return $user->profil !== null
                && $user->profil->aktif
                && $user->profil->peran === PeranPengguna::ADMIN;
        });

        Gate::define('operator', function (User $user): bool {
            return $user->profil !== null
                && $user->profil->aktif
                && in_array($user->profil->peran, [PeranPengguna::ADMIN, PeranPengguna::OPERATOR], true);
        });

        // Policy Registration
        Gate::policy(\App\Models\Rapat::class, \App\Policies\RapatPolicy::class);

        // Register Livewire components in App\Http\Livewire namespace
        \Livewire\Livewire::component('auth.halaman-login', \App\Http\Livewire\Auth\HalamanLogin::class);
        \Livewire\Livewire::component('pengaturan.halaman-pengaturan', \App\Http\Livewire\Pengaturan\HalamanPengaturan::class);
        \Livewire\Livewire::component('pengaturan.kelola-operator', \App\Http\Livewire\Pengaturan\KelolaOperator::class);
        \Livewire\Livewire::component('pengaturan.pengaturan-identitas', \App\Http\Livewire\Pengaturan\PengaturanIdentitas::class);
        \Livewire\Livewire::component('pengaturan.pengaturan-retensi', \App\Http\Livewire\Pengaturan\PengaturanRetensi::class);
        \Livewire\Livewire::component('pengaturan.tabel-audit-log', \App\Http\Livewire\Pengaturan\TabelAuditLog::class);
        \Livewire\Livewire::component('rapat.daftar-rapat', \App\Http\Livewire\Rapat\DaftarRapat::class);
        \Livewire\Livewire::component('rapat.form-rapat', \App\Http\Livewire\Rapat\FormRapat::class);
        \Livewire\Livewire::component('rapat.detail-rapat', \App\Http\Livewire\Rapat\DetailRapat::class);
        \Livewire\Livewire::component('rapat.modal-duplikasi-rapat', \App\Http\Livewire\Rapat\ModalDuplikasiRapat::class);
        \Livewire\Livewire::component('rapat.panel-qr', \App\Http\Livewire\Rapat\PanelQr::class);
        \Livewire\Livewire::component('undangan.impor-csv', \App\Http\Livewire\Undangan\ImporCsv::class);
        \Livewire\Livewire::component('undangan.modal-pilih-riwayat-undangan', \App\Http\Livewire\Undangan\ModalPilihRiwayatUndangan::class);
        \Livewire\Livewire::component('undangan.form-undangan', \App\Http\Livewire\Undangan\FormUndangan::class);
        \Livewire\Livewire::component('undangan.tabel-undangan', \App\Http\Livewire\Undangan\TabelUndangan::class);
    }
}
