<?php

namespace App\Providers;

use App\Enums\PeranPengguna;
use App\Http\Livewire\Auth\HalamanLogin;
use App\Http\Livewire\Pengaturan\HalamanPengaturan;
use App\Http\Livewire\Pengaturan\KelolaOperator;
use App\Http\Livewire\Pengaturan\PengaturanIdentitas;
use App\Http\Livewire\Pengaturan\PengaturanRetensi;
use App\Http\Livewire\Pengaturan\TabelAuditLog;
use App\Http\Livewire\Rapat\DaftarRapat;
use App\Http\Livewire\Rapat\DetailRapat;
use App\Http\Livewire\Rapat\FormRapat;
use App\Http\Livewire\Rapat\ModalDuplikasiRapat;
use App\Http\Livewire\Rapat\PanelQr;
use App\Http\Livewire\Undangan\FormUndangan;
use App\Http\Livewire\Undangan\ImporCsv;
use App\Http\Livewire\Undangan\ModalPilihRiwayatUndangan;
use App\Http\Livewire\Undangan\TabelUndangan;
use App\Models\Rapat;
use App\Models\User;
use App\Policies\RapatPolicy;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id', 'ind');

        // Rate Limiter Rute Publik & Kiosk
        RateLimiter::for('publik', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('pin-kiosk', function (Request $request) {
            $kode = (string) $request->route('kode', '');

            return Limit::perMinute(5)->by($request->ip().'|'.$kode);
        });

        RateLimiter::for('api-sinkron', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

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
        Gate::policy(Rapat::class, RapatPolicy::class);

        // Register Livewire components in App\Http\Livewire namespace
        Livewire::component('auth.halaman-login', HalamanLogin::class);
        Livewire::component('pengaturan.halaman-pengaturan', HalamanPengaturan::class);
        Livewire::component('pengaturan.kelola-operator', KelolaOperator::class);
        Livewire::component('pengaturan.pengaturan-identitas', PengaturanIdentitas::class);
        Livewire::component('pengaturan.pengaturan-retensi', PengaturanRetensi::class);
        Livewire::component('pengaturan.tabel-audit-log', TabelAuditLog::class);
        Livewire::component('rapat.daftar-rapat', DaftarRapat::class);
        Livewire::component('rapat.form-rapat', FormRapat::class);
        Livewire::component('rapat.detail-rapat', DetailRapat::class);
        Livewire::component('rapat.modal-duplikasi-rapat', ModalDuplikasiRapat::class);
        Livewire::component('rapat.panel-qr', PanelQr::class);
        Livewire::component('undangan.impor-csv', ImporCsv::class);
        Livewire::component('undangan.modal-pilih-riwayat-undangan', ModalPilihRiwayatUndangan::class);
        Livewire::component('undangan.form-undangan', FormUndangan::class);
        Livewire::component('undangan.tabel-undangan', TabelUndangan::class);
    }
}
