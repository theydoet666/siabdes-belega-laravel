<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BuktiKehadiranController extends Controller
{
    /**
     * Menampilkan Kartu Bukti Hadir Digital Pribadi (/bukti/{id}).
     */
    public function show(string $id): View
    {
        $kehadiran = Kehadiran::with(['undangan', 'rapat'])->findOrFail($id);

        $pengaturan = PengaturanSistem::first() ?? new PengaturanSistem([
            'nama_sistem' => 'SIABDES Belega',
            'subjudul_sistem' => 'Sistem Absensi Rapat Kantor Desa Belega',
            'nama_desa' => 'Pemerintah Desa Belega',
            'alamat_desa' => 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581',
            'kecamatan' => 'Kecamatan Blahbatuh',
            'kabupaten' => 'Kabupaten Gianyar',
            'provinsi' => 'Bali',
        ]);

        $rapat = $kehadiran->rapat;
        $undangan = $kehadiran->undangan;

        $mapHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $mapBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Format waktu kehadiran
        $waktuKehadiran = $kehadiran->waktu_perangkat ?? $kehadiran->dibuat_pada ?? now();
        $hariHadir = $mapHari[$waktuKehadiran->format('l')] ?? $waktuKehadiran->format('l');
        $bulanHadir = $mapBulan[(int) $waktuKehadiran->format('n')] ?? $waktuKehadiran->format('F');
        $waktuHadirFormatted = "{$hariHadir}, {$waktuKehadiran->format('j')} {$bulanHadir} {$waktuKehadiran->format('Y')} — {$waktuKehadiran->format('H:i:s')} WITA";

        // Format tanggal rapat
        $hariRapat = $rapat->tanggal ? ($mapHari[$rapat->tanggal->format('l')] ?? $rapat->tanggal->format('l')) : '-';
        $bulanRapat = $rapat->tanggal ? ($mapBulan[(int) $rapat->tanggal->format('n')] ?? $rapat->tanggal->format('F')) : '-';
        $tanggalRapatFormatted = $rapat->tanggal ? "{$hariRapat}, {$rapat->tanggal->format('j')} {$bulanRapat} {$rapat->tanggal->format('Y')}" : '-';

        // URL verifikasi keabsahan dokumen
        $urlVerifikasi = route('dokumen.verifikasi', $rapat->kode);
        $qrVerifikasiSvg = null;
        try {
            $qrVerifikasiSvg = QrCode::size(80)->margin(0)->generate($urlVerifikasi);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal generate QR Bukti Hadir: ' . $e->getMessage());
        }

        // Teks untuk share ke WhatsApp
        $urlBuktiPribadi = route('kehadiran.bukti', $kehadiran->id);
        $pesanWa = "Halo, saya telah menyelesaikan absensi resmi Pemerintah Desa Belega pada acara '{$rapat->judul}' (Kode: {$rapat->kode}).\n\nLihat Kartu Bukti Hadir Digital saya:\n{$urlBuktiPribadi}";
        $linkWa = "https://wa.me/?text=" . urlencode($pesanWa);

        return view('kehadiran.kartu-bukti', [
            'kehadiran' => $kehadiran,
            'rapat' => $rapat,
            'undangan' => $undangan,
            'pengaturan' => $pengaturan,
            'waktuHadirFormatted' => $waktuHadirFormatted,
            'tanggalRapatFormatted' => $tanggalRapatFormatted,
            'urlVerifikasi' => $urlVerifikasi,
            'qrVerifikasiSvg' => $qrVerifikasiSvg,
            'urlBuktiPribadi' => $urlBuktiPribadi,
            'linkWa' => $linkWa,
        ]);
    }
}
