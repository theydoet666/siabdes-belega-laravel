<?php

namespace Tests\Feature;

use App\Enums\PeranPengguna;
use App\Enums\StatusRapat;
use App\Http\Livewire\Rapat\DaftarRapat;
use App\Http\Livewire\Rapat\DetailRapat;
use App\Http\Livewire\Rapat\FormRapat;
use App\Http\Livewire\Rapat\ModalDuplikasiRapat;
use App\Http\Livewire\Rapat\PanelQr;
use App\Models\AuditLog;
use App\Models\Kehadiran;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use App\Services\KodeRapatService;
use App\Services\OperatorService;
use App\Services\RapatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ManajemenRapatTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Profil $adminProfil;
    protected User $operatorUser;
    protected Profil $operatorProfil;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::where('email', 'admin@belega.desa.id')->first();
        $this->adminProfil = $this->adminUser->profil;

        $operatorService = app(OperatorService::class);
        $this->operatorProfil = $operatorService->tambahOperator(
            email: 'operator@belega.desa.id',
            password: 'password123',
            nama: 'Operator Penguji',
            peran: PeranPengguna::OPERATOR
        );
        $this->operatorUser = $this->operatorProfil->user;
    }

    public function test_kode_rapat_service_menghasilkan_4_karakter_tanpa_karakter_ambigu(): void
    {
        $service = app(KodeRapatService::class);

        for ($i = 0; $i < 50; $i++) {
            $kode = $service->generate();

            $this->assertEquals(4, strlen($kode));
            $this->assertTrue($service->isValid($kode));
            $this->assertDoesNotMatchConstraints('/[IO01]/i', $kode);
        }
    }

    private function assertDoesNotMatchConstraints(string $pattern, string $string): void
    {
        $this->assertFalse((bool) preg_match($pattern, $string), "String '{$string}' mengandung karakter yang dilarang.");
    }

    public function test_form_rapat_dapat_membuat_rapat_baru_dengan_default_lengkap(): void
    {
        $this->actingAs($this->operatorUser);

        Livewire::test(FormRapat::class)
            ->set('judul', 'Musyawarah RKPDes 2027')
            ->set('tanggal', '2026-10-15')
            ->set('jam_mulai', '09:00')
            ->set('jam_selesai', '12:30')
            ->set('tempat', 'Gedung Serbaguna Desa Belega')
            ->set('penyelenggara', 'Pemerintah Desa Belega')
            ->set('pin_kiosk', '123456')
            ->set('retensi_hari', 90)
            ->set('penandatangan_nama', 'I WAYAN SUDARSANA, S.Sos.')
            ->set('penandatangan_jabatan', 'Perbekel Belega')
            ->set('penandatangan_lokasi', 'Belega')
            ->call('simpan')
            ->assertHasNoErrors();

        $rapat = Rapat::where('judul', 'Musyawarah RKPDes 2027')->first();
        $this->assertNotNull($rapat);
        $this->assertEquals(4, strlen($rapat->kode));
        $this->assertEquals(StatusRapat::DRAFT, $rapat->status);
        $this->assertTrue(Hash::check('123456', $rapat->pin_kiosk));
        $this->assertEquals('Gedung Serbaguna Desa Belega', $rapat->tempat);

        // Pastikan audit log tercatat
        $this->assertDatabaseHas('audit_log', [
            'aktor' => $this->operatorProfil->id,
            'tabel_target' => 'rapat',
            'baris_id' => $rapat->id,
        ]);
    }

    public function test_detail_rapat_dapat_mengubah_status_siklus_hidup_dan_konfigurasi(): void
    {
        $this->actingAs($this->operatorUser);
        $rapatService = app(RapatService::class);

        $rapat = $rapatService->buatRapat([
            'judul' => 'Rapat Koordinasi BPD',
            'tanggal' => '2026-11-01',
            'tempat' => 'Kantor Desa Belega',
            'penyelenggara' => 'Pemerintah Desa Belega',
            'retensi_hari' => 90,
            'pin_kiosk' => '654321',
            'penandatangan_nama' => 'I WAYAN SUDARSANA, S.Sos.',
            'penandatangan_jabatan' => 'Perbekel Belega',
            'penandatangan_nip' => '',
            'penandatangan_lokasi' => 'Belega',
        ], $this->operatorProfil);

        // Ubah status ke DIBUKA
        Livewire::test(DetailRapat::class, ['id' => $rapat->id])
            ->call('ubahStatus', 'dibuka')
            ->assertSet('status', 'dibuka')
            ->assertSee('DIBUKA');

        $this->assertEquals(StatusRapat::DIBUKA, $rapat->fresh()->status);

        // Ubah konfigurasi tempat & catatan
        Livewire::test(DetailRapat::class, ['id' => $rapat->id])
            ->set('tempat', 'Ruang Rapat Utama')
            ->set('catatan', 'Membawa berkas fisik')
            ->call('simpanPerubahan')
            ->assertHasNoErrors()
            ->assertSee('Perubahan konfigurasi rapat berhasil disimpan.');

        $this->assertEquals('Ruang Rapat Utama', $rapat->fresh()->tempat);
        $this->assertEquals('Membawa berkas fisik', $rapat->fresh()->catatan);
    }

    public function test_duplikasi_rapat_menyalin_informasi_dan_undangan_dengan_kode_unik_baru(): void
    {
        $this->actingAs($this->operatorUser);
        $rapatService = app(RapatService::class);

        $rapatAsal = $rapatService->buatRapat([
            'judul' => 'Rapat Evaluasi Triwulan',
            'tanggal' => '2026-11-10',
            'tempat' => 'Ruang Rapat',
            'penyelenggara' => 'Pemerintah Desa Belega',
            'pin_kiosk' => '112233',
        ], $this->operatorProfil);

        // Tambah 2 undangan
        Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapatAsal->id,
            'nama' => 'I Ketut Dana',
            'jabatan' => 'Kelian Banjar',
            'instansi' => 'Banjar Belega Kangin',
        ]);
        Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapatAsal->id,
            'nama' => 'Ni Luh Putu Rai',
            'jabatan' => 'Ketua PKK',
            'instansi' => 'PKK Desa Belega',
        ]);

        $this->assertEquals(2, $rapatAsal->undangan()->count());

        // Duplikasi via Livewire
        Livewire::test(ModalDuplikasiRapat::class)
            ->call('buka', $rapatAsal->id)
            ->set('judulBaru', 'Rapat Evaluasi Triwulan 2')
            ->set('salinUndangan', true)
            ->call('duplikasi')
            ->assertHasNoErrors();

        $rapatDuplikat = Rapat::where('judul', 'Rapat Evaluasi Triwulan 2')->first();
        $this->assertNotNull($rapatDuplikat);
        $this->assertNotEquals($rapatAsal->id, $rapatDuplikat->id);
        $this->assertNotEquals($rapatAsal->kode, $rapatDuplikat->kode);
        $this->assertEquals(StatusRapat::DRAFT, $rapatDuplikat->status);
        $this->assertEquals(2, $rapatDuplikat->undangan()->count());
    }

    public function test_panel_qr_merender_qrcode_dengan_url_registrasi(): void
    {
        $rapatService = app(RapatService::class);
        $rapat = $rapatService->buatRapat([
            'judul' => 'Rapat Uji QR',
            'tanggal' => '2026-12-01',
            'tempat' => 'Aula',
            'pin_kiosk' => '999999',
        ], $this->operatorProfil);

        Livewire::test(PanelQr::class)
            ->call('buka', $rapat->id)
            ->assertSet('tampil', true)
            ->assertSee($rapat->kode)
            ->assertSee('/r/' . $rapat->kode);
    }

    public function test_operator_tidak_bisa_menghapus_rapat_tetapi_admin_bisa(): void
    {
        Storage::fake('public');

        $rapatService = app(RapatService::class);
        $rapat = $rapatService->buatRapat([
            'judul' => 'Rapat Khusus Hapus',
            'tanggal' => '2026-12-05',
            'tempat' => 'Ruang Sekretariat',
            'pin_kiosk' => '123123',
        ], $this->operatorProfil);

        // Tambah undangan & kehadiran dummy beserta file storage
        $undangan = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapat->id,
            'nama' => 'Peserta Uji',
        ]);

        $ttdPath = 'ttd/test_ttd_' . $rapat->id . '.png';
        $fotoPath = 'foto/test_foto_' . $rapat->id . '.jpg';
        Storage::disk('public')->put($ttdPath, 'fake-signature-content');
        Storage::disk('public')->put($fotoPath, 'fake-photo-content');

        Kehadiran::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapat->id,
            'undangan_id' => $undangan->id,
            'ttd_path' => $ttdPath,
            'foto_path' => $fotoPath,
            'jalur' => 'kiosk',
        ]);

        $this->assertTrue(Storage::disk('public')->exists($ttdPath));
        $this->assertTrue(Storage::disk('public')->exists($fotoPath));

        // 1. Operator mencoba menghapus -> harus DITOLAK (403)
        $this->actingAs($this->operatorUser);
        $this->assertFalse($this->operatorUser->can('delete', $rapat));

        // 2. Admin menghapus -> BERHASIL
        $this->actingAs($this->adminUser);
        $this->assertTrue($this->adminUser->can('delete', $rapat));

        Livewire::test(DetailRapat::class, ['id' => $rapat->id])
            ->call('hapusRapat')
            ->assertRedirect(route('rapat.index'));

        // Cek database
        $this->assertDatabaseMissing('rapat', ['id' => $rapat->id]);
        $this->assertDatabaseMissing('undangan', ['id' => $undangan->id]);

        // Cek file storage terhapus setelah commit
        $this->assertFalse(Storage::disk('public')->exists($ttdPath));
        $this->assertFalse(Storage::disk('public')->exists($fotoPath));

        // Cek audit log penghapusan rapat tercatat
        $this->assertDatabaseHas('audit_log', [
            'aktor' => $this->adminProfil->id,
            'tabel_target' => 'rapat',
            'baris_id' => $rapat->id,
        ]);
    }

    public function test_daftar_rapat_pencarian_dan_filter_status(): void
    {
        $this->actingAs($this->operatorUser);
        $rapatService = app(RapatService::class);

        $r1 = $rapatService->buatRapat([
            'judul' => 'Sosialisasi Bank Sampah',
            'tanggal' => '2026-10-01',
            'tempat' => 'Balai Banjar',
            'pin_kiosk' => '111111',
        ], $this->operatorProfil);

        $r2 = $rapatService->buatRapat([
            'judul' => 'Rembuk Stunting Posyandu',
            'tanggal' => '2026-10-02',
            'tempat' => 'Puskesmas Pembantu',
            'pin_kiosk' => '222222',
        ], $this->operatorProfil);

        $r2->status = StatusRapat::DIBUKA;
        $r2->save();

        Livewire::test(DaftarRapat::class)
            ->set('cari', 'Sampah')
            ->assertSee('Sosialisasi Bank Sampah')
            ->assertDontSee('Rembuk Stunting Posyandu');

        Livewire::test(DaftarRapat::class)
            ->set('filterStatus', 'dibuka')
            ->assertSee('Rembuk Stunting Posyandu')
            ->assertDontSee('Sosialisasi Bank Sampah');
    }
}
