<?php

namespace Tests\Feature;

use App\Enums\JalurCheckin;
use App\Enums\PeranPengguna;
use App\Enums\SumberUndangan;
use App\Models\Kehadiran;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use App\Services\CheckinService;
use App\Services\OperatorService;
use App\Services\PencarianUndanganService;
use App\Services\RapatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class KioskDanCheckinTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Profil $adminProfil;
    protected Rapat $rapat;
    protected Undangan $undangan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');

        $this->adminUser = User::where('email', 'admin@belega.desa.id')->first();
        $this->adminProfil = $this->adminUser->profil;

        $rapatService = app(RapatService::class);
        $this->rapat = $rapatService->buatRapat([
            'judul' => 'Musyawarah Perencanaan Desa Belega',
            'tanggal' => '2026-11-15',
            'tempat' => 'Aula Kantor Desa',
            'pin_kiosk' => '123456',
        ], $this->adminProfil);

        $this->undangan = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Ketut Sumadana',
            'jabatan' => 'Kelian Banjar',
            'instansi' => 'Banjar Belega Kangin',
            'hp' => '081234567890',
            'sumber' => SumberUndangan::IMPORT,
            'urutan' => 1,
        ]);
    }

    public function test_kiosk_terkunci_pin_meminta_input_sebelum_masuk(): void
    {
        // 1. Akses awal tanpa PIN -> Menampilkan form PIN
        $response = $this->get('/kiosk/' . $this->rapat->kode);
        $response->assertStatus(200);
        $response->assertSee('Meja Kiosk Terkunci PIN');

        // 2. Kirim PIN salah -> Ditolak dengan pesan error
        $responseSalah = $this->post('/kiosk/' . $this->rapat->kode, [
            'pin_kiosk_input' => '000000',
        ]);
        $responseSalah->assertStatus(200);
        $responseSalah->assertSee('PIN Kiosk yang Anda masukkan salah');

        // 3. Kirim PIN benar -> Redirect & session unlocked
        $responseBenar = $this->post('/kiosk/' . $this->rapat->kode, [
            'pin_kiosk_input' => '123456',
        ]);
        $responseBenar->assertRedirect(route('kiosk.index', ['kode' => $this->rapat->kode]));
        $this->assertTrue(session('kiosk_unlocked_' . $this->rapat->id));

        // 4. Akses berikutnya -> Langsung masuk ke layar kiosk
        $responseMasuk = $this->get('/kiosk/' . $this->rapat->kode);
        $responseMasuk->assertStatus(200);
        $responseMasuk->assertSee('SIABDES Belega');
        $responseMasuk->assertSee('Selamat Datang');

        // 5. Kunci meja kiosk -> Session dihapus & kembali terkunci
        $responseKunci = $this->post('/kiosk/' . $this->rapat->kode . '/kunci');
        $responseKunci->assertRedirect(route('kiosk.index', ['kode' => $this->rapat->kode]));
        $this->assertNull(session('kiosk_unlocked_' . $this->rapat->id));
    }

    public function test_checkin_service_proses_kehadiran_idempoten_dan_simpan_storage(): void
    {
        $service = app(CheckinService::class);
        $checkinId = (string) Str::uuid();

        // 1x1 pixel PNG transparan & 1x1 JPEG dalam base64
        $fakeTtdBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $fakeFotoBase64 = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=';

        $kehadiran1 = $service->prosesCheckin([
            'id' => $checkinId,
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'ttd' => $fakeTtdBase64,
            'foto' => $fakeFotoBase64,
            'jalur' => 'kiosk',
            'perangkat_id' => 'tablet_meja_1',
            'waktu_perangkat' => now()->toIso8601String(),
        ]);

        $this->assertNotNull($kehadiran1);
        $this->assertEquals($checkinId, $kehadiran1->id);
        $this->assertEquals(JalurCheckin::KIOSK, $kehadiran1->jalur);

        // Verifikasi file storage tersimpan di disk public
        $this->assertTrue(Storage::disk('public')->exists($kehadiran1->ttd_path));
        $this->assertTrue(Storage::disk('public')->exists($kehadiran1->foto_path));

        // Uji idempotensi: kirim ulang checkin dengan ID yang sama
        $kehadiran2 = $service->prosesCheckin([
            'id' => $checkinId,
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'ttd' => $fakeTtdBase64,
        ]);

        $this->assertEquals($kehadiran1->id, $kehadiran2->id);
        $this->assertEquals(1, Kehadiran::where('id', $checkinId)->count());
    }

    public function test_checkin_service_membuat_undangan_tambahan_on_the_fly(): void
    {
        $service = app(CheckinService::class);
        $checkinId = (string) Str::uuid();
        $undanganId = (string) Str::uuid();

        $kehadiran = $service->prosesCheckin([
            'id' => $checkinId,
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $undanganId,
            'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
            'undangan_baru' => [
                'nama' => 'I Wayan Tamu Baru',
                'jabatan' => 'Warga',
                'instansi' => 'Banjar Kawan',
                'hp' => '087888999000',
            ],
            'jalur' => 'kiosk',
        ]);

        $this->assertDatabaseHas('undangan', [
            'id' => $undanganId,
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Wayan Tamu Baru',
            'sumber' => 'tambahan',
        ]);

        $this->assertDatabaseHas('kehadiran', [
            'id' => $checkinId,
            'undangan_id' => $undanganId,
        ]);
    }

    public function test_api_sinkron_kehadiran_batch(): void
    {
        $checkinId1 = (string) Str::uuid();
        $checkinId2 = (string) Str::uuid();

        $undangan2 = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'nama' => 'Ni Wayan Astuti',
        ]);

        $payload = [
            'items' => [
                [
                    'id' => $checkinId1,
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $this->undangan->id,
                    'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
                    'jalur' => 'kiosk',
                ],
                [
                    'id' => $checkinId2,
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $undangan2->id,
                    'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
                    'jalur' => 'kiosk',
                ],
            ]
        ];

        $response = $this->postJson('/api/sinkron-kehadiran', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'sukses' => true,
            'jumlah_sukses' => 2,
        ]);

        $this->assertDatabaseHas('kehadiran', ['id' => $checkinId1]);
        $this->assertDatabaseHas('kehadiran', ['id' => $checkinId2]);
    }

    public function test_pencarian_undangan_service_token_matching(): void
    {
        $service = app(PencarianUndanganService::class);

        $hasil = $service->cari($this->rapat->id, 'Sumadana Kangin');
        $this->assertCount(1, $hasil);
        $this->assertEquals('I Ketut Sumadana', $hasil->first()->nama);

        // Pastikan kolom hp tidak disertakan demi perlindungan privasi
        $this->assertNull($hasil->first()->hp);
    }

    public function test_rute_registrasi_mandiri_ponsel_publik(): void
    {
        $response = $this->get('/r/' . $this->rapat->kode);
        $response->assertStatus(200);
        $response->assertSee($this->rapat->judul);

        // Uji endpoint ajax pencarian
        $responseCari = $this->getJson('/r/' . $this->rapat->kode . '/cari?q=Ketut');
        $responseCari->assertStatus(200);
        $responseCari->assertJsonFragment([
            'nama' => 'I Ketut Sumadana',
        ]);
    }

    public function test_halaman_kartu_bukti_hadir_digital_dapat_diakses_dan_menampilkan_data_peserta(): void
    {
        $kehadiran = Kehadiran::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'ttd_path' => 'ttd/sample-ttd.png',
            'jalur' => JalurCheckin::KIOSK,
            'waktu_perangkat' => now(),
            'dibatalkan' => false,
        ]);

        $response = $this->get(route('kehadiran.bukti', $kehadiran->id));

        $response->assertOk();
        $response->assertViewIs('kehadiran.kartu-bukti');
        $response->assertSee('KARTU BUKTI KEHADIRAN DIGITAL');
        $response->assertSee('I Ketut Sumadana');
        $response->assertSee('Kelian Banjar');
        $response->assertSee('Banjar Belega Kangin');
        $response->assertSee($this->rapat->judul);
        $response->assertSee('Hadir Sah');
    }

    public function test_daftar_undangan_kiosk_dan_registrasi_menyertakan_kehadiran_id_untuk_akses_bukti(): void
    {
        $kehadiran = Kehadiran::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'ttd_path' => 'ttd/sample-ttd.png',
            'jalur' => JalurCheckin::KIOSK,
            'waktu_perangkat' => now(),
            'dibatalkan' => false,
        ]);

        // Cek endpoint JSON kiosk/data
        $responseJson = $this->getJson('/kiosk/' . $this->rapat->kode . '/data');
        $responseJson->assertOk();
        $responseJson->assertJsonFragment([
            'id' => $this->undangan->id,
            'sudah_hadir' => true,
            'kehadiran_id' => $kehadiran->id,
        ]);

        // Cek view registrasi mandiri
        $responseView = $this->get('/r/' . $this->rapat->kode);
        $responseView->assertOk();
        $undanganAwal = $responseView->viewData('undanganAwal');
        $this->assertTrue($undanganAwal->first()->sudah_hadir);
        $this->assertEquals($kehadiran->id, $undanganAwal->first()->kehadiran_id);
    }
}
