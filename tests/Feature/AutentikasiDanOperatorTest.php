<?php

namespace Tests\Feature;

use App\Enums\PeranPengguna;
use App\Http\Livewire\Auth\HalamanLogin;
use App\Http\Livewire\Pengaturan\KelolaOperator;
use App\Models\AuditLog;
use App\Models\Profil;
use App\Models\User;
use App\Services\OperatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AutentikasiDanOperatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_halaman_login_dapat_diakses_oleh_tamu(): void
    {
        $response = $this->get('/masuk');
        $response->assertStatus(200);
        $response->assertSee('SIABDES Belega');
        $response->assertSee('Masuk ke Sistem');
    }

    public function test_login_berhasil_dengan_kredensial_admin_seed(): void
    {
        Livewire::test(HalamanLogin::class)
            ->set('email', 'admin@belega.desa.id')
            ->set('password', 'admin123')
            ->call('masuk')
            ->assertRedirect(route('rapat.index'));

        $this->assertAuthenticated();
    }

    public function test_login_gagal_jika_kredensial_salah(): void
    {
        Livewire::test(HalamanLogin::class)
            ->set('email', 'admin@belega.desa.id')
            ->set('password', 'passwordsalah')
            ->call('masuk')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_gagal_jika_akun_nonaktif(): void
    {
        $operatorService = app(OperatorService::class);
        $profil = $operatorService->tambahOperator(
            email: 'nonaktif@belega.desa.id',
            password: 'password123',
            nama: 'Staf Nonaktif',
            peran: PeranPengguna::OPERATOR
        );

        // Nonaktifkan
        $operatorService->toggleStatus($profil);

        Livewire::test(HalamanLogin::class)
            ->set('email', 'nonaktif@belega.desa.id')
            ->set('password', 'password123')
            ->call('masuk')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_operator_service_membuat_user_dan_profil_serta_audit_log_secara_atomik(): void
    {
        $operatorService = app(OperatorService::class);
        $adminProfil = Profil::where('email', 'admin@belega.desa.id')->first();

        $profilBaru = $operatorService->tambahOperator(
            email: 'operator.baru@belega.desa.id',
            password: 'passwordRahasia',
            nama: 'I Made Operator',
            peran: PeranPengguna::OPERATOR,
            aktor: $adminProfil
        );

        $this->assertDatabaseHas('users', [
            'email' => 'operator.baru@belega.desa.id',
            'name' => 'I Made Operator',
        ]);

        $this->assertDatabaseHas('profil', [
            'id' => $profilBaru->id,
            'email' => 'operator.baru@belega.desa.id',
            'nama' => 'I Made Operator',
            'peran' => 'operator',
            'aktif' => 1,
        ]);

        $this->assertDatabaseHas('audit_log', [
            'aktor' => $adminProfil->id,
            'tabel_target' => 'profil',
            'baris_id' => $profilBaru->id,
        ]);
    }

    public function test_operator_service_ubah_peran_dan_toggle_status_dengan_audit_log(): void
    {
        $operatorService = app(OperatorService::class);
        $adminProfil = Profil::where('email', 'admin@belega.desa.id')->first();

        $profil = $operatorService->tambahOperator(
            email: 'staf.tes@belega.desa.id',
            password: 'password123',
            nama: 'Staf Tes',
            peran: PeranPengguna::OPERATOR,
            aktor: $adminProfil
        );

        // Ubah peran ke admin
        $operatorService->ubahPeran($profil, PeranPengguna::ADMIN, $adminProfil);
        $this->assertEquals(PeranPengguna::ADMIN, $profil->fresh()->peran);

        // Toggle status ke nonaktif
        $operatorService->toggleStatus($profil, $adminProfil);
        $this->assertFalse($profil->fresh()->aktif);

        // Cek audit logs tercatat
        $this->assertEquals(3, AuditLog::where('baris_id', $profil->id)->count());
    }

    public function test_kelola_operator_livewire_dapat_menambah_operator_dan_ubah_peran(): void
    {
        $adminUser = User::where('email', 'admin@belega.desa.id')->first();

        $this->actingAs($adminUser);

        Livewire::test(KelolaOperator::class)
            ->call('bukaModal')
            ->set('nama', 'Ni Wayan Sari')
            ->set('email', 'sari@belega.desa.id')
            ->set('password', 'sari12345')
            ->set('peran', 'operator')
            ->call('simpanOperator')
            ->assertHasNoErrors()
            ->assertSet('bukaModalTambah', false)
            ->assertSee('Ni Wayan Sari');

        $profilSari = Profil::where('email', 'sari@belega.desa.id')->first();
        $this->assertNotNull($profilSari);

        // Ubah peran via Livewire
        Livewire::test(KelolaOperator::class)
            ->call('gantiPeran', $profilSari->id)
            ->assertSee('berhasil diubah');

        $this->assertEquals(PeranPengguna::ADMIN, $profilSari->fresh()->peran);
    }

    public function test_admin_tidak_bisa_mengubah_peran_atau_menonaktifkan_akun_sendiri(): void
    {
        $adminUser = User::where('email', 'admin@belega.desa.id')->first();
        $this->actingAs($adminUser);

        $adminProfil = $adminUser->profil;

        Livewire::test(KelolaOperator::class)
            ->call('gantiPeran', $adminProfil->id)
            ->assertSet('pesanError', 'Anda tidak dapat mengubah peran akun Anda sendiri.');

        Livewire::test(KelolaOperator::class)
            ->call('toggleStatus', $adminProfil->id)
            ->assertSet('pesanError', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

        $this->assertEquals(PeranPengguna::ADMIN, $adminProfil->fresh()->peran);
        $this->assertTrue($adminProfil->fresh()->aktif);
    }

    public function test_akses_role_middleware_admin_dan_operator(): void
    {
        $operatorService = app(OperatorService::class);
        $operatorProfil = $operatorService->tambahOperator(
            email: 'operator.biasa@belega.desa.id',
            password: 'password123',
            nama: 'Operator Biasa',
            peran: PeranPengguna::OPERATOR
        );

        $operatorUser = $operatorProfil->user;
        $adminUser = User::where('email', 'admin@belega.desa.id')->first();

        // Operator biasa dapat akses /rapat
        $this->actingAs($operatorUser)->get('/rapat')->assertStatus(200);

        // Operator biasa ditolak akses /pengaturan (403 Forbidden)
        $this->actingAs($operatorUser)->get('/pengaturan')->assertStatus(403);

        // Admin dapat akses /rapat dan /pengaturan
        $this->actingAs($adminUser)->get('/rapat')->assertStatus(200);
        $this->actingAs($adminUser)->get('/pengaturan')->assertStatus(200);
    }
}
