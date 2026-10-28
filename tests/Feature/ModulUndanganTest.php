<?php

namespace Tests\Feature;

use App\Enums\JalurCheckin;
use App\Enums\SumberUndangan;
use App\Http\Livewire\Undangan\FormUndangan;
use App\Http\Livewire\Undangan\ModalPilihRiwayatUndangan;
use App\Http\Livewire\Undangan\TabelUndangan;
use App\Models\Kehadiran;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use App\Repositories\UndanganRepository;
use App\Services\ImporUndanganService;
use App\Services\RapatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ModulUndanganTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Profil $adminProfil;

    protected Rapat $rapat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::where('email', 'admin@belega.desa.id')->first();
        $this->adminProfil = $this->adminUser->profil;

        $rapatService = app(RapatService::class);
        $this->rapat = $rapatService->buatRapat([
            'judul' => 'Rapat Uji Modul Undangan',
            'tanggal' => '2026-10-20',
            'tempat' => 'Ruang Rapat',
            'penyelenggara' => 'Pemerintah Desa Belega',
            'pin_kiosk' => '123456',
        ], $this->adminProfil);
    }

    public function test_impor_undangan_service_validasi_dan_deteksi_duplikat(): void
    {
        $service = app(ImporUndanganService::class);

        // Buat file CSV contoh 10 baris (termasuk 1 duplikat di file)
        $csvContent = "Nama,Jabatan,Instansi,No HP\n"
            ."I Wayan Sudarsana,Perbekel,Pemerintah Desa,0811111111\n"
            ."I Made Sujana,Sekdes,Pemerintah Desa,0822222222\n"
            ."Ni Luh Gede Rai,Kaur Keuangan,Pemerintah Desa,0833333333\n"
            ."I Ketut Ariawan,Kasi Pemerintahan,Pemerintah Desa,0844444444\n"
            ."I Nyoman Sugiartha,Kelian Banjar,Banjar Belega Kangin,0855555555\n"
            ."I Wayan Mardika,Kelian Banjar,Banjar Belega Kawan,0866666666\n"
            ."Ni Kadek Ayu,Ketua BPD,BPD Desa Belega,0877777777\n"
            ."I Wayan Sudarsana,Perbekel,Pemerintah Desa,0811111111\n" // DUPLIKAT DI FILE
            ."I Gusti Ngurah Agung,Tokoh Adat,Desa Adat Belega,0888888888\n"
            .",Anggota Tanpa Nama,Organisasi,0899999999\n"; // INVALID TANPA NAMA

        $tempFile = tempnam(sys_get_temp_dir(), 'test_csv_').'.csv';
        file_put_contents($tempFile, $csvContent);

        $uploadedFile = new UploadedFile($tempFile, 'undangan.csv', 'text/csv', null, true);

        $preview = $service->bacaDanValidasi($uploadedFile, $this->rapat->id);

        $this->assertEquals(10, $preview['total']);
        $this->assertEquals(8, $preview['total_valid']);
        $this->assertEquals(1, $preview['total_duplikat']);
        $this->assertEquals(1, $preview['total_invalid']);

        // Simpan dengan abaikan duplikat
        $disimpan = $service->simpan($this->rapat->id, $preview['baris'], true, $this->adminProfil);
        $this->assertEquals(8, $disimpan);
        $this->assertEquals(8, Undangan::where('rapat_id', $this->rapat->id)->count());

        // Uji deteksi duplikat terhadap data yang sudah ada di database
        $previewUlang = $service->bacaDanValidasi($uploadedFile, $this->rapat->id);
        $this->assertGreaterThan(0, $previewUlang['total_duplikat']);

        @unlink($tempFile);
    }

    public function test_undangan_repository_riwayat_unik(): void
    {
        $rapatService = app(RapatService::class);
        $rapat2 = $rapatService->buatRapat([
            'judul' => 'Rapat Kedua',
            'tanggal' => '2026-10-25',
            'tempat' => 'Ruang Rapat 2',
        ], $this->adminProfil);

        // Tambah undangan di rapat 1
        Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Made Tokoh',
            'jabatan' => 'Ketua LPM',
            'instansi' => 'LPM Desa Belega',
            'hp' => '0812345678',
        ]);

        // Tambah undangan yang sama di rapat 2
        Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapat2->id,
            'nama' => 'I Made Tokoh',
            'jabatan' => 'Ketua LPM',
            'instansi' => 'LPM Desa Belega',
            'hp' => '0812345678',
        ]);

        // Tambah orang beda di rapat 2
        Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapat2->id,
            'nama' => 'Ni Nyoman Putri',
            'jabatan' => 'Bendahara BPD',
            'instansi' => 'BPD Desa Belega',
            'hp' => '0898765432',
        ]);

        $repo = app(UndanganRepository::class);
        $riwayat = $repo->riwayatUnik();

        // Hanya boleh ada 2 baris unik
        $this->assertEquals(2, $riwayat->count());
        $this->assertEquals(['I Made Tokoh', 'Ni Nyoman Putri'], $riwayat->pluck('nama')->toArray());
    }

    public function test_form_undangan_manual_menambahkan_sumber_tambahan(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(FormUndangan::class)
            ->call('buka', $this->rapat->id)
            ->set('nama', 'I Ketut Tamu Tambahan')
            ->set('jabatan', 'Warga')
            ->set('instansi', 'Banjar Kangin')
            ->set('hp', '081999888777')
            ->set('sumber', 'tambahan')
            ->call('simpan')
            ->assertHasNoErrors();

        $undangan = Undangan::where('nama', 'I Ketut Tamu Tambahan')->first();
        $this->assertNotNull($undangan);
        $this->assertEquals(SumberUndangan::TAMBAHAN, $undangan->sumber);

        $this->assertDatabaseHas('audit_log', [
            'tabel_target' => 'undangan',
            'baris_id' => $undangan->id,
        ]);
    }

    public function test_modal_pilih_riwayat_undangan_menyalin_ke_rapat(): void
    {
        $this->actingAs($this->adminUser);

        // Buat data riwayat di rapat lain
        $rapatLama = app(RapatService::class)->buatRapat([
            'judul' => 'Rapat Lama',
            'tanggal' => '2026-09-01',
            'tempat' => 'Tempat',
        ], $this->adminProfil);

        Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $rapatLama->id,
            'nama' => 'I Wayan Tetap',
            'jabatan' => 'Kelian',
            'instansi' => 'Banjar',
            'hp' => '081234',
        ]);

        $repo = app(UndanganRepository::class);

        Livewire::test(ModalPilihRiwayatUndangan::class)
            ->call('buka', $this->rapat->id)
            ->call('toggleSemua')
            ->call('salinTerpilih')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('undangan', [
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Wayan Tetap',
            'sumber' => 'import',
        ]);
    }

    public function test_tabel_undangan_pencarian_dan_hapus(): void
    {
        $this->actingAs($this->adminUser);

        $u1 = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Ketut Suara',
            'jabatan' => 'Kaur',
            'urutan' => 1,
        ]);

        $u2 = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'nama' => 'Ni Wayan Sari',
            'jabatan' => 'Kasi',
            'urutan' => 2,
        ]);

        Livewire::test(TabelUndangan::class, ['rapatId' => $this->rapat->id])
            ->set('cari', 'Suara')
            ->assertSee('I Ketut Suara')
            ->assertDontSee('Ni Wayan Sari');

        // Hapus u1
        Livewire::test(TabelUndangan::class, ['rapatId' => $this->rapat->id])
            ->call('hapusUndangan', $u1->id)
            ->assertSee('berhasil dihapus');

        $this->assertDatabaseMissing('undangan', ['id' => $u1->id]);
    }

    public function test_tabel_undangan_menampilkan_tombol_unduh_kartu_hadir_jika_sudah_absen(): void
    {
        $this->actingAs($this->adminUser);

        $undangan = Undangan::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Made Hadir',
            'jabatan' => 'Kader',
            'urutan' => 1,
        ]);

        $kehadiran = Kehadiran::create([
            'id' => (string) Str::uuid(),
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $undangan->id,
            'ttd_path' => 'ttd/test.png',
            'jalur' => JalurCheckin::KIOSK,
            'waktu_perangkat' => now(),
            'dibatalkan' => false,
        ]);

        Livewire::test(TabelUndangan::class, ['rapatId' => $this->rapat->id])
            ->assertSee('I Made Hadir')
            ->assertSee(URL::signedRoute('kehadiran.bukti', ['id' => $kehadiran->id]));
    }
}
