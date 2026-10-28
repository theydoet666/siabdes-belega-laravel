<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\PengaturanSistem;
use App\Models\Rapat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $mapBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
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
            Log::warning('Gagal generate QR Bukti Hadir: '.$e->getMessage());
        }

        // URL bukti kehadiran bertanda tangan sementara (24 jam)
        $urlBuktiPribadi = URL::temporarySignedRoute('kehadiran.bukti', now()->addHours(24), ['id' => $kehadiran->id]);

        // URL berkas foto wajah dan tanda tangan privat bertanda tangan sementara (24 jam)
        $fotoUrl = $kehadiran->foto_path
            ? URL::temporarySignedRoute('berkas.tampil', now()->addHours(24), ['jenis' => 'foto', 'id' => $kehadiran->id])
            : null;

        $ttdUrl = $kehadiran->ttd_path
            ? URL::temporarySignedRoute('berkas.tampil', now()->addHours(24), ['jenis' => 'ttd', 'id' => $kehadiran->id])
            : null;

        // Teks untuk share ke WhatsApp
        $pesanWa = "Halo, saya telah menyelesaikan absensi resmi Pemerintah Desa Belega pada acara '{$rapat->judul}' (Kode: {$rapat->kode}).\n\nLihat Kartu Bukti Hadir Digital saya:\n{$urlBuktiPribadi}";
        $linkWa = 'https://wa.me/?text='.urlencode($pesanWa);

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
            'fotoUrl' => $fotoUrl,
            'ttdUrl' => $ttdUrl,
            'linkWa' => $linkWa,
        ]);
    }

    /**
     * Membuka Kartu Bukti Hadir dari antarmuka rapat publik/kiosk dengan menghasilkan signed URL sah.
     */
    public function bukaDariRapat(string $kode, string $id): RedirectResponse
    {
        $rapat = Rapat::where('kode', $kode)->firstOrFail();

        $kehadiran = Kehadiran::where('id', $id)
            ->where('rapat_id', $rapat->id)
            ->where('dibatalkan', false)
            ->firstOrFail();

        $signedUrl = URL::temporarySignedRoute('kehadiran.bukti', now()->addHours(24), [
            'id' => $kehadiran->id,
        ]);

        return redirect()->to($signedUrl);
    }
}
