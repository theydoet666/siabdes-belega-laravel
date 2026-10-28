<?php

namespace Tests\Feature;

use App\Enums\JalurCheckin;
use App\Enums\PeranPengguna;
use App\Enums\StatusRapat;
use App\Enums\SumberUndangan;
use App\Http\Livewire\Pengaturan\PengaturanIdentitas;
use App\Http\Livewire\Pengaturan\PengaturanRetensi;
use App\Http\Livewire\Pengaturan\TabelAuditLog;
use App\Models\AuditLog;
use App\Models\Kehadiran;
use App\Models\PengaturanSistem;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use App\Services\RetensiFotoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class KepatuhanPrivasiDanAuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Profil $adminProfil;

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

        $this->adminUser = User::factory()->create();
        $this->adminProfil = Profil::create([
            'id' => (string) Str::uuid(),
            'user_id' => $this->adminUser->id,
            'nama' => 'Administrator Desa Belega',
            'email' => $this->adminUser->email,
            'peran' => PeranPengguna::ADMIN,
            'aktif' => true,
        ]);
    }

    public function test_retensi_foto_service_menghapus_foto_fisik_dan_mengosongkan_database(): void
    {
        Storage::fake('public');

        $rapat = Rapat::create([
            'kode' => 'RTN1',
            'judul' => 'Rapat Uji Retensi Privasi',
            'tanggal' => Carbon::now()->subDays(100),
            'tempat' => 'Ruang Rapat Desa',
            'penyelenggara' => 'Pemerintah Desa Belega',
            'status' => StatusRapat::DITUTUP,
            'retensi_hari' => 90,
            'dibuat_oleh' => $this->adminProfil->id,
        ]);

        $undangan = Undangan::create([
            'rapat_id' => $rapat->id,
            'nama' => 'I Wayan Peserta',
            'sumber' => SumberUndangan::IMPORT,
        ]);

        $fotoPath = 'foto/foto-uji-1.jpg';
        Storage::disk('public')->put($fotoPath, 'fake image data');

        $kehadiran = Kehadiran::create([
            'rapat_id' => $rapat->id,
            'undangan_id' => $undangan->id,
            'ttd_path' => 'ttd/sample.png',
            'foto_path' => $fotoPath,
            'jalur' => JalurCheckin::KIOSK,
            'dibatalkan' => false,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($fotoPath));

        $service = app(RetensiFotoService::class);
        $fotoDihapus = $service->hapusFotoRapat($rapat->id, $this->adminProfil->id);

        $this->assertEquals(1, $fotoDihapus);
        $this->assertFalse(Storage::disk('public')->exists($fotoPath));

        $kehadiran->refresh();
        $this->assertNull($kehadiran->foto_path);

        $rapat->refresh();
        $this->assertNotNull($rapat->foto_dihapus_pada);

        $this->assertDatabaseHas('audit_log', [
            'aktor' => $this->adminProfil->id,
            'tabel_target' => 'rapat',
            'baris_id' => $rapat->id,
        ]);
    }

    public function test_artisan_command_retensi_bersihkan_foto_menghapus_rapat_kadaluarsa(): void
    {
        Storage::fake('public');

        // Rapat 1: Kedaluwarsa (100 hari lalu, retensi 90 hari)
        $rapatKadaluarsa = Rapat::create([
            'kode' => 'EXP1',
            'judul' => 'Rapat Kedaluwarsa',
            'tanggal' => Carbon::now()->subDays(100),
            'tempat' => 'Ruang A',
            'penyelenggara' => 'Desa',
            'status' => StatusRapat::DITUTUP,
            'retensi_hari' => 90,
            'dibuat_oleh' => $this->adminProfil->id,
        ]);

        $undangan1 = Undangan::create([
            'rapat_id' => $rapatKadaluarsa->id,
            'nama' => 'Peserta Rapat 1',
        ]);

        $fotoPath1 = 'foto/exp1.jpg';
        Storage::disk('public')->put($fotoPath1, 'fake content 1');

        Kehadiran::create([
            'rapat_id' => $rapatKadaluarsa->id,
            'undangan_id' => $undangan1->id,
            'ttd_path' => 'ttd/sample1.png',
            'foto_path' => $fotoPath1,
            'jalur' => JalurCheckin::KIOSK,
        ]);

        // Rapat 2: Masih Aktif (10 hari lalu, retensi 90 hari)
        $rapatAktif = Rapat::create([
            'kode' => 'AKT2',
            'judul' => 'Rapat Masih Aktif',
            'tanggal' => Carbon::now()->subDays(10),
            'tempat' => 'Ruang B',
            'penyelenggara' => 'Desa',
            'status' => StatusRapat::DIBUKA,
            'retensi_hari' => 90,
            'dibuat_oleh' => $this->adminProfil->id,
        ]);

        $undangan2 = Undangan::create([
            'rapat_id' => $rapatAktif->id,
            'nama' => 'Peserta Rapat 2',
        ]);

        $fotoPath2 = 'foto/akt2.jpg';
        Storage::disk('public')->put($fotoPath2, 'fake content 2');

        Kehadiran::create([
            'rapat_id' => $rapatAktif->id,
            'undangan_id' => $undangan2->id,
            'ttd_path' => 'ttd/sample2.png',
            'foto_path' => $fotoPath2,
            'jalur' => JalurCheckin::KIOSK,
        ]);

        // Jalankan Artisan Command
        $this->artisan('retensi:bersihkan-foto')
            ->expectsOutputToContain('Pembersihan otomatis retensi foto selesai')
            ->assertSuccessful();

        $this->assertFalse(Storage::disk('public')->exists($fotoPath1));
        $this->assertTrue(Storage::disk('public')->exists($fotoPath2));

        $rapatKadaluarsa->refresh();
        $this->assertNotNull($rapatKadaluarsa->foto_dihapus_pada);

        $rapatAktif->refresh();
        $this->assertNull($rapatAktif->foto_dihapus_pada);
    }

    public function test_halaman_privasi_publik_dapat_diakses_tanpa_login(): void
    {
        $response = $this->get(route('privasi'));
        $response->assertOk();
        $response->assertSee('Kebijakan Privasi', false);
        $response->assertSee('Pelindungan Data Pribadi');
        $response->assertSee('UU No. 27 Tahun 2022');
        $response->assertSee('Pemerintah Desa Belega');
        $response->assertSee('90 hari');
    }

    public function test_livewire_pengaturan_retensi_dapat_mengubah_retensi_dan_eksekusi_pembersihan(): void
    {
        Storage::fake('public');

        $rapat = Rapat::create([
            'kode' => 'RTN2',
            'judul' => 'Rapat Uji Komponen Livewire',
            'tanggal' => Carbon::now()->subDays(50),
            'tempat' => 'Ruang Desa',
            'penyelenggara' => 'Desa Belega',
            'status' => StatusRapat::DITUTUP,
            'retensi_hari' => 90,
            'dibuat_oleh' => $this->adminProfil->id,
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(PengaturanRetensi::class)
            ->call('mulaiEditRetensi', $rapat->id, 90)
            ->set('editRetensiHari', 30)
            ->call('simpanRetensi', $rapat->id)
            ->assertSet('pesanSukses', "Masa retensi untuk rapat '{$rapat->judul}' berhasil diubah menjadi 30 hari.");

        $rapat->refresh();
        $this->assertEquals(30, $rapat->retensi_hari);

        // Uji eksekusi pembersihan manual
        Livewire::test(PengaturanRetensi::class)
            ->call('konfirmasiBersihkan', $rapat->id)
            ->assertSet('bukaModalKonfirmasi', true)
            ->call('eksekusiBersihkanFoto')
            ->assertSet('bukaModalKonfirmasi', false);

        $rapat->refresh();
        $this->assertNotNull($rapat->foto_dihapus_pada);
    }

    public function test_livewire_tabel_audit_log_pencarian_dan_filter(): void
    {
        AuditLog::create([
            'aktor' => $this->adminProfil->id,
            'aksi' => 'Aksi Uji Penambahan Akun Admin',
            'tabel_target' => 'profil',
            'baris_id' => '1',
            'rincian' => ['keterangan' => 'test data'],
        ]);

        AuditLog::create([
            'aktor' => $this->adminProfil->id,
            'aksi' => 'Aksi Uji Penghapusan Rapat',
            'tabel_target' => 'rapat',
            'baris_id' => '2',
            'rincian' => ['keterangan' => 'hapus'],
        ]);

        $this->actingAs($this->adminUser);

        Livewire::test(TabelAuditLog::class)
            ->assertSee('Aksi Uji Penambahan Akun Admin')
            ->assertSee('Aksi Uji Penghapusan Rapat')
            ->set('filterTabel', 'rapat')
            ->assertSee('Aksi Uji Penghapusan Rapat')
            ->assertDontSee('Aksi Uji Penambahan Akun Admin');
    }

    public function test_livewire_pengaturan_identitas_mencatat_audit_log(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(PengaturanIdentitas::class)
            ->set('nama_desa', 'Pemerintah Desa Belega Blahbatuh')
            ->call('simpan')
            ->assertSet('pesanSukses', 'Pengaturan identitas desa dan kop dinas berhasil disimpan.');

        $this->assertDatabaseHas('pengaturan_sistem', [
            'nama_desa' => 'Pemerintah Desa Belega Blahbatuh',
        ]);

        $this->assertDatabaseHas('audit_log', [
            'aktor' => $this->adminProfil->id,
            'tabel_target' => 'pengaturan_sistem',
            'aksi' => 'Memperbarui konfigurasi identitas instansi & kop dinas sistem',
        ]);
    }
}
