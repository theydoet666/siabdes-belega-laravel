<?php

namespace Tests\Feature;

use App\Enums\JalurCheckin;
use App\Enums\PeranPengguna;
use App\Enums\StatusRapat;
use App\Enums\SumberUndangan;
use App\Exports\RekapKehadiranExport;
use App\Models\Kehadiran;
use App\Models\PengaturanSistem;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ModulCetakDanEksporTest extends TestCase
{
    use RefreshDatabase;

    protected User $operatorUser;
    protected Profil $operatorProfil;
    protected Rapat $rapat;

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

        $this->operatorUser = User::factory()->create();
        $this->operatorProfil = Profil::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $this->operatorUser->id,
            'nama' => 'Operator Belega',
            'email' => $this->operatorUser->email,
            'peran' => PeranPengguna::OPERATOR,
            'aktif' => true,
        ]);

        $this->rapat = Rapat::create([
            'kode' => 'BLG9',
            'judul' => 'Musyawarah Perencanaan Pembangunan Desa (Musrenbangdes)',
            'tanggal' => Carbon::parse('2026-10-15'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'tempat' => 'Balai Banjar Belega Kangin',
            'penyelenggara' => 'Pemerintah Desa Belega',
            'catatan' => 'Pembahasan RKPDes Tahun Anggaran 2027',
            'status' => StatusRapat::DIBUKA,
            'pin_kiosk' => bcrypt('123456'),
            'retensi_hari' => 90,
            'penandatangan_nama' => 'I WAYAN SUDARSANA, S.Sos.',
            'penandatangan_jabatan' => 'Perbekel Belega',
            'penandatangan_nip' => '197508122000031001',
            'penandatangan_lokasi' => 'Belega',
            'dibuat_oleh' => $this->operatorProfil->id,
        ]);
    }

    public function test_tamu_tidak_bisa_mengakses_halaman_cetak(): void
    {
        $response = $this->get(route('rapat.cetak', $this->rapat->id));
        $response->assertRedirect(route('login'));
    }

    public function test_operator_dapat_melihat_lembar_cetak_a4_dengan_kop_dan_3_penandatangan(): void
    {
        // Buat 16 peserta: 12 resmi (import), 4 tambahan di tempat
        for ($i = 1; $i <= 12; $i++) {
            $undangan = Undangan::create([
                'rapat_id' => $this->rapat->id,
                'nama' => "*Peserta Resmi Ke-{$i}*",
                'jabatan' => "Kelian Banjar {$i}",
                'instansi' => 'Banjar Dinas',
                'hp' => "0812345678{$i}",
                'sumber' => SumberUndangan::IMPORT,
                'urutan' => $i,
            ]);

            // Set 8 diantaranya hadir
            if ($i <= 8) {
                Kehadiran::create([
                    'rapat_id' => $this->rapat->id,
                    'undangan_id' => $undangan->id,
                    'ttd_path' => 'ttd/sample-ttd.png',
                    'jalur' => JalurCheckin::KIOSK,
                    'waktu_perangkat' => Carbon::now('Asia/Makassar'),
                    'dibatalkan' => false,
                ]);
            }
        }

        // 4 peserta tambahan
        for ($j = 1; $j <= 4; $j++) {
            $undanganTambahan = Undangan::create([
                'rapat_id' => $this->rapat->id,
                'nama' => "~Tamu Tambahan {$j}~",
                'jabatan' => 'Tokoh Masyarakat',
                'instansi' => 'Warga',
                'hp' => "0898765432{$j}",
                'sumber' => SumberUndangan::TAMBAHAN,
                'urutan' => 12 + $j,
            ]);

            Kehadiran::create([
                'rapat_id' => $this->rapat->id,
                'undangan_id' => $undanganTambahan->id,
                'ttd_path' => 'ttd/sample-ttd-mandiri.png',
                'jalur' => JalurCheckin::MANDIRI,
                'waktu_perangkat' => Carbon::now('Asia/Makassar'),
                'dibatalkan' => false,
            ]);
        }

        $response = $this->actingAs($this->operatorUser)
            ->get(route('rapat.cetak', $this->rapat->id));

        $response->assertOk();
        $response->assertViewIs('cetak.lembar-a4');

        // Pastikan Kop Dinas muncul
        $response->assertSee('PEMERINTAH KABUPATEN GIANYAR');
        $response->assertSee('KECAMATAN BLAHBATUH');
        $response->assertSee('PEMERINTAH DESA BELEGA');

        // Pastikan Judul & Metadata Rapat
        $response->assertSee('DAFTAR HADIR');
        $response->assertSee('Musyawarah Perencanaan Pembangunan Desa (Musrenbangdes)');
        $response->assertSee('Balai Banjar Belega Kangin');

        // Pastikan nama dibersihkan dari simbol * dan ~
        $response->assertSee('Peserta Resmi Ke-1');
        $response->assertDontSee('*Peserta Resmi Ke-1*');
        $response->assertSee('Tamu Tambahan 1');
        $response->assertDontSee('~Tamu Tambahan 1~');

        // Pastikan Blok Pengesahan 3 Pihak Muncul
        $response->assertSee('Telah dilakukan verifikasi');
        $response->assertSee('Sekretaris Desa');
        $response->assertSee('Perbekel Belega');
        $response->assertSee('I WAYAN SUDARSANA, S.Sos.');
        $response->assertSee('197508122000031001');

        // Pastikan statistik kehadiran benar (8 + 4 = 12 hadir dari 16 undangan)
        $response->assertSee('12');
        $response->assertSee('16');
    }

    public function test_ekspor_excel_menggunakan_rekap_kehadiran_export(): void
    {
        Excel::fake();

        $response = $this->actingAs($this->operatorUser)
            ->get(route('rapat.ekspor-excel', $this->rapat->id));

        $response->assertOk();

        Excel::assertDownloaded("Rekap_Kehadiran_{$this->rapat->kode}_2026-10-15.xlsx", function (RekapKehadiranExport $export) {
            return true;
        });
    }

    public function test_rekap_kehadiran_export_mapping_dan_headings(): void
    {
        // Buat 2 peserta: 1 hadir dengan diwakili, 1 belum hadir
        $undangan1 = Undangan::create([
            'rapat_id' => $this->rapat->id,
            'nama' => '*I Made Suardana*',
            'jabatan' => 'Kelian Banjar',
            'instansi' => 'Banjar Kangin',
            'hp' => '081234567890',
            'sumber' => SumberUndangan::IMPORT,
            'urutan' => 1,
        ]);

        Kehadiran::create([
            'rapat_id' => $this->rapat->id,
            'undangan_id' => $undangan1->id,
            'ttd_path' => 'ttd/sample-ttd-1.png',
            'jalur' => JalurCheckin::KIOSK,
            'waktu_perangkat' => Carbon::parse('2026-10-15 09:15:00', 'Asia/Makassar'),
            'diwakili_oleh' => '*Ni Luh Putu (Istri)*',
            'dibatalkan' => false,
        ]);

        $undangan2 = Undangan::create([
            'rapat_id' => $this->rapat->id,
            'nama' => 'I Ketut Wijaya',
            'jabatan' => 'Ketua LPM',
            'instansi' => 'Desa Belega',
            'hp' => '081234567891',
            'sumber' => SumberUndangan::TAMBAHAN,
            'urutan' => 2,
        ]);

        $export = new RekapKehadiranExport($this->rapat->id);
        $headings = $export->headings();

        $this->assertCount(10, $headings);
        $this->assertEquals('No.', $headings[0]);
        $this->assertEquals('Nama Peserta', $headings[1]);
        $this->assertEquals('Status Kehadiran', $headings[5]);

        $collection = $export->collection();
        $this->assertCount(2, $collection);

        $row1 = $export->map($collection[0]);
        $this->assertEquals(1, $row1[0]);
        $this->assertEquals('I Made Suardana', $row1[1]); // Asterisk cleaned
        $this->assertEquals('HADIR', $row1[5]);
        $this->assertEquals('KIOSK', $row1[7]);
        $this->assertStringContainsString('Ni Luh Putu (Istri)', $row1[8]);
        $this->assertEquals('Daftar Undangan Resmi', $row1[9]);

        $row2 = $export->map($collection[1]);
        $this->assertEquals(2, $row2[0]);
        $this->assertEquals('I Ketut Wijaya', $row2[1]);
        $this->assertEquals('BELUM HADIR', $row2[5]);
        $this->assertEquals('-', $row2[6]);
        $this->assertEquals('Tambahan di Tempat', $row2[9]);
    }
}
