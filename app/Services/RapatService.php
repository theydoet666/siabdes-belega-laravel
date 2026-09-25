<?php

namespace App\Services;

use App\Enums\StatusRapat;
use App\Enums\PeranPengguna;
use App\Models\AuditLog;
use App\Models\Profil;
use App\Models\Rapat;
use App\Models\Undangan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapatService
{
    public function __construct(
        protected KodeRapatService $kodeRapatService
    ) {}

    /**
     * Membuat rapat baru.
     *
     * @param array $data
     * @param Profil $aktor
     * @return Rapat
     */
    public function buatRapat(array $data, Profil $aktor): Rapat
    {
        return DB::transaction(function () use ($data, $aktor) {
            $kode = $this->kodeRapatService->generate();

            $pinKioskHash = !empty($data['pin_kiosk'])
                ? Hash::make($data['pin_kiosk'])
                : null;

            $rapat = Rapat::create([
                'id' => (string) Str::uuid(),
                'kode' => $kode,
                'judul' => $data['judul'],
                'tanggal' => $data['tanggal'],
                'jam_mulai' => $data['jam_mulai'] ?? null,
                'jam_selesai' => $data['jam_selesai'] ?? null,
                'tempat' => $data['tempat'],
                'penyelenggara' => $data['penyelenggara'] ?? 'Pemerintah Desa Belega',
                'catatan' => $data['catatan'] ?? null,
                'status' => StatusRapat::DRAFT,
                'pin_kiosk' => $pinKioskHash,
                'retensi_hari' => $data['retensi_hari'] ?? 90,
                'verifikator_tampil' => $data['verifikator_tampil'] ?? true,
                'verifikator_label' => $data['verifikator_label'] ?? 'Telah dilakukan verifikasi',
                'verifikator_jabatan' => $data['verifikator_jabatan'] ?? 'Sekretaris Desa',
                'verifikator_nama' => $data['verifikator_nama'] ?? 'Gusti Ketut Amertayasa, S.M',
                'verifikator_nip' => $data['verifikator_nip'] ?? '',
                'pelaksana_tampil' => $data['pelaksana_tampil'] ?? true,
                'pelaksana_jabatan' => $data['pelaksana_jabatan'] ?? 'Kasi Pelayanan',
                'pelaksana_nama' => $data['pelaksana_nama'] ?? 'Ni Made Arini',
                'pelaksana_nip' => $data['pelaksana_nip'] ?? '',
                'mengetahui_tampil' => $data['mengetahui_tampil'] ?? true,
                'mengetahui_label' => $data['mengetahui_label'] ?? 'Mengetahui :',
                'penandatangan_nama' => $data['penandatangan_nama'] ?? 'I WAYAN SUDARSANA, S.Sos.',
                'penandatangan_jabatan' => $data['penandatangan_jabatan'] ?? 'Perbekel Belega',
                'penandatangan_nip' => $data['penandatangan_nip'] ?? '',
                'penandatangan_lokasi' => $data['penandatangan_lokasi'] ?? 'Belega',
                'dibuat_oleh' => $aktor->id,
            ]);

            AuditLog::create([
                'aktor' => $aktor->id,
                'aksi' => "Membuat rapat baru: {$rapat->judul} (Kode: {$rapat->kode})",
                'tabel_target' => 'rapat',
                'baris_id' => $rapat->id,
                'rincian' => [
                    'kode' => $rapat->kode,
                    'judul' => $rapat->judul,
                    'tanggal' => $rapat->tanggal->format('Y-m-d'),
                    'tempat' => $rapat->tempat,
                ],
            ]);

            return $rapat;
        });
    }

    /**
     * Menduplikasi rapat.
     *
     * @param Rapat $rapatAsal
     * @param string $judulBaru
     * @param bool $salinUndangan
     * @param Profil $aktor
     * @return Rapat
     */
    public function duplikasiRapat(
        Rapat $rapatAsal,
        string $judulBaru,
        bool $salinUndangan,
        Profil $aktor
    ): Rapat {
        return DB::transaction(function () use ($rapatAsal, $judulBaru, $salinUndangan, $aktor) {
            $kodeBaru = $this->kodeRapatService->generate();

            $rapatBaru = Rapat::create([
                'id' => (string) Str::uuid(),
                'kode' => $kodeBaru,
                'judul' => $judulBaru,
                'tanggal' => $rapatAsal->tanggal,
                'jam_mulai' => $rapatAsal->jam_mulai,
                'jam_selesai' => $rapatAsal->jam_selesai,
                'tempat' => $rapatAsal->tempat,
                'penyelenggara' => $rapatAsal->penyelenggara,
                'catatan' => $rapatAsal->catatan,
                'status' => StatusRapat::DRAFT,
                'pin_kiosk' => $rapatAsal->pin_kiosk,
                'retensi_hari' => $rapatAsal->retensi_hari,
                'verifikator_tampil' => $rapatAsal->verifikator_tampil ?? true,
                'verifikator_label' => $rapatAsal->verifikator_label ?? 'Telah dilakukan verifikasi',
                'verifikator_jabatan' => $rapatAsal->verifikator_jabatan ?? 'Sekretaris Desa',
                'verifikator_nama' => $rapatAsal->verifikator_nama ?? 'Gusti Ketut Amertayasa, S.M',
                'verifikator_nip' => $rapatAsal->verifikator_nip ?? '',
                'pelaksana_tampil' => $rapatAsal->pelaksana_tampil ?? true,
                'pelaksana_jabatan' => $rapatAsal->pelaksana_jabatan ?? 'Kasi Pelayanan',
                'pelaksana_nama' => $rapatAsal->pelaksana_nama ?? 'Ni Made Arini',
                'pelaksana_nip' => $rapatAsal->pelaksana_nip ?? '',
                'mengetahui_tampil' => $rapatAsal->mengetahui_tampil ?? true,
                'mengetahui_label' => $rapatAsal->mengetahui_label ?? 'Mengetahui :',
                'penandatangan_nama' => $rapatAsal->penandatangan_nama,
                'penandatangan_jabatan' => $rapatAsal->penandatangan_jabatan,
                'penandatangan_nip' => $rapatAsal->penandatangan_nip,
                'penandatangan_lokasi' => $rapatAsal->penandatangan_lokasi,
                'dibuat_oleh' => $aktor->id,
            ]);

            $jumlahUndanganDisalin = 0;

            if ($salinUndangan) {
                $daftarUndanganAsal = $rapatAsal->undangan()->orderBy('urutan')->get();
                foreach ($daftarUndanganAsal as $undangan) {
                    Undangan::create([
                        'id' => (string) Str::uuid(),
                        'rapat_id' => $rapatBaru->id,
                        'nama' => $undangan->nama,
                        'jabatan' => $undangan->jabatan,
                        'instansi' => $undangan->instansi,
                        'hp' => $undangan->hp,
                        'sumber' => $undangan->sumber,
                        'urutan' => $undangan->urutan,
                    ]);
                    $jumlahUndanganDisalin++;
                }
            }

            AuditLog::create([
                'aktor' => $aktor->id,
                'aksi' => "Menduplikasi rapat '{$rapatAsal->judul}' menjadi '{$rapatBaru->judul}' (Kode: {$rapatBaru->kode})",
                'tabel_target' => 'rapat',
                'baris_id' => $rapatBaru->id,
                'rincian' => [
                    'rapat_asal_id' => $rapatAsal->id,
                    'kode_asal' => $rapatAsal->kode,
                    'kode_baru' => $rapatBaru->kode,
                    'salin_undangan' => $salinUndangan,
                    'jumlah_undangan_disalin' => $jumlahUndanganDisalin,
                ],
            ]);

            return $rapatBaru;
        });
    }

    /**
     * Menghapus rapat secara permanen (khusus admin).
     * Menghapus data rapat (cascade ke undangan dan kehadiran) serta file fisik di storage.
     *
     * @param Rapat $rapat
     * @param Profil $aktor
     * @return bool
     */
    public function hapusRapat(Rapat $rapat, Profil $aktor): bool
    {
        // Kumpulkan path file tanda tangan dan foto sebelum baris kehadiran dihapus cascade
        $daftarKehadiran = $rapat->kehadiran()->get(['ttd_path', 'foto_path']);
        $filePaths = [];

        foreach ($daftarKehadiran as $k) {
            if (!empty($k->ttd_path)) {
                $filePaths[] = $k->ttd_path;
            }
            if (!empty($k->foto_path)) {
                $filePaths[] = $k->foto_path;
            }
        }

        $judulRapat = $rapat->judul;
        $kodeRapat = $rapat->kode;
        $rapatId = $rapat->id;

        return DB::transaction(function () use ($rapat, $filePaths, $judulRapat, $kodeRapat, $rapatId, $aktor) {
            AuditLog::create([
                'aktor' => $aktor->id,
                'aksi' => "Menghapus rapat permanen: {$judulRapat} (Kode: {$kodeRapat})",
                'tabel_target' => 'rapat',
                'baris_id' => $rapatId,
                'rincian' => [
                    'kode' => $kodeRapat,
                    'judul' => $judulRapat,
                    'jumlah_file_dihapus' => count($filePaths),
                ],
            ]);

            // Hapus baris rapat (MySQL cascade delete akan menghapus undangan & kehadiran)
            $rapat->delete();

            // Hapus file fisik di storage SETELAH transaksi database sukses commit
            DB::afterCommit(function () use ($filePaths) {
                if (!empty($filePaths)) {
                    foreach ($filePaths as $path) {
                        // Cek dan hapus dari disk public atau disk default
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        } elseif (Storage::exists($path)) {
                            Storage::delete($path);
                        }
                    }
                }
            });

            return true;
        });
    }
}
