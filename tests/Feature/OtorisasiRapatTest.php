<?php

namespace Tests\Feature;

use App\Enums\PeranPengguna;
use App\Enums\StatusRapat;
use App\Http\Livewire\Rapat\DaftarRapat;
use App\Http\Livewire\Rapat\DetailRapat;
use App\Http\Livewire\Undangan\TabelUndangan;
use App\Models\PengaturanSistem;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class OtorisasiRapatTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Profil $adminProfil;

    protected User $operator1User;

    protected Profil $operator1Profil;

    protected User $operator2User;

    protected Profil $operator2Profil;

    protected Rapat $rapatOperator1;

    protected Undangan $undanganRapat1;

    protected function setUp(): void
    {
        parent::setUp();

        PengaturanSistem::create([
            'id' => 1,
            'nama_sistem' => 'SIABDES Belega',
            'subjudul_sistem' => 'Sistem Absensi Rapat Kantor Desa Belega',
            'nama_desa' => 'Pemerintah Desa Belega',
            'alamat_desa' => 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581',
            'kecamatan' => 'Kecamatan Blahbatuh',
            'kabupaten' => 'Kabupaten Gianyar',
            'provinsi' => 'Bali',
        ]);

        // Buat Administrator
        $this->adminUser = User::factory()->create(['email' => 'admin@belega.desa.id']);
        $this->adminProfil = Profil::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->adminUser->id,
            'nama' => 'Admin Desa',
            'email' => $this->adminUser->email,
            'peran' => PeranPengguna::ADMIN,
            'aktif' => true,
        ]);

        // Buat Operator 1
        $this->operator1User = User::factory()->create(['email' => 'op1@belega.desa.id']);
        $this->operator1Profil = Profil::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->operator1User->id,
            'nama' => 'Operator Kasi Kesra',
            'email' => $this->operator1User->email,
            'peran' => PeranPengguna::OPERATOR,
            'aktif' => true,
        ]);

        // Buat Operator 2
        $this->operator2User = User::factory()->create(['email' => 'op2@belega.desa.id']);
        $this->operator2Profil = Profil::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->operator2User->id,
            'nama' => 'Operator Kaur Keuangan',
            'email' => $this->operator2User->email,
            'peran' => PeranPengguna::OPERATOR,
            'aktif' => true,
        ]);

        // Rapat dibuat oleh Operator 1
        $this->rapatOperator1 = Rapat::create([
            'id' => (string) Str::uuid(),
            'kode' => 'KSR88888',
            'judul' => 'Rapat Koordinasi Kesejahteraan Rakyat',
            'tanggal' => now()->addDays(2),
            'tempat' => 'Ruang Rapat Kantor Perbekel',
            'penyelenggara' => 'Seksi Kesra',
            'status' => StatusRapat::DIBUKA,
            'dibuat_oleh' => $this->operator1Profil->id,
        ]);

        $this->undanganRapat1 = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapatOperator1->id,
            'nama' => 'I Ketut Peserta',
            'jabatan' => 'Tokoh Masyarakat',
            'instansi' => 'Desa Belega',
        ]);
    }

    public function test_pembuat_rapat_dan_admin_dapat_mengakses_dan_mengubah_rapat(): void
    {
        // 1. Pembuat rapat (Operator 1) dapat mengakses detail rapat
        $this->actingAs($this->operator1User);
        $this->get('/rapat/'.$this->rapatOperator1->id)->assertOk();

        Livewire::test(DetailRapat::class, ['id' => $this->rapatOperator1->id])
            ->call('ubahStatus', 'ditutup')
            ->assertHasNoErrors();

        $this->assertEquals(StatusRapat::DITUTUP, $this->rapatOperator1->fresh()->status);

        // 2. Admin dapat mengakses dan mengelola rapat milik operator manapun
        $this->actingAs($this->adminUser);
        $this->get('/rapat/'.$this->rapatOperator1->id)->assertOk();

        Livewire::test(DetailRapat::class, ['id' => $this->rapatOperator1->id])
            ->call('ubahStatus', 'draft')
            ->assertHasNoErrors();

        $this->assertEquals(StatusRapat::DRAFT, $this->rapatOperator1->fresh()->status);
    }

    public function test_operator_lain_tidak_bisa_melihat_atau_mengubah_rapat_bukan_miliknya(): void
    {
        // Operator 2 mencoba mengakses rapat milik Operator 1
        $this->actingAs($this->operator2User);

        // Akses route halaman detail harus 403
        $this->get('/rapat/'.$this->rapatOperator1->id)->assertStatus(403);

        // Akses cetak lembar A4 harus 403
        $this->get('/rapat/'.$this->rapatOperator1->id.'/cetak')->assertStatus(403);

        // Akses ekspor excel harus 403
        $this->get('/rapat/'.$this->rapatOperator1->id.'/ekspor-excel')->assertStatus(403);

        // Akses Livewire DetailRapat harus 403
        Livewire::test(DetailRapat::class, ['id' => $this->rapatOperator1->id])
            ->assertStatus(403);

        // Akses Livewire TabelUndangan harus 403
        Livewire::test(TabelUndangan::class, ['rapatId' => $this->rapatOperator1->id])
            ->assertStatus(403);

        // Di daftar rapat Operator 2, Rapat milik Operator 1 tidak tampil
        Livewire::test(DaftarRapat::class)
            ->assertDontSee('Rapat Koordinasi Kesejahteraan Rakyat');
    }
}
