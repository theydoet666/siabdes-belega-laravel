# Rangkuman & Catatan Pengembangan SIABDES Belega (Laravel 12)

**Tanggal Pembaruan Terakhir:** 29 Oktober 2026  
**Status Proyek:** Seluruh fitur telah selesai dan **BERHASIL DI-HOSTING / DEPLOY KE PRODUCTION** di subdomain resmi `https://siabsendesa.belega.id/` (cPanel Rumahweb, PHP 8.4.27, MySQL 8). Dokumentasi SOP pemeliharaan dan revisi berkala tersimpan di `DOKUMENTASI_HOSTING_PRODUCTION.md`.

---

## 1. Arsitektur & Teknologi Terpasang
- **Framework Utama:** Laravel 12.0 (PHP 8.2+)
- **Komponen Reaktif:** Livewire 3.5 + Alpine.js 3
- **Styling:** Tailwind CSS v4 (`@tailwindcss/vite`) dengan token tema resmi SIABDES (`daun`, `daun-tua`, `kertas`, `tinta`, `pena`, `kuning`, `garis`)
- **Basis Data:** MySQL 8 (dan SQLite in-memory untuk automated testing)
- **Modul Offline-First Kiosk:** Dexie.js (IndexedDB) + Web Audio API (beep) + Canvas quadratic curve smoothing (Pointer Events) + `html2canvas` (Local Bundle)
- **Ekspor Dokumen & Cetak:** `maatwebsite/excel` (XLSX) + `@media print` A4 margin 20mm (`resources/css/print.css`)
- **QR Code:** `simplesoftwareio/simple-qrcode`

---

## 2. Pembaruan Fitur Sesi 28 September 2026

### A. Template Cetak Lembar Daftar Hadir A4 Resmi (`/rapat/{id}/cetak`)
- **Kop Surat & Logo**: Diperbesar dan proporsional (`w-24 h-24`, max 85px) dengan tipografi berwibawa (*Times New Roman*, font 13pt–17pt).
- **Tabel Kehadiran**: Kolom Waktu (WITA) dihilangkan sesuai standar arsip formal desa (terdiri dari No, Nama Lengkap, Jabatan, Instansi/Banjar, Tanda Tangan).
- **Footer**: Statistik Total Kehadiran & Waktu Cetak diposisikan rapi di bawah blok 3 penandatangan.
- **Margin Presisi**: Dikonfigurasi presisi `@page { size: A4 portrait; margin: 20mm; }` di semua sisi.
- **QR Keabsahan Dokumen & Toggle Button**: Terdapat tombol interaktif *QR Keabsahan (Aktif/Nonaktif)* pada top bar untuk menampilkan/menyembunyikan blok verifikasi QR resmi di footer.

### B. Halaman Publik Verifikasi Keabsahan Dokumen (`/v/{kode}`)
- **Rute Publik**: `Route::get('/v/{kode}', [VerifikasiDokumenController::class, 'show'])->name('dokumen.verifikasi');`
- Menampilkan sertifikat digital integritas rapat:
  - Lencana: `✓ DOKUMEN RESMI TERVERIFIKASI` (Basis data Pemerintah Desa Belega).
  - Waktu pengecekan verifikasi (*WITA*), Kode Unik Rapat.
  - Detail Rapat (Judul, Hari/Tanggal, Waktu, Tempat, Penyelenggara).
  - Rekapitulasi Kehadiran (Jumlah Hadir, Total Undangan, Persentase).
  - Pejabat Pengesah (Perbekel Belega, Sekretaris Desa, Pelaksana Kegiatan).
  - Catatan jaminan kepatuhan hukum UU No. 27/2022 PDP.

### C. Salinan Kartu Bukti Hadir Digital Pribadi (`/bukti/{id}`)
- **Rute Publik UUID**: `Route::get('/bukti/{id}', [BuktiKehadiranController::class, 'show'])->name('kehadiran.bukti');`
- Menampilkan kartu identitas kehadiran resmi peserta:
  - Kop Desa Belega, Lencana `Hadir Sah`.
  - Nama Peserta, Jabatan, Asal Banjar, Keterangan Perwakilan.
  - Tanda Tangan Digital & Foto Wajah Asli.
  - QR Code Verifikasi Dokumen Rapat.
  - **Tombol Unduh PNG ke Galeri**: Menggunakan `html2canvas` lokal (resolusi tinggi, offline-friendly).
  - **Tombol Bagikan ke WhatsApp**: Teks pesan konfirmasi kehadiran siap kirim.
  - **Tombol Cetak / PDF**: Format ramah cetak kartu saku.

### D. Fitur Unduh Bukti pada Tabel Daftar Undangan (`/rapat/{id}`) & Modal Kiosk
- Di kolom **Status Kehadiran** dan **Aksi** pada detail rapat, peserta yang berstatus `Hadir` memiliki tombol unduh/buka Kartu Bukti Hadir Digital langsung ke halaman `/bukti/{id}`.
- Pada modal **Daftar Seluruh Undangan** dan hasil pencarian Kiosk (`/kiosk/{kode}` dan `/r/{kode}`), peserta yang sudah absen kini memiliki tombol aktif **`● Lihat Bukti ↗`** yang membuka kartu bukti hadir digital resmi di tab baru.

### E. Perbaikan Bug & Ketahanan Sistem
- **Reset Kanvas TTD**: Perbaikan *DOM persistence* dan *ResizeObserver* pada Kiosk sehingga tanda tangan peserta sebelumnya tidak terbawa ke peserta berikutnya.
- **Sinkronisasi Antrean Offline**: Penanganan respon API `gagal_ids` dan batas percobaan coba ulang (*max retry*) di `antrean.js` agar antrean data yang ditolak tidak mengalami *infinite loop* pengiriman setiap 5 detik.
- **Paginasi Livewire**: Konfirmasi dan fleksibilitas pagination pada tabel daftar undangan rapat.

---

## 3. Ringkasan Pengujian Otomatis

Jalankan perintah berikut untuk memvalidasi seluruh test suite:
```bash
php artisan test
```

**Hasil Pengujian:**
```text
PASS  Tests\Unit\ExampleTest (1 test)
PASS  Tests\Feature\AutentikasiDanOperatorTest (9 tests)
PASS  Tests\Feature\ExampleTest (1 test)
PASS  Tests\Feature\KepatuhanPrivasiDanAuditLogTest (6 tests)
PASS  Tests\Feature\KeseragamanDesainDanLayoutTest (3 tests)
PASS  Tests\Feature\KioskDanCheckinTest (8 tests)
PASS  Tests\Feature\ManajemenRapatTest (7 tests)
PASS  Tests\Feature\ModulCetakDanEksporTest (5 tests)
PASS  Tests\Feature\ModulUndanganTest (6 tests)

Tests:    46 passed (390 assertions)
Duration: 2.27s
```

Kompilasi Aset Frontend:
```bash
npm run build
```

---

## 5. Pembaruan Sesi 29 Oktober 2026 — Deployment Produksi Sukses

- **Target URL Produksi:** `https://siabsendesa.belega.id/`
- **Hosting Environment:** Rumahweb Shared Hosting (cPanel / CloudLinux), PHP 8.4.27 (`ea-php84`).
- **Database:** MySQL `belm9279_siabdes_belega` (User: `belm9279_admin_siabdesa`).
- **Penataan Folder Keamanan Tinggi:**
  - File inti Laravel terisolasi aman di `/home/belm9279/siabdes_app/` (di luar web root).
  - Web root publik terpusat di `/home/belm9279/public_html/siabsendesa.belega.id/`.
- **Catatan & SOP Revisi Masa Depan:** Tersimpan lengkap di [`DOKUMENTASI_HOSTING_PRODUCTION.md`](file:///c:/laragon/www/siabdes-belega-laravel/DOKUMENTASI_HOSTING_PRODUCTION.md).

---

## 6. Pembaruan Sesi 30 Oktober 2026 — Perbaikan Kamera Kiosk Multi-Peserta & Form Login Dinamis

- **Solusi Kamera Kiosk:** Mengatasi masalah kamera mati / layar hitam saat orang kedua dan seterusnya absen di perangkat yang sama:
  - Event-driven camera lifecycle (`siapkan-kamera-wajah` dan `hentikan-kamera-wajah`).
  - Kamera hanya aktif saat masuk ke layar foto, dan otomatis mati saat kembali ke tanda tangan atau selesai absensi.
  - Reset bersih `srcObject = null` dan pembersihan track lama sebelum membuka kamera baru.
  - Tombol bantuan interaktif "Aktifkan / Coba Kamera Lagi" pada layar foto.
- **Form Login Dinamis:** Logo dinas, nama sistem, dan subjudul terhubung otomatis dengan tabel `pengaturan_sistem`.
- **Kompilasi Aset:** Aset JavaScript terkompilasi ke `public/build/assets/app-DXMyTv61.js` (arsip: `public/build.zip`).


