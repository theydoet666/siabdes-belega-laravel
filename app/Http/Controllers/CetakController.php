<?php

namespace App\Http\Controllers;

use App\Exports\RekapKehadiranExport;
use App\Models\PengaturanSistem;
use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CetakController extends Controller
{
    /**
     * Tampilkan halaman cetak Lembar A4 Daftar Hadir Rapat
     */
    public function a4(string $id): View
    {
        $rapat = Rapat::with([
            'undangan' => function ($query) {
                $query->orderBy('urutan', 'asc')
                      ->orderBy('dibuat_pada', 'asc')
                      ->with(['kehadiran' => function ($kq) {
                          $kq->where('dibatalkan', false);
                      }]);
            },
        ])->findOrFail($id);

        $pengaturan = PengaturanSistem::first() ?? new PengaturanSistem([
            'nama_sistem' => 'SIABDES Belega',
            'subjudul_sistem' => 'Sistem Absensi Rapat Kantor Desa Belega',
            'nama_desa' => 'Pemerintah Desa Belega',
            'alamat_desa' => 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581',
            'kecamatan' => 'Kecamatan Blahbatuh',
            'kabupaten' => 'Kabupaten Gianyar',
            'provinsi' => 'Bali',
        ]);

        $totalUndangan = $rapat->undangan->count();
        $totalHadir = $rapat->undangan->filter(function ($u) {
            return $u->kehadiran->isNotEmpty();
        })->count();

        $now = Carbon::now('Asia/Makassar');
        $mapHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $mapBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaHariCetak = $mapHari[$now->format('l')] ?? $now->format('l');
        $namaBulanCetak = $mapBulan[(int) $now->format('n')] ?? $now->format('F');
        $waktuCetak = "{$namaHariCetak}, {$now->format('j')} {$namaBulanCetak} {$now->format('Y')} - {$now->format('H:i')} WITA";
        $waktuCetakSingkat = "{$now->format('j')} {$namaBulanCetak} {$now->format('Y')}, {$now->format('H.i')} WITA";

        // URL publik untuk verifikasi keabsahan dokumen
        $urlVerifikasi = route('dokumen.verifikasi', $rapat->kode);
        $qrVerifikasiSvg = null;
        try {
            $qrVerifikasiSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(70)
                ->margin(0)
                ->generate($urlVerifikasi);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal generate QR Verifikasi Cetak: ' . $e->getMessage());
        }

        return view('cetak.lembar-a4', [
            'rapat' => $rapat,
            'pengaturan' => $pengaturan,
            'daftarUndangan' => $rapat->undangan,
            'totalUndangan' => $totalUndangan,
            'totalHadir' => $totalHadir,
            'waktuCetak' => $waktuCetak,
            'waktuCetakSingkat' => $waktuCetakSingkat,
            'urlVerifikasi' => $urlVerifikasi,
            'qrVerifikasiSvg' => $qrVerifikasiSvg,
        ]);
    }

    /**
     * Ekspor rekap kehadiran rapat ke format Excel (.xlsx)
     */
    public function eksporExcel(string $id): BinaryFileResponse
    {
        $rapat = Rapat::findOrFail($id);
        
        $tanggalFormatted = $rapat->tanggal ? $rapat->tanggal->format('Y-m-d') : date('Y-m-d');
        $namaFile = "Rekap_Kehadiran_{$rapat->kode}_{$tanggalFormatted}.xlsx";

        return Excel::download(new RekapKehadiranExport($rapat->id), $namaFile);
    }
}
