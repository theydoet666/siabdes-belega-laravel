<?php

namespace Tests\Feature;

use App\Enums\PeranPengguna;
use App\Enums\StatusRapat;
use App\Http\Livewire\Pengaturan\PengaturanIdentitas;
use App\Models\PengaturanSistem;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class KeseragamanDesainDanLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Profil $adminProfil;

    protected User $operatorUser;

    protected Profil $operatorProfil;

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
            'logo_url' => null,
            'favicon_url' => null,
        ]);

        $this->adminUser = User::factory()->create();
        $this->adminProfil = Profil::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->adminUser->id,
            'nama' => 'Administrator Desa Belega',
            'email' => $this->adminUser->email,
            'peran' => PeranPengguna::ADMIN,
            'aktif' => true,
        ]);

        $this->operatorUser = User::factory()->create();
        $this->operatorProfil = Profil::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->operatorUser->id,
            'nama' => 'Operator Rapat Desa',
            'email' => $this->operatorUser->email,
            'peran' => PeranPengguna::OPERATOR,
            'aktif' => true,
        ]);
    }

    public function test_footer_resmi_terintegrasi_pada_seluruh_halaman_utama(): void
    {
        $this->actingAs($this->operatorUser);

        $response = $this->get(route('rapat.index'));
        $response->assertOk();
        $response->assertSee('SIABDES Belega');
        $response->assertSee('Kebijakan Privasi (UU PDP)');
        $response->assertSee(date('Y'));
    }

    public function test_pengaturan_identitas_dapat_mengunggah_logo_dan_favicon(): void
    {
        Storage::fake('public');

        $this->actingAs($this->adminUser);

        $logoFile = UploadedFile::fake()->image('logo-desa.png', 200, 200);
        $faviconFile = UploadedFile::fake()->image('favicon.png', 32, 32);

        Livewire::test(PengaturanIdentitas::class)
            ->set('nama_sistem', 'SIABDES Mandiri')
            ->set('nama_desa', 'Desa Belega Gianyar')
            ->set('logoBaru', $logoFile)
            ->set('faviconBaru', $faviconFile)
            ->call('simpan')
            ->assertSet('pesanSukses', 'Pengaturan identitas desa dan kop dinas berhasil disimpan.');

        $pengaturan = PengaturanSistem::first();
        $this->assertEquals('SIABDES Mandiri', $pengaturan->nama_sistem);
        $this->assertEquals('Desa Belega Gianyar', $pengaturan->nama_desa);
        $this->assertNotNull($pengaturan->logo_url);
        $this->assertNotNull($pengaturan->favicon_url);

        $this->assertDatabaseHas('audit_log', [
            'aktor' => $this->adminProfil->id,
            'tabel_target' => 'pengaturan_sistem',
        ]);
    }

    public function test_paginasi_kustom_livewire_merender_format_desain_desa(): void
    {
        // Buat 25 rapat untuk menguji paginasi
        for ($i = 1; $i <= 25; $i++) {
            Rapat::create([
                'kode' => 'P'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'judul' => "Agenda Rapat Pengujian Paginasi Ke-{$i}",
                'tanggal' => Carbon::now()->subDays($i),
                'tempat' => 'Balai Desa',
                'penyelenggara' => 'Pemerintah Desa',
                'status' => StatusRapat::DIBUKA,
                'dibuat_oleh' => $this->operatorProfil->id,
            ]);
        }

        $this->actingAs($this->operatorUser);

        $response = $this->get(route('rapat.index'));
        $response->assertOk();
        $response->assertSee('Menampilkan', false);
        $response->assertSee('dari', false);
        $response->assertSee('25', false);
        $response->assertSee('data', false);
    }
}
