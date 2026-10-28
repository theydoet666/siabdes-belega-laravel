<?php

namespace App\Services;

use App\Enums\JalurCheckin;
use App\Enums\StatusRapat;
use App\Enums\SumberUndangan;
use App\Models\Kehadiran;
use App\Models\Rapat;
use App\Models\Undangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckinService
{
    /**
     * Memproses absensi kehadiran secara idempoten dan thread-safe.
     */
    public function prosesCheckin(array $data): Kehadiran
    {
        $rawId = $data['id'] ?? null;
        $checkinId = (! empty($rawId) && Str::isUuid($rawId)) ? $rawId : (string) Str::uuid();
        $rapatId = $data['rapat_id'];

        // Cek apakah rekaman idempoten dengan ID ini sudah pernah tersimpan
        $eksisting = Kehadiran::with(['undangan', 'rapat'])->find($checkinId);
        if ($eksisting) {
            return $eksisting;
        }

        return DB::transaction(function () use ($data, $checkinId, $rapatId) {
            $rapat = Rapat::findOrFail($rapatId);

            // Validasi status rapat: tolak check-in jika rapat telah ditutup (kecuali operator/admin login)
            if ($rapat->status === StatusRapat::DITUTUP && ! Auth::check()) {
                throw new \DomainException('Absensi ditolak karena rapat telah resmi ditutup.');
            }

            // 1. Tangani pembuatan undangan tambahan baru jika peserta belum terdaftar di database
            $undanganId = $data['undangan_id'] ?? null;

            if (! empty($data['undangan_baru']) && is_array($data['undangan_baru'])) {
                if ($rapat->status === StatusRapat::DITUTUP) {
                    throw new \DomainException('Pendaftaran peserta baru ditolak karena rapat telah ditutup.');
                }

                $jumlahTambahan = Undangan::where('rapat_id', $rapatId)
                    ->where('sumber', SumberUndangan::TAMBAHAN)
                    ->count();

                if ($jumlahTambahan >= 100) {
                    throw new \DomainException('Batas maksimal peserta tambahan untuk rapat ini telah tercapai (maksimal 100).');
                }

                $baru = $data['undangan_baru'];
                $maxUrutan = (int) Undangan::where('rapat_id', $rapatId)->max('urutan');

                $undanganBaru = Undangan::firstOrCreate(
                    ['id' => (! empty($undanganId) && Str::isUuid($undanganId)) ? $undanganId : (string) Str::uuid()],
                    [
                        'rapat_id' => $rapatId,
                        'nama' => trim($baru['nama'] ?? 'Peserta Tambahan'),
                        'jabatan' => trim($baru['jabatan'] ?? ''),
                        'instansi' => trim($baru['instansi'] ?? ''),
                        'hp' => trim($baru['hp'] ?? ''),
                        'sumber' => SumberUndangan::TAMBAHAN,
                        'urutan' => $maxUrutan + 1,
                    ]
                );
                $undanganId = $undanganBaru->id;
            }

            // Pastikan undangan valid dan benar-benar terdaftar pada rapat ini
            $undangan = Undangan::where('id', $undanganId)
                ->where('rapat_id', $rapatId)
                ->firstOrFail();

            // Cek apakah undangan ini sudah pernah check-in di rapat ini (hindari duplikasi)
            $kehadiranEksisting = Kehadiran::with(['undangan', 'rapat'])
                ->where('rapat_id', $rapatId)
                ->where('undangan_id', $undangan->id)
                ->where('dibatalkan', false)
                ->first();

            if ($kehadiranEksisting) {
                return $kehadiranEksisting;
            }

            // 2. Simpan file tanda tangan ke Laravel Storage (disk public)
            $ttdPath = "ttd/{$rapatId}/{$checkinId}.png";
            $this->simpanBase64KeStorage($data['ttd'] ?? '', $ttdPath);

            // 3. Simpan file foto wajah ke Laravel Storage jika ada
            $fotoPath = null;
            if (! empty($data['foto'])) {
                $fotoPath = "foto/{$rapatId}/{$checkinId}.jpg";
                $this->simpanBase64KeStorage($data['foto'], $fotoPath);
            }

            // 4. Buat record kehadiran
            $jalurEnum = match ($data['jalur'] ?? 'kiosk') {
                'mandiri' => JalurCheckin::MANDIRI,
                'operator' => JalurCheckin::OPERATOR,
                default => JalurCheckin::KIOSK,
            };

            $waktuPerangkat = ! empty($data['waktu_perangkat'])
                ? Carbon::parse($data['waktu_perangkat'])
                : now();

            $kehadiran = Kehadiran::create([
                'id' => $checkinId,
                'rapat_id' => $rapatId,
                'undangan_id' => $undangan->id,
                'ttd_path' => $ttdPath,
                'foto_path' => $fotoPath,
                'jalur' => $jalurEnum,
                'perangkat_id' => $data['perangkat_id'] ?? null,
                'waktu_perangkat' => $waktuPerangkat,
                'diwakili_oleh' => $data['diwakili_oleh'] ?? null,
                'dibatalkan' => false,
            ]);

            return $kehadiran->load(['undangan', 'rapat']);
        });
    }

    /**
     * Konversi data base64 ke binary, validasi format gambar dengan getimagesizefromstring,
     * re-encode menggunakan GD, dan simpan ke disk public.
     *
     * @throws \InvalidArgumentException jika format bukan PNG atau JPEG yang valid
     */
    protected function simpanBase64KeStorage(string $base64Data, string $storagePath): bool
    {
        if (empty($base64Data)) {
            return false;
        }

        // Hapus header data URL (misal data:image/png;base64,...)
        if (str_contains($base64Data, ';base64,')) {
            [, $base64Data] = explode(';base64,', $base64Data);
        }

        $binary = base64_decode($base64Data, true);
        if ($binary === false || empty($binary)) {
            throw new \InvalidArgumentException('Format encoding base64 tidak valid.');
        }

        // Validasi isi biner dengan getimagesizefromstring
        $info = @getimagesizefromstring($binary);
        if ($info === false || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG], true)) {
            throw new \InvalidArgumentException('Berkas bukan gambar PNG atau JPEG yang valid.');
        }

        // Re-encode dengan GD untuk membuang metadata atau payload berbahaya
        $image = @imagecreatefromstring($binary);
        if ($image !== false) {
            ob_start();
            if ($info[2] === IMAGETYPE_PNG) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
                imagepng($image);
            } else {
                imagejpeg($image, null, 85);
            }
            $cleanBinary = ob_get_clean();
            imagedestroy($image);

            if ($cleanBinary !== false) {
                $binary = $cleanBinary;
            }
        }

        return Storage::disk('local')->put($storagePath, $binary);
    }
}
