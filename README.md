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
