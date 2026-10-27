<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSistem;
use App\Models\Rapat;
use Carbon\Carbon;
use Illuminate\View\View;

class VerifikasiDokumenController extends Controller
{
    /**
     * Menampilkan halaman publik verifikasi keabsahan dokumen rapat (/v/{kode}).
     */
    public function show(string $kode): View
    {
        $rapat = Rapat::where('kode', $kode)
            ->with([
                'undangan' => function ($query) {
                    $query->orderBy('urutan', 'asc')
                          ->with(['kehadiran' => function ($kq) {
                              $kq->where('dibatalkan', false);
                          }]);
                },
                'pembuat',
            ])
            ->firstOrFail();

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

        $persentaseHadir = $totalUndangan > 0 ? round(($totalHadir / $totalUndangan) * 100, 1) : 0;

        $mapHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $mapBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $hariRapat = $rapat->tanggal ? ($mapHari[$rapat->tanggal->format('l')] ?? $rapat->tanggal->format('l')) : '-';
        $bulanRapat = $rapat->tanggal ? ($mapBulan[(int) $rapat->tanggal->format('n')] ?? $rapat->tanggal->format('F')) : '-';
        $tanggalRapatFormatted = $rapat->tanggal ? "{$hariRapat}, {$rapat->tanggal->format('j')} {$bulanRapat} {$rapat->tanggal->format('Y')}" : '-';

        $jamMulai = $rapat->jam_mulai ? date('H:i', strtotime($rapat->jam_mulai)) : null;
        $jamSelesai = $rapat->jam_selesai ? date('H:i', strtotime($rapat->jam_selesai)) : null;
        $waktuRapatFormatted = $jamMulai && $jamSelesai ? "{$jamMulai} - {$jamSelesai} WITA" : ($jamMulai ? "{$jamMulai} WITA - Selesai" : '-');

        $now = Carbon::now('Asia/Makassar');
        $namaHariNow = $mapHari[$now->format('l')] ?? $now->format('l');
        $namaBulanNow = $mapBulan[(int) $now->format('n')] ?? $now->format('F');
        $waktuVerifikasi = "{$namaHariNow}, {$now->format('j')} {$namaBulanNow} {$now->format('Y')} pukul {$now->format('H:i:s')} WITA";

        return view('verifikasi.dokumen', [
            'rapat' => $rapat,
            'pengaturan' => $pengaturan,
            'totalUndangan' => $totalUndangan,
            'totalHadir' => $totalHadir,
            'persentaseHadir' => $persentaseHadir,
            'tanggalRapatFormatted' => $tanggalRapatFormatted,
            'waktuRapatFormatted' => $waktuRapatFormatted,
            'waktuVerifikasi' => $waktuVerifikasi,
        ]);
    }
}
