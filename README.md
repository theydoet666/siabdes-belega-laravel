# SIABDES Belega (Laravel)

Sistem Absensi Digital Rapat Kantor Desa Belega (Kec. Blahbatuh, Kab. Gianyar, Bali).

## Stack Teknologi

- **Backend / Monolith**: Laravel 12 + Livewire 3 + MySQL 8
- **Frontend**: Blade + Livewire + Alpine.js + Tailwind CSS
- **Ekspor & Utilitas**: Maatwebsite Excel, SimpleSoftwareIO QRCode, Dexie.js (Offline queue)

## Inisialisasi Basis Data

Untuk inisialisasi basis data awal dan data master, jalankan migrasi serta seeder:
```bash
php artisan migrate --seed
```

> **Catatan Keamanan Akun Awal:**
> Password awal administrator (`admin@belega.desa.id`) dapat disetel melalui variabel lingkungan `ADMIN_INITIAL_PASSWORD` di berkas `.env`. Jika variabel tersebut dikosongkan, sistem seeder secara otomatis akan membuat password acak 16 karakter dan menampilkannya sekali di layar terminal. Sangat disarankan untuk segera mengubah password administrator saat login pertama kali.


## Perintah Dasar

```bash
# Migrasi dan seeding database
php artisan migrate:fresh --seed

# Menjalankan server Laravel
php artisan serve

# Menjalankan Vite dev server
npm run dev

# Kompilasi aset frontend
npm run build
```

## Checklist Deploy Produksi

Sebelum merilis sistem ke server produksi publik:

1. **Konfigurasi Environment**:
   - Salin contoh template: `cp .env.production.example .env`
   - Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.
   - Gunakan URL bersertifikat SSL resmi: `APP_URL=https://siabdes.belega.desa.id`.
   - Pastikan `LOG_LEVEL=warning`.
   - Aktifkan keamanan sesi: `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, dan `SESSION_SAME_SITE=lax`.
2. **Kunci Aplikasi & Kredensial**:
   - Generate application key: `php artisan key:generate`
   - Tetapkan `ADMIN_INITIAL_PASSWORD` yang kuat atau biarkan kosong untuk di-generate otomatis saat `migrate --seed`.
3. **Penyimpanan Berkas Privat (UU No. 27/2022 PDP)**:
   - Pastikan direktori `storage/app/private` dapat ditulis oleh web server (`chown -R www-data:www-data storage bootstrap/cache`).
   - Berkas foto wajah dan tanda tangan tersimpan di disk `local` privat dan dilindungi signed route / otorisasi operator (tidak boleh ada symlink folder foto/ttd ke public).
   - Jalankan migrasi berkas lama jika diperlukan: `php artisan berkas:pindahkan-ke-privat`.
4. **Optimasi Kinerja & Cache**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   npm run build
   ```
5. **Otomatisasi Retensi Data & Cron**:
   - Tambahkan cron scheduler Laravel pada server (`crontab -e`):
     `* * * * * cd /path-to-siabdes && php artisan schedule:run >> /dev/null 2>&1`
   - Hal ini memastikan pembersihan retensi foto otomatis (`retensi:bersihkan-foto`) berjalan berkala.

