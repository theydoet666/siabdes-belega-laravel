<?php

namespace Tests\Feature;

use App\Enums\JalurCheckin;
use App\Enums\StatusRapat;
use App\Enums\SumberUndangan;
use App\Models\Kehadiran;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use App\Services\CheckinService;
use App\Services\PencarianUndanganService;
use App\Services\RapatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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
        Storage::fake('local');

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
        $response = $this->get('/kiosk/'.$this->rapat->kode);
        $response->assertStatus(200);
        $response->assertSee('Meja Kiosk Terkunci PIN');

        // 2. Kirim PIN salah -> Ditolak dengan pesan error
        $responseSalah = $this->post('/kiosk/'.$this->rapat->kode, [
            'pin_kiosk_input' => '000000',
        ]);
        $responseSalah->assertStatus(200);
        $responseSalah->assertSee('PIN Kiosk yang Anda masukkan salah');

        // 3. Kirim PIN benar -> Redirect & session unlocked
        $responseBenar = $this->post('/kiosk/'.$this->rapat->kode, [
            'pin_kiosk_input' => '123456',
        ]);
        $responseBenar->assertRedirect(route('kiosk.index', ['kode' => $this->rapat->kode]));
        $this->assertTrue(session('kiosk_unlocked_'.$this->rapat->id));

        // 4. Akses berikutnya -> Langsung masuk ke layar kiosk
        $responseMasuk = $this->get('/kiosk/'.$this->rapat->kode);
        $responseMasuk->assertStatus(200);
        $responseMasuk->assertSee('SIABDES Belega');
        $responseMasuk->assertSee('Selamat Datang');

        // 5. Kunci meja kiosk -> Session dihapus & kembali terkunci
        $responseKunci = $this->post('/kiosk/'.$this->rapat->kode.'/kunci');
        $responseKunci->assertRedirect(route('kiosk.index', ['kode' => $this->rapat->kode]));
        $this->assertNull(session('kiosk_unlocked_'.$this->rapat->id));
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

        // Verifikasi file storage tersimpan di disk lokal (privat) dan tidak di disk publik
        $this->assertTrue(Storage::disk('local')->exists($kehadiran1->ttd_path));
        $this->assertTrue(Storage::disk('local')->exists($kehadiran1->foto_path));
        $this->assertFalse(Storage::disk('public')->exists($kehadiran1->ttd_path));
        $this->assertFalse(Storage::disk('public')->exists($kehadiran1->foto_path));

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
            ],
        ];

        $response = $this->withSession(['kiosk_unlocked_'.$this->rapat->id => true])
            ->postJson('/api/sinkron-kehadiran', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'sukses' => true,
            'jumlah_sukses' => 2,
        ]);

        $this->assertDatabaseHas('kehadiran', ['id' => $checkinId1]);
        $this->assertDatabaseHas('kehadiran', ['id' => $checkinId2]);
    }

    public function test_keamanan_api_sinkron_kehadiran_validasi_dan_otorisasi(): void
    {
        // 1. Jalur kiosk tanpa sesi dan tanpa token kiosk ditolak 403
        $responseTanpaSesi = $this->postJson('/api/sinkron-kehadiran', [
            'items' => [
                [
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $this->undangan->id,
                    'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
                    'jalur' => 'kiosk',
                ],
            ],
        ]);
        $responseTanpaSesi->assertStatus(403);

        // 2. Akses dengan header X-Kiosk-Token yang valid diizinkan
        $tokenKiosk = (string) Str::uuid();
        Cache::put('kiosk_token_'.$this->rapat->id, $tokenKiosk, now()->addDay());
        $responseDenganToken = $this->withHeaders(['X-Kiosk-Token' => $tokenKiosk])
            ->postJson('/api/sinkron-kehadiran', [
                'items' => [
                    [
                        'rapat_id' => $this->rapat->id,
                        'undangan_id' => $this->undangan->id,
                        'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
                        'jalur' => 'kiosk',
                    ],
                ],
            ]);
        $responseDenganToken->assertStatus(200);

        // 3. Payload dengan undangan_id dari rapat lain ditolak (422)
        $rapatLain = app(RapatService::class)->buatRapat([
            'judul' => 'Rapat Koordinasi Lainnya',
            'tanggal' => '2026-11-20',
            'tempat' => 'Balai Banjar',
        ], $this->adminProfil);

        $undanganRapatLain = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapatLain->id,
            'nama' => 'I Made Korban Lintas Rapat',
        ]);

        $responseLintasRapat = $this->withSession(['kiosk_unlocked_'.$this->rapat->id => true])
            ->postJson('/api/sinkron-kehadiran', [
                'items' => [
                    [
                        'rapat_id' => $this->rapat->id, // Rapat ini
                        'undangan_id' => $undanganRapatLain->id, // Undangan rapat lain
                        'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
                        'jalur' => 'kiosk',
                    ],
                ],
            ]);
        $responseLintasRapat->assertStatus(422);

        // 4. Berkas bukan gambar (data teks/script disamarkan sebagai base64) ditolak (422)
        $bukanGambarBase64 = 'data:image/png;base64,'.base64_encode('<?php echo "evil script"; ?>');
        $responseBukanGambar = $this->withSession(['kiosk_unlocked_'.$this->rapat->id => true])
            ->postJson('/api/sinkron-kehadiran', [
                'items' => [
                    [
                        'rapat_id' => $this->rapat->id,
                        'undangan_id' => $this->undangan->id,
                        'ttd' => $bukanGambarBase64,
                        'jalur' => 'kiosk',
                    ],
                ],
            ]);
        $responseBukanGambar->assertStatus(422);

        // 5. Payload melebihi ukuran (ttd > 300 KB) ditolak (422)
        $dataBesar = 'data:image/png;base64,'.base64_encode(str_repeat('A', 350 * 1024));
        $responseUkuranLebih = $this->withSession(['kiosk_unlocked_'.$this->rapat->id => true])
            ->postJson('/api/sinkron-kehadiran', [
                'items' => [
                    [
                        'rapat_id' => $this->rapat->id,
                        'undangan_id' => $this->undangan->id,
                        'ttd' => $dataBesar,
                        'jalur' => 'kiosk',
                    ],
                ],
            ]);
        $responseUkuranLebih->assertStatus(422);
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
        $response = $this->get('/r/'.$this->rapat->kode);
        $response->assertStatus(200);
        $response->assertSee($this->rapat->judul);

        // Uji endpoint ajax pencarian
        $responseCari = $this->getJson('/r/'.$this->rapat->kode.'/cari?q=Ketut');
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

        // Permintaan tanpa signature valid harus ditolak 403
        $this->get(route('kehadiran.bukti', $kehadiran->id))->assertStatus(403);

        // Permintaan dengan temporary signed URL harus berhasil 200
        $urlSigned = URL::temporarySignedRoute('kehadiran.bukti', now()->addHours(24), ['id' => $kehadiran->id]);
        $response = $this->get($urlSigned);

        $response->assertOk();

        $response->assertViewIs('kehadiran.kartu-bukti');
        $response->assertSee('KARTU BUKTI KEHADIRAN DIGITAL');
        $response->assertSee('I Ketut Sumadana');
        $response->assertSee('Kelian Banjar');
        $response->assertSee('Banjar Belega Kangin');
        $response->assertSee($this->rapat->judul);
        $response->assertSee('Hadir Sah');

        // Akses via tautan rapat publik /r/{kode}/bukti/{id} harus redirect ke signed URL dan bisa dibuka
        $redirectResponse = $this->get(route('kehadiran.bukti.buka', [
            'kode' => $this->rapat->kode,
            'id' => $kehadiran->id,
        ]));
        $redirectResponse->assertRedirect();
        $targetUrl = $redirectResponse->headers->get('Location');
        $this->assertStringContainsString('/bukti/'.$kehadiran->id, $targetUrl);
        $this->assertStringContainsString('signature=', $targetUrl);

        $responseFollow = $this->get($targetUrl);
        $responseFollow->assertOk();
        $responseFollow->assertSee('KARTU BUKTI KEHADIRAN DIGITAL');
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

        // Cek endpoint JSON kiosk/data (dengan sesi PIN terbuka)
        $responseJson = $this->withSession(['kiosk_unlocked_'.$this->rapat->id => true])
            ->getJson('/kiosk/'.$this->rapat->kode.'/data');
        $responseJson->assertOk();
        $responseJson->assertJsonFragment([
            'id' => $this->undangan->id,
            'sudah_hadir' => true,
            'kehadiran_id' => $kehadiran->id,
        ]);

        // Cek view registrasi mandiri
        $responseView = $this->get('/r/'.$this->rapat->kode);
        $responseView->assertOk();
        $undanganAwal = $responseView->viewData('undanganAwal');
        $this->assertTrue($undanganAwal->first()->sudah_hadir);
        $this->assertEquals($kehadiran->id, $undanganAwal->first()->kehadiran_id);
    }

    public function test_kiosk_data_memerlukan_pin_dan_tidak_membocorkan_pin_maupun_hp(): void
    {
        // 1. Request tanpa sesi PIN pada rapat ber-PIN ditolak 403
        $responseTerkunci = $this->getJson('/kiosk/'.$this->rapat->kode.'/data');
        $responseTerkunci->assertStatus(403);

        // 2. Request setelah PIN dibuka (dengan sesi) mengembalikan 200
        $responseTerbuka = $this->withSession(['kiosk_unlocked_'.$this->rapat->id => true])
            ->getJson('/kiosk/'.$this->rapat->kode.'/data');
        $responseTerbuka->assertStatus(200);

        // 3. Pastikan tidak ada pin_kiosk atau hp dalam response JSON
        $json = $responseTerbuka->json();
        $this->assertArrayNotHasKey('pin_kiosk', $json['rapat']);
        $this->assertStringNotContainsString('pin_kiosk', $responseTerbuka->getContent());
        $this->assertStringNotContainsString('081234567890', $responseTerbuka->getContent());

        foreach ($json['undangan'] as $u) {
            $this->assertArrayNotHasKey('hp', $u);
        }

        // 4. Pastikan view registrasi mandiri dan pencarian cari tidak memuat hp
        $responseRegistrasi = $this->get('/r/'.$this->rapat->kode);
        $responseRegistrasi->assertOk();
        $this->assertStringNotContainsString('081234567890', $responseRegistrasi->getContent());

        $responseCari = $this->getJson('/r/'.$this->rapat->kode.'/cari?q=Ketut');
        $responseCari->assertOk();
        $responseCari->assertJsonMissing(['hp' => '081234567890']);
    }

    public function test_rate_limit_pin_kiosk_dan_rute_publik(): void
    {
        // Bersihkan state rate limiter sebelum pengujian
        RateLimiter::clear('kiosk-pin|127.0.0.1|'.$this->rapat->id);

        // 1. Kirim 5 PIN salah berturut-turut (masih diizinkan mencoba, status 200)
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->post('/kiosk/'.$this->rapat->kode, [
                'pin_kiosk_input' => '999999',
            ]);
            $res->assertStatus(200);
            $res->assertSee('PIN Kiosk yang Anda masukkan salah');
        }

        // 2. Percobaan ke-6 salah menghasilkan blokir sementara (status 429)
        $resBlokir = $this->post('/kiosk/'.$this->rapat->kode, [
            'pin_kiosk_input' => '999999',
        ]);
        $resBlokir->assertStatus(429);
        $resBlokir->assertSee('Terlalu banyak percobaan');

        // 3. Rute publik terkena throttle 429 setelah melebihi batas 60 request/menit
        RateLimiter::clear('publik|127.0.0.1');
        for ($i = 1; $i <= 60; $i++) {
            $this->get('/r/'.$this->rapat->kode);
        }

        $resThrottle = $this->get('/r/'.$this->rapat->kode);
        $resThrottle->assertStatus(429);
    }

    public function test_keamanan_berkas_privat_dan_rute_signed_bukti_kehadiran(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        // 1. Simpan foto dan tanda tangan palsu di disk lokal (privat)
        $fotoPath = 'foto/'.$this->rapat->id.'/peserta-1.jpg';
        $ttdPath = 'ttd/'.$this->rapat->id.'/peserta-1.png';
        Storage::disk('local')->put($fotoPath, 'fake-foto-content');
        Storage::disk('local')->put($ttdPath, 'fake-ttd-content');

        $kehadiran = Kehadiran::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'foto_path' => $fotoPath,
            'ttd_path' => $ttdPath,
            'jalur' => JalurCheckin::KIOSK,
            'waktu_perangkat' => now(),
            'dibatalkan' => false,
        ]);

        // 2. Akses berkas tanpa login & tanpa signature harus ditolak (403)
        $this->get('/berkas/foto/'.$kehadiran->id)->assertStatus(403);
        $this->get('/berkas/ttd/'.$kehadiran->id)->assertStatus(403);

        // 3. Akses berkas dengan signed URL harus diizinkan (200)
        $urlFotoSigned = URL::temporarySignedRoute('berkas.tampil', now()->addHours(24), ['jenis' => 'foto', 'id' => $kehadiran->id]);
        $resFoto = $this->get($urlFotoSigned);
        $resFoto->assertOk();
        $this->assertEquals('fake-foto-content', $resFoto->getContent());

        $urlTtdSigned = URL::temporarySignedRoute('berkas.tampil', now()->addHours(24), ['jenis' => 'ttd', 'id' => $kehadiran->id]);
        $resTtd = $this->get($urlTtdSigned);
        $resTtd->assertOk();
        $this->assertEquals('fake-ttd-content', $resTtd->getContent());

        // 4. Akses berkas oleh operator/admin yang login harus diizinkan tanpa signature (200)
        $this->actingAs($this->adminUser);
        $this->get('/berkas/foto/'.$kehadiran->id)->assertOk();
        $this->get('/berkas/ttd/'.$kehadiran->id)->assertOk();

        // 5. Perintah artisan memindahkan berkas lama dari public ke disk privat (local)
        Storage::disk('public')->put('foto/'.$this->rapat->id.'/lama.jpg', 'konten-foto-lama');
        Storage::disk('public')->put('ttd/'.$this->rapat->id.'/lama.png', 'konten-ttd-lama');
        $kehadiranLama = Kehadiran::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'foto_path' => 'foto/'.$this->rapat->id.'/lama.jpg',
            'ttd_path' => 'ttd/'.$this->rapat->id.'/lama.png',
            'jalur' => JalurCheckin::KIOSK,
            'waktu_perangkat' => now(),
            'dibatalkan' => false,
        ]);

        $this->artisan('berkas:pindahkan-ke-privat')->assertExitCode(0);
        $this->assertFalse(Storage::disk('public')->exists('foto/'.$this->rapat->id.'/lama.jpg'));
        $this->assertTrue(Storage::disk('local')->exists('foto/'.$this->rapat->id.'/lama.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('ttd/'.$this->rapat->id.'/lama.png'));
        $this->assertTrue(Storage::disk('local')->exists('ttd/'.$this->rapat->id.'/lama.png'));
    }

    public function test_kiosk_tanpa_pin_berfungsi_normal_dan_session_unlocked(): void
    {
        $rapatService = app(RapatService::class);
        $rapatTanpaPin = $rapatService->buatRapat([
            'judul' => 'Rapat Terbuka Warga Belega',
            'tanggal' => '2026-11-20',
            'tempat' => 'Bale Banjar',
            'pin_kiosk' => null,
        ], $this->adminProfil);

        // Akses langsung tanpa form PIN
        $res = $this->get('/kiosk/'.$rapatTanpaPin->kode);
        $res->assertStatus(200);
        $res->assertSee('SIABDES Belega');
        $this->assertTrue(session('kiosk_unlocked_'.$rapatTanpaPin->id));
        $this->assertNotEmpty(session('kiosk_token_'.$rapatTanpaPin->id));
    }

    public function test_keamanan_api_sinkron_mencegah_spoofing_jalur_operator_dan_menolak_rapat_ditutup(): void
    {
        $fakeTtd = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        // 1. Anonim mencoba memalsukan jalur 'operator' -> Ditolak 403
        $resSpoof = $this->postJson('/api/sinkron-kehadiran', [
            'items' => [
                [
                    'id' => (string) Str::uuid(),
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $this->undangan->id,
                    'jalur' => 'operator',
                    'ttd' => $fakeTtd,
                ],
            ],
        ]);
        $resSpoof->assertStatus(403);
        $resSpoof->assertJsonFragment(['error' => 'Akses ditolak: jalur operator mewajibkan sesi operator atau admin yang aktif.']);

        // 2. Rapat ditutup -> Jalur mandiri ditolak 403
        $this->rapat->update(['status' => StatusRapat::DITUTUP]);
        $resMandiri = $this->postJson('/api/sinkron-kehadiran', [
            'items' => [
                [
                    'id' => (string) Str::uuid(),
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $this->undangan->id,
                    'jalur' => 'mandiri',
                    'ttd' => $fakeTtd,
                ],
            ],
        ]);
        $resMandiri->assertStatus(403);
        $resMandiri->assertJsonFragment(['error' => 'Akses ditolak: registrasi mandiri ditutup untuk rapat ini.']);

        // 3. Operator resmi yang login dapat menggunakan jalur operator
        $this->actingAs($this->adminUser);
        $checkinId = (string) Str::uuid();
        $resOperator = $this->postJson('/api/sinkron-kehadiran', [
            'items' => [
                [
                    'id' => $checkinId,
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $this->undangan->id,
                    'jalur' => 'operator',
                    'ttd' => $fakeTtd,
                ],
            ],
        ]);
        $resOperator->assertStatus(200);
        $resOperator->assertJson(['sukses' => true, 'jumlah_sukses' => 1]);
    }

    public function test_checkin_service_menolak_kehadiran_peserta_jika_rapat_telah_ditutup(): void
    {
        $service = app(CheckinService::class);
        $this->rapat->update(['status' => StatusRapat::DITUTUP]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Absensi ditolak karena rapat telah resmi ditutup.');

        $service->prosesCheckin([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $this->undangan->id,
            'ttd' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
            'jalur' => 'kiosk',
        ]);
    }
}
