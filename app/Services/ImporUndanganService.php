<?php

namespace App\Services;

use App\Enums\SumberUndangan;
use App\Models\AuditLog;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImporUndanganService
{
    /**
     * Membaca dan memvalidasi berkas CSV / Excel untuk rapat tertentu.
     *
     * @param UploadedFile|string $file
     * @param string $rapatId
     * @return array
     */
    public function bacaDanValidasi($file, string $rapatId): array
    {
        // Baca file menggunakan Excel::toArray
        $data = Excel::toArray(new class {}, $file);

        if (empty($data) || empty($data[0])) {
            return [
                'total' => 0,
                'total_valid' => 0,
                'total_duplikat' => 0,
                'total_invalid' => 0,
                'baris' => [],
            ];
        }

        $rows = $data[0];

        // Ambil daftar nama yang sudah ada di rapat aktif untuk cek duplikasi
        $undanganEksis = Undangan::where('rapat_id', $rapatId)
            ->get(['nama'])
            ->map(fn ($u) => $this->normalisasiNama($u->nama))
            ->toArray();

        $eksisLookup = array_fill_keys($undanganEksis, true);
        $fileLookup = [];

        // Deteksi header kolom pada baris pertama
        $headerMap = $this->deteksiHeader($rows[0]);
        $mulaiBaris = $headerMap['ada_header'] ? 1 : 0;

        $hasil = [];
        $countValid = 0;
        $countDuplikat = 0;
        $countInvalid = 0;

        for ($i = $mulaiBaris; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Abaikan baris yang seluruh kolomnya kosong
            if ($this->isBarisKosong($row)) {
                continue;
            }

            $nama = trim((string) ($row[$headerMap['nama']] ?? ''));
            $jabatan = trim((string) ($row[$headerMap['jabatan']] ?? ''));
            $instansi = trim((string) ($row[$headerMap['instansi']] ?? ''));
            $hp = trim((string) ($row[$headerMap['hp']] ?? ''));

            $nomorBaris = $i + 1;
            $namaNorm = $this->normalisasiNama($nama);

            if (empty($nama)) {
                $status = 'invalid';
                $pesan = 'Nama undangan wajib diisi.';
                $countInvalid++;
            } elseif (isset($fileLookup[$namaNorm])) {
                $status = 'duplikat';
                $pesan = "Duplikat di dalam berkas (sama dengan baris #{$fileLookup[$namaNorm]}).";
                $countDuplikat++;
            } elseif (isset($eksisLookup[$namaNorm])) {
                $status = 'duplikat';
                $pesan = 'Nama ini sudah terdaftar sebelumnya di rapat ini.';
                $countDuplikat++;
            } else {
                $status = 'valid';
                $pesan = 'Siap diimpor.';
                $countValid++;
                $fileLookup[$namaNorm] = $nomorBaris;
            }

            $hasil[] = [
                'nomor' => $nomorBaris,
                'nama' => $nama,
                'jabatan' => $jabatan,
                'instansi' => $instansi,
                'hp' => $hp,
                'status' => $status,
                'pesan' => $pesan,
            ];
        }

        return [
            'total' => count($hasil),
            'total_valid' => $countValid,
            'total_duplikat' => $countDuplikat,
            'total_invalid' => $countInvalid,
            'baris' => $hasil,
        ];
    }

    /**
     * Menyimpan baris-baris valid hasil parsing ke tabel undangan.
     *
     * @param string $rapatId
     * @param array $barisData
     * @param bool $abaikanDuplikat
     * @param Profil|null $aktor
     * @return int Jumlah baris yang berhasil diimpor
     */
    public function simpan(
        string $rapatId,
        array $barisData,
        bool $abaikanDuplikat = true,
        ?Profil $aktor = null
    ): int {
        return DB::transaction(function () use ($rapatId, $barisData, $abaikanDuplikat, $aktor) {
            $maxUrutan = (int) Undangan::where('rapat_id', $rapatId)->max('urutan');
            $disimpan = 0;

            foreach ($barisData as $item) {
                $status = $item['status'] ?? 'valid';

                if ($status === 'invalid') {
                    continue;
                }

                if ($status === 'duplikat' && $abaikanDuplikat) {
                    continue;
                }

                $maxUrutan++;

                Undangan::create([
                    'id' => (string) Str::uuid(),
                    'rapat_id' => $rapatId,
                    'nama' => trim($item['nama']),
                    'jabatan' => trim($item['jabatan'] ?? ''),
                    'instansi' => trim($item['instansi'] ?? ''),
                    'hp' => trim($item['hp'] ?? ''),
                    'sumber' => SumberUndangan::IMPORT,
                    'urutan' => $maxUrutan,
                ]);

                $disimpan++;
            }

            if ($disimpan > 0) {
                $rapat = Rapat::find($rapatId);
                AuditLog::create([
                    'aktor' => $aktor?->id ?? auth()->user()?->profil?->id,
                    'aksi' => "Mengimpor {$disimpan} undangan ke rapat '{$rapat?->judul}'",
                    'tabel_target' => 'undangan',
                    'baris_id' => $rapatId,
                    'rincian' => [
                        'rapat_id' => $rapatId,
                        'jumlah_diimpor' => $disimpan,
                        'abaikan_duplikat' => $abaikanDuplikat,
                    ],
                ]);
            }

            return $disimpan;
        });
    }

    /**
     * Normalisasi string nama untuk perbandingan duplikasi (case-insensitive & tanda baca).
     */
    public function normalisasiNama(string $nama): string
    {
        $bersih = preg_replace('/[^a-zA-Z0-9 ]/u', ' ', $nama);
        $bersih = preg_replace('/\s+/', ' ', $bersih);
        return mb_strtolower(trim($bersih));
    }

    /**
     * Mendeteksi letak index kolom berdasarkan kata kunci header.
     */
    protected function deteksiHeader(array $firstRow): array
    {
        $namaIndex = 0;
        $jabatanIndex = 1;
        $instansiIndex = 2;
        $hpIndex = 3;
        $adaHeader = false;

        $aliasesNama = ['nama', 'nama lengkap', 'nama undangan', 'name', 'full name', 'peserta', 'nama peserta'];
        $aliasesJabatan = ['jabatan', 'posisi', 'title', 'position', 'pekerjaan'];
        $aliasesInstansi = ['instansi', 'lembaga', 'organisasi', 'instansi asal', 'banjar', 'organization', 'institution', 'dinas'];
        $aliasesHp = ['hp', 'no hp', 'nomor hp', 'telepon', 'no telepon', 'phone', 'handphone', 'wa', 'no wa', 'nomor wa'];

        foreach ($firstRow as $idx => $val) {
            $valClean = mb_strtolower(trim((string) $val));

            if (in_array($valClean, $aliasesNama, true)) {
                $namaIndex = $idx;
                $adaHeader = true;
            } elseif (in_array($valClean, $aliasesJabatan, true)) {
                $jabatanIndex = $idx;
                $adaHeader = true;
            } elseif (in_array($valClean, $aliasesInstansi, true)) {
                $instansiIndex = $idx;
                $adaHeader = true;
            } elseif (in_array($valClean, $aliasesHp, true)) {
                $hpIndex = $idx;
                $adaHeader = true;
            }
        }

        return [
            'ada_header' => $adaHeader,
            'nama' => $namaIndex,
            'jabatan' => $jabatanIndex,
            'instansi' => $instansiIndex,
            'hp' => $hpIndex,
        ];
    }

    /**
     * Mengecek apakah sebuah baris kosong sepenuhnya.
     */
    protected function isBarisKosong(array $row): bool
    {
        foreach ($row as $val) {
            if (trim((string) $val) !== '') {
                return false;
            }
        }
        return true;
    }
}
