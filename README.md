# SIABDES Belega (Laravel)

Sistem Absensi Digital Rapat Kantor Desa Belega (Kec. Blahbatuh, Kab. Gianyar, Bali).

## Stack Teknologi

- **Backend / Monolith**: Laravel 12 + Livewire 3 + MySQL 8
- **Frontend**: Blade + Livewire + Alpine.js + Tailwind CSS
- **Ekspor & Utilitas**: Maatwebsite Excel, SimpleSoftwareIO QRCode, Dexie.js (Offline queue)

## Akun Pengujian Development (Seeder)

Setelah menjalankan `php artisan migrate:fresh --seed`, akun administrator default yang dibuat adalah:

| Parameter | Nilai Default |
|---|---|
| **Email** | `admin@belega.desa.id` |
| **Password** | `admin123` |
| **Nama** | Administrator Desa Belega |
| **Peran** | `admin` |

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
