# 📘 DOKUMENTASI LENGKAP HOSTING & PRODUCTION SIABDES BELEGA

**Terakhir Diperbarui:** 29 Oktober 2026  
**Status Produksi:** ✅ ONLINE & AKTIF  
**URL Produksi:** [https://siabsendesa.belega.id/](https://siabsendesa.belega.id/)  

---

## 1. Spesifikasi Server & Hosting

| Komponen | Spesifikasi / Konfigurasi |
| :--- | :--- |
| **Provider** | Rumahweb (cPanel / CloudLinux) |
| **Versi PHP** | **PHP 8.4.27** (`ea-php84`) |
| **Ekstensi PHP Wajib** | `mbstring` *(kritis untuk Laravel Str/mb_split)*, `fileinfo`, `pdo_mysql`, `xml`, `zip`, `bcmath` |
| **User Home Directory** | `/home/belm9279/` |
| **Subdomain Document Root** | `/home/belm9279/public_html/siabsendesa.belega.id/` |
| **Folder Inti Laravel** | `/home/belm9279/siabdes_app/` *(Aman di luar `public_html`)* |

---

## 2. Kredensial & Konfigurasi Sistem

### A. Database Produksi (MySQL)
- **Host:** `127.0.0.1` (Port: 3306)
- **Database Name:** `belm9279_siabdes_belega`
- **Username:** `belm9279_admin_siabdesa`
- **Password:** `zNHxP~yq!xFM$8Zu` *(di `.env` wajib gunakan tanda petik tunggal: `'zNHxP~yq!xFM$8Zu'`)*

### B. Laravel Keys & Security
- **APP_KEY:** `base64:E/DcejTC7SBKBlCUcbaybgW/Ei7hn9e5Rjdhqy87+ec=`
- **APP_URL:** `https://siabsendesa.belega.id`
- **Password Awal Admin (Seeder):** `GIHfQgk09KvhIcCV`

---

## 3. Topologi Folder di Server

```text
/home/belm9279/
│
├── siabdes_app/                               <-- Aplikasi Inti Laravel
│   ├── app/                                   <-- Controller, Models, Livewire
│   ├── bootstrap/
│   │   └── cache/                             <-- Permission 775
│   ├── config/
│   ├── database/
│   ├── public/
│   │   └── build/                             <-- Wajib ada untuk membaca manifest.json
│   ├── resources/                             <-- Views / Blade
│   ├── routes/                                <-- web.php
│   ├── storage/                               <-- Permission 775
│   │   ├── app/public/                        <-- Target symlink
│   │   ├── framework/
│   │   └── logs/
│   ├── vendor/                                <-- Dependencies Composer
│   ├── .env                                   <-- Environment Production
│   └── artisan
│
└── public_html/
    └── siabsendesa.belega.id/                 <-- Document Root Subdomain
        ├── .htaccess                          <-- Apache Rewrite Laravel
        ├── favicon.ico
        ├── index.php                          <-- Dimodifikasi 2 baris autoload
        ├── robots.txt
        ├── storage/                           <-- Symlink ke siabdes_app/storage/app/public
        └── build/                             <-- Aset Publik (CSS, JS, Fonts)
            ├── assets/
            └── manifest.json
```

---

## 4. Konfigurasi Kritis File `index.php`

File `/home/belm9279/public_html/siabsendesa.belega.id/index.php` telah disesuaikan menjadi:

```php
// Register the Composer autoloader...
require __DIR__.'/../../siabdes_app/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../../siabdes_app/bootstrap/app.php';
```

---

## 5. Catatan Masalah yang Pernah Ditemukan & Solusinya

1. **`Call to undefined function Illuminate\Support\mb_split()`:**  
   - *Penyebab:* Ekstensi `mbstring` belum aktif di PHP cPanel.  
   - *Solusi:* Aktifkan ekstensi `mbstring` melalui menu *Select PHP Version* atau *MultiPHP INI Editor* di cPanel.
2. **`Vite manifest not found at: /siabdes_app/public/build/manifest.json`:**  
   - *Penyebab:* Laravel mengecek `public_path('build/manifest.json')` di folder `siabdes_app/public/`.  
   - *Solusi:* Folder `build` wajib disalin ke **dua lokasi**:  
     - `/public_html/siabsendesa.belega.id/build/` (untuk browser/klien).  
     - `/home/belm9279/siabdes_app/public/build/` (untuk fungsi `@vite` Laravel).
3. **Storage Symlink Diblokir Apache Shared Hosting (404 Not Found pada berkas upload):**  
   - *Penyebab:* Web server Apache di shared hosting sering menerapkan proteksi `SymLinksIfOwnerMatch` atau menonaktifkan symlink yang mengarah ke luar `public_html`.  
   - *Solusi:* Menggunakan skrip gerbang `storage.php` yang dipadukan dengan aturan `.htaccess`:
     ```apache
     RewriteRule ^storage/(.*)$ storage.php [L,QSA]
     ```
     Solusi ini menyajikan berkas foto/logo/ttd secara langsung dan sangat cepat tanpa batasan symlink.

4. **Kamera Kiosk Layar Hitam pada Peserta Kedua dan Seterusnya:**  
   - *Penyebab:* Pada Single Page Application / Kiosk Alpine.js, stream kamera orang pertama dimatikan setelah jepret foto (`stream.stop()`), namun saat orang berikutnya maju, komponen kamera tidak otomatis melakukan *re-init/re-acquire* stream kamera baru.  
   - *Solusi:* Mengirimkan event global `siapkan-kamera-wajah` saat beralih ke layar foto, membersihkan `srcObject = null`, dan meminta ulang izin `getUserMedia` yang segar untuk tiap peserta baru.


---

## 6. SOP / Prosedur Revisi & Update di Masa Depan

Bila besok ada perubahan fitur atau perbaikan bug, ikuti panduan praktis berikut:

### Skenario A: Perubahan File Kode PHP / Blade / Controller / Route
> **Contoh:** Mengubah logika di controller, teks blade, atau query database.
1. Di komputer lokal, edit kode seperti biasa dan uji di Laragon.
2. Buka **cPanel > File Manager**.
3. Cukup upload & overwrite file yang Anda edit langsung ke folder yang sesuai di:  
   📂 `/home/belm9279/siabdes_app/...`  
   *(Contoh: jika ubah `app/Http/Controllers/RapatController.php`, cukup upload file tersebut saja).*

### Skenario B: Perubahan Tampilan Frontend (CSS Tailwind / Alpine.js / JS)
> **Contoh:** Menambah styling class baru Tailwind atau merombak komponen UI frontend.
1. Di komputer lokal (PowerShell):
   ```powershell
   npm run build
   ```
2. Buka folder `c:\laragon\www\siabdes-belega-laravel\public\build\`.
3. Zip folder `build/` tersebut menjadi `build.zip`.
4. Upload `build.zip` ke server dan ekstrak ke **kedua lokasi**:
   - `/public_html/siabsendesa.belega.id/build/`
   - `/home/belm9279/siabdes_app/public/build/`

### Skenario C: Penambahan Tabel / Kolom Database Baru (Migrasi)
> **Contoh:** Menambah kolom baru pada rapat atau tabel baru.
1. Jalankan migrasi di komputer lokal:
   ```powershell
   php artisan migrate
   ```
2. Buka **phpMyAdmin lokal** (`http://localhost/phpmyadmin`).
3. Export hanya tabel atau struktur kolom yang baru.
4. Buka **phpMyAdmin cPanel** (`belm9279_siabdes_belega`) > tab **SQL** / **Import**, jalankan query perubahan tersebut.

### Skenario D: Membersihkan Cache Laravel di Server Tanpa SSH
Bila perubahan konfigurasi tidak langsung muncul, buat file `clear-cache.php` di Document Root subdomain:
```php
<?php
require __DIR__ . '/../../siabdes_app/vendor/autoload.php';
$app = require_once __DIR__ . '/../../siabdes_app/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('optimize:clear');
echo "Cache Laravel berhasil dibersihkan!";
```
Buka `https://siabsendesa.belega.id/clear-cache.php` sekali, lalu langsung hapus filenya.

