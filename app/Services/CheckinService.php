<?php

namespace App\Services;

use App\Enums\JalurCheckin;
use App\Enums\SumberUndangan;
use App\Models\Kehadiran;
use App\Models\Rapat;
use App\Models\Undangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CheckinService
{
    /**
     * Memproses absensi kehadiran secara idempoten dan thread-safe.
     *
     * @param array $data
     * @return Kehadiran
     */
    public function prosesCheckin(array $data): Kehadiran
    {
        $checkinId = $data['id'] ?? (string) Str::uuid();
        $rapatId = $data['rapat_id'];

        // Cek apakah rekaman idempoten dengan ID ini sudah pernah tersimpan
        $eksisting = Kehadiran::with(['undangan', 'rapat'])->find($checkinId);
        if ($eksisting) {
            return $eksisting;
        }

        return DB::transaction(function () use ($data, $checkinId, $rapatId) {
            // 1. Tangani pembuatan undangan tambahan baru jika peserta belum terdaftar di database
            $undanganId = $data['undangan_id'] ?? null;

            if (!empty($data['undangan_baru']) && is_array($data['undangan_baru'])) {
                $baru = $data['undangan_baru'];
                $maxUrutan = (int) Undangan::where('rapat_id', $rapatId)->max('urutan');

                $undanganBaru = Undangan::firstOrCreate(
                    ['id' => $undanganId ?: (string) Str::uuid()],
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

            // Pastikan undangan valid
            $undangan = Undangan::findOrFail($undanganId);

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
            if (!empty($data['foto'])) {
                $fotoPath = "foto/{$rapatId}/{$checkinId}.jpg";
                $this->simpanBase64KeStorage($data['foto'], $fotoPath);
            }

            // 4. Buat record kehadiran
            $jalurEnum = match ($data['jalur'] ?? 'kiosk') {
                'mandiri' => JalurCheckin::MANDIRI,
                'operator' => JalurCheckin::OPERATOR,
                default => JalurCheckin::KIOSK,
            };

            $waktuPerangkat = !empty($data['waktu_perangkat'])
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
     * Konversi data base64 ke binary dan simpan ke disk public.
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

        $binary = base64_decode($base64Data);
        if ($binary === false) {
            return false;
        }

        return Storage::disk('public')->put($storagePath, $binary);
    }
}
