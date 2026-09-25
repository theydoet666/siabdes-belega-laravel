# PRD — SIABDES Belega (Migrasi Stack)
### Sistem Absensi Digital Rapat Kantor Desa Belega

| | |
|---|---|
| **Versi dokumen** | 2.0 (Migrasi Stack) |
| **Tanggal** | 23 September 2026 |
| **Status** | Perencanaan Migrasi — Stack Lama Masih Berjalan |
| **Pemilik produk** | Pemerintah Desa Belega, Kec. Blahbatuh, Kab. Gianyar |
| **Stack Lama** | React + Vite + Tailwind CSS · Supabase (Postgres, Auth, Storage, Edge Functions) · PWA Dexie.js |
| **Stack Baru** | **Laravel 12** + **Blade** + **Livewire 3** + **Alpine.js** + **Tailwind CSS** (frontend) · **MySQL 8** (database) |
| **Prinsip Migrasi** | **Logic bisnis, alur kerja, dan desain visual TIDAK BOLEH berubah.** Migrasi ini murni migrasi teknologi (re-platforming), bukan pembuatan ulang produk. |

---

## 0. Prinsip & Batasan Migrasi

Dokumen ini adalah hasil pembacaan menyeluruh terhadap repository awal (`SIABDES-BELEGA`, stack React + Supabase) dan menerjemahkan seluruh spesifikasi yang sudah *terimplementasi & aktif* ke dalam arsitektur Laravel. Aturan wajib selama migrasi:

1. **Tidak ada perubahan logic bisnis.** Semua aturan (validasi kode rapat 4 karakter tanpa `I,O,0,1`, idempotensi check-in, retensi foto 90 hari, RLS-equivalent authorization, dsb.) harus dipertahankan persis.
2. **Tidak ada perubahan desain/UI.** Token warna, tipografi, layout, ukuran tombol minimum 48px, format cetak A4 margin 2 cm, dan seluruh komponen visual harus direplikasi 1:1 memakai Tailwind CSS + Blade/Livewire — hanya cara rendering yang berubah (dari React CSR ke server-rendered Blade + Livewire reactive component), bukan hasil visualnya.
3. **Fungsi ≈ fungsi, bukan cara ≈ cara.** Supabase RPC/Edge Function diterjemahkan menjadi Eloquent Model + Service Class + Livewire Component/Controller Action dengan **perilaku input/output yang identik**.
4. **Postgres → MySQL**: tipe data disesuaikan (uuid, enum, jsonb, generated column, timestamptz) tanpa mengubah makna kolom atau constraint.
5. **Offline-first tetap menjadi kebutuhan wajib** di mode kiosk (T5), tetapi strateginya diadaptasi karena Livewire bersifat server-driven (lihat §11 "Strategi Offline-First pada Arsitektur Livewire").

---

## 1. Ringkasan

SIABDES Belega adalah aplikasi web progresif (PWA) untuk mencatat kehadiran peserta rapat di Kantor Desa Belega secara terstruktur, cepat, dan berkekuatan hukum dinas. Peserta hadir di meja registrasi, mencari namanya di daftar undangan atau memilih dari modal daftar hadir, menandatangani di layar sentuh dengan garis kuadratik yang halus, dan secara opsional mengambil foto bukti kehadiran. Sistem secara otomatis menghasilkan lembar daftar hadir resmi berstandar kertas **A4** (margin 2 cm) lengkap dengan kop dinas, footer pengesahan waktu WITA, blok tanda tangan Perbekel dinamis, serta ekspor format Excel (XLSX) untuk rekapitulasi arsip.

Pada versi migrasi ini, aplikasi dibangun ulang di atas **Laravel** sebagai backend monolitik, **Blade** sebagai templating, **Livewire** untuk komponen reaktif tanpa menulis API terpisah, **Alpine.js** untuk interaksi client-side ringan (kanvas tanda tangan, kamera, countdown, modal), dan **Tailwind CSS** untuk styling — seluruhnya di atas **MySQL** sebagai basis data. Kiosk tablet tetap harus tetap dapat melakukan registrasi & tanda tangan sekalipun jaringan ke server Laravel terputus sesaat, lalu menyinkronkan data begitu koneksi pulih (lihat §11).

---

## 2. Latar Belakang dan Masalah

*(Tidak berubah dari versi produk sebelumnya — masalah bisnis yang melatarbelakangi produk tetap sama, migrasi ini tidak mengubah kebutuhan pengguna.)*

1. **Lambat dan rawan salah ketik.** Rekap manual memakan waktu 1–2 jam setelah rapat selesai dan nama undangan sering salah eja saat dipindahkan ke berkas pertanggungjawaban.
2. **Lembar hilang atau rusak.** Daftar hadir fisik rentan terselip atau rusak, menghambat penyusunan Laporan Pertanggungjawaban (LPJ).
3. **Undangan tambahan tidak tercatat rapi.** Peserta yang hadir di luar undangan resmi menulis di pinggir kertas, menyulitkan verifikasi lembaga asal.
4. **Tidak ada bank data historis.** Riwayat kehadiran tokoh dan warga tidak terdata rapi lintas rapat, menyulitkan penyiapan daftar undangan musyawarah desa berikutnya.
5. **Verifikasi kehadiran lemah.** Tanda tangan di kertas mudah diwakilkan tanpa pencatatan jam kedatangan atau foto pembanding.

---

## 3. Tujuan dan Metrik Keberhasilan

### 3.1 Tujuan Produk

| Kode | Tujuan |
|---|---|
| T1 | Menghilangkan proses rekap manual setelah rapat selesai |
| T2 | Menghasilkan lembar cetak daftar hadir resmi A4 siap ttd Perbekel secara instan |
| T3 | Mencatat undangan tambahan secara terstruktur dan terpisah |
| T4 | Menyediakan bank data riwayat undangan unik lintas rapat untuk percepatan impor |
| T5 | Berfungsi penuh 100% saat jaringan internet offline di tablet kiosk |
| T6 | Memenuhi standar kepatuhan privasi data pribadi (UU No. 27/2022 PDP) dengan retensi foto otomatis |
| T7 *(baru)* | Migrasi ke Laravel/MySQL tanpa regresi fungsi maupun tampilan terhadap versi sebelumnya |

### 3.2 Metrik Keberhasilan

| Metrik | Target | Cara Ukur |
|---|---|---|
| Waktu check-in per peserta | ≤ 30 detik | Selisih waktu mulai cari nama dan cetak kartu hadir di log |
| Waktu cetak lembar daftar hadir final | ≤ 1 menit setelah rapat | Akses menu cetak langsung dari panel rapat |
| Peserta check-in mandiri / dibantu | ≥ 80% mandiri | Evaluasi berkala operator rapat |
| Kehilangan data saat internet putus | 0 kejadian (Zero Loss) | Selisih antrean IndexedDB lokal dan tabel `kehadiran` MySQL |
| Ketepatan pencarian nama | ≥ 95% | Skor kecocokan token string & fuzzy matching di Service `PencarianUndanganService` |
| Paritas fungsi pasca-migrasi | 100% fitur §5 berjalan identik | Uji regresi manual per modul terhadap versi lama |

---

## 4. Pengguna & Matriks Hak Akses

### 4.1 Persona Pengguna

- **Peserta Rapat (Warga / Tokoh Lembaga Desa):** Menggunakan tablet kiosk atau tautan mandiri (`/r/{kode}`). Membutuhkan antarmuka bersih, tombol besar (min. 48px), opsi mencari nama atau memilih dari daftar undangan, kanvas tanda tangan responsif (Alpine.js + `<canvas>`), dan bukti hadir instan.
- **Operator Rapat (Perangkat Desa / Staf Kaur):** Mengelola data rapat, mengimpor undangan (CSV/XLSX/Riwayat Undangan), membuka mode kiosk ber-PIN, memantau absensi langsung (Livewire `wire:poll` / event broadcasting), dan mencetak laporan daftar hadir resmi.
- **Administrator Sistem (Sekretaris Desa / Admin TI):** Mengelola akun dan profil operator baru, mengonfigurasi identitas desa & kop surat, mengelola retensi data foto privasi, dan memantau log audit keamanan.

### 4.2 Matriks Peran

| Fitur / Kemampuan | Peserta (Anonim) | Operator Rapat | Administrator Desa |
|---|:---:|:---:|:---:|
| Cari nama & pilih dari daftar undangan rapat | ✓ | ✓ | ✓ |
| Menambah diri sebagai undangan tambahan | ✓ | ✓ | ✓ |
| Check-in (Tanda Tangan Digital & Foto Opsional) | ✓ | ✓ | ✓ |
| Membuat, mengedit, & menduplikasi rapat | ✗ | ✓ | ✓ |
| Menghapus data rapat permanen & berkas storage | ✗ | ✗ | ✓ |
| Mengatur Penandatangan Laporan (Perbekel/Sekdes) | ✗ | ✓ | ✓ |
| Impor CSV/XLSX & Bank Riwayat Undangan Unik | ✗ | ✓ | ✓ |
| Manajemen Kehadiran & Batal Hadir (dengan Audit Log) | ✗ | ✓ | ✓ |
| Cetak Lembar Hadir Resmi A4 & Ekspor Excel | ✗ | ✓ | ✓ |
| Tambah & Kelola Akun/Profil Operator Baru | ✗ | ✗ | ✓ |
| Pengaturan Identitas Desa, Logo, & Kop Cetak | ✗ | ✗ | ✓ |
| Eksekusi Retensi Foto PDP & Pembersihan Massal | ✗ | ✗ | ✓ |
| Akses Log Audit Keamanan Sistem | ✗ | ✗ | ✓ |

**Implementasi otorisasi di Laravel:** kolom `peran` (`admin` / `operator`) pada tabel `profil` dipetakan ke **Laravel Gate & Policy** (`AdminOnly`, `OperatorOrAdmin`) serta middleware route-group (`auth`, `role:admin`). Ini menggantikan Row Level Security (RLS) Postgres/Supabase — aturan akses yang sama dipindahkan ke lapisan **Policy class** per model (`RapatPolicy`, `PengaturanPolicy`, `AuditLogPolicy`), bukan di level database.

---

## 5. Ruang Lingkup & Fitur (Paritas Penuh dengan Versi Sebelumnya)

> Seluruh fitur di bawah **wajib berperilaku identik** dengan versi React/Supabase. Kolom "Implementasi Laravel" menunjukkan penggantian teknis, bukan perubahan perilaku.

### 5.1 Autentikasi & Manajemen Operator Multi-User
- Login aman berbasis sesi Laravel (`Auth::attempt`), menggantikan Supabase Auth, sesi tetap tersimpan (`remember me`).
- Panel Admin untuk menambah operator baru — pengganti RPC `tambah_operator` adalah **`OperatorService::tambahOperator()`** yang membuat baris `users`/`profil` dengan password di-hash `bcrypt` (bawaan Laravel `Hash::make`).
- Ubah peran (*Admin/Operator*), aktif/nonaktifkan akun, dengan pencatatan ke `audit_log`.
- Paginasi tabel operator memakai Livewire `WithPagination` trait (menggantikan paginasi manual React).

### 5.2 Manajemen Rapat & Penandatangan Dokumen Dinamis
- Pembuatan rapat baru dengan kode unik 4 karakter alfanumerik bebas ambigu (**tanpa `I, O, 0, 1`**) — logic generator kode dipindahkan ke `App\Services\KodeRapatService` (regex validasi identik: `^[A-HJ-NP-Z2-9]{4}$`).
- Konfigurasi penandatangan laporan per rapat (*Nama, Jabatan, NIP, Lokasi Pengesahan*).
- Status siklus hidup rapat: `draft` → `dibuka` → `ditutup` (Eloquent `enum` cast).
- **Hapus Rapat (khusus Admin)**: cascade delete relasi undangan & kehadiran via `onDelete('cascade')` di migration, plus penghapusan berkas fisik tanda tangan/foto dari **Laravel Storage** (disk `public` atau `s3`-compatible, pengganti Supabase Storage bucket `bukti`), dicatat ke `audit_log`.
- Paginasi dan tombol "segarkan data" pada daftar rapat via Livewire.

### 5.3 Manajemen Undangan & Bank Riwayat Lintas Rapat
- Impor massal CSV & Excel (`maatwebsite/excel`) dengan validasi kolom & penandaan duplikat — menggantikan `papaparse`/`xlsx` di sisi client; validasi kini dilakukan di server (`ImporUndanganRequest` + `ImporUndanganService`).
- **"Salin dari Riwayat Undangan"**: query Eloquent `Undangan::distinct()->select(...)` yang menggantikan RPC `ambil_riwayat_undangan_unik()`, mengambil kombinasi unik nama/jabatan/instansi/HP lintas rapat lama.
- Penambahan undangan manual per orang & penandaan undangan tambahan (`sumber` = `import` | `tambahan`).
- Paginasi data undangan di panel operator (Livewire component `TabelUndangan`).

### 5.4 Mode Kiosk & Registrasi Mandiri Offline-First
- Akses kiosk meja registrasi terkunci PIN 6 digit (hash bcrypt di kolom `rapat.pin_kiosk`).
- **Pencarian Cerdas & Daftar Lengkap**: pencarian real-time dengan debounce 200 ms via Livewire `wire:model.live.debounce.200ms`, plus modal dialog daftar seluruh undangan (menggantikan RPC `daftar_undangan_rapat`, kini `UndanganController@daftarPublik` / Livewire component `ModalDaftarUndangan`).
- **Kanvas Tanda Tangan Halus**: kurva kuadratik berbasis Pointer Events — logic JS canvas smoothing dipindahkan apa adanya ke **Alpine.js component** (`x-data="tandaTanganPad()"`), termasuk auto-crop & kompresi PNG transparan (fungsi `src/lib/gambar.js` diporting menjadi modul JS Alpine, logic matematis tidak diubah).
- **Pengambilan Foto Wajah dengan Timer 3 Detik**: `getUserMedia` + Web Audio API beep + overlay animasi + flash efek — diporting ke Alpine.js component (`x-data="kameraWajah()"`), instruksi & UX sama persis.
- **Kartu Bukti Hadir**: stempel dinas berputar −9°, jam check-in WITA, nomor urut kehadiran — di-render Blade partial dengan Tailwind (identik secara visual dengan komponen React `KartuBuktiHadir.jsx`).

### 5.5 Keluaran Laporan Resmi & Ekspor
- **Standar Cetak A4** margin 2 cm pada seluruh sisi, `page-break-inside: avoid` pada baris tanda tangan, header tabel berulang — CSS cetak (`@media print`) diporting 1:1 dari `src/gaya/index.css` ke `resources/css/print.css`.
- Blok pengesahan penandatanganan 3 pihak (Pelaksana, Sekdes, Perbekel).
- Ekspor Excel (XLSX) via `maatwebsite/excel` (`RekapKehadiranExport` class), menggantikan library `xlsx` di client.

### 5.6 Kepatuhan Privasi (UU 27/2022 PDP) & Log Audit
- Pengaturan retensi foto kehadiran (default 90 hari) per rapat.
- Pembersihan foto permanen dari storage & DB via **`RetensiFotoService::hapusFotoRapat()`** (pengganti RPC `hapus_foto_rapat_admin`), dijadwalkan otomatis via **Laravel Scheduler** (`app/Console/Kernel.php` — `schedule->command('retensi:bersihkan-foto')->daily()`), plus tombol eksekusi manual di panel Admin.
- Halaman Kebijakan Privasi publik `/privasi` (Blade view statis).
- Log Audit Keamanan interaktif (filter rentang waktu, filter aksi, paginasi) — Livewire component `TabelAuditLog`.

### 5.7 Keseragaman Antarmuka & Footer Sistem
- Footer resmi terintegrasi di seluruh halaman via Blade layout `resources/views/layouts/app.blade.php` (menggantikan komponen footer React yang di-*mount* di setiap route).

---

## 6. Alur Pengguna (User Flows)

*(Alur tidak berubah — hanya mekanisme render yang berbeda: setiap "layar" di bawah adalah Livewire component dengan transisi state di sisi server + Alpine untuk transisi visual halus.)*

### 6.1 Alur Registrasi Peserta di Tablet Kiosk

```
Layar Awal Kiosk (Livewire: KioskRegistrasi)
  │
  ├── Opsi A: Ketik Nama di Kolom Pencarian
  │     └── Muncul rekomendasi nama, jabatan, instansi (wire:model.live.debounce.200ms)
  │
  ├── Opsi B: Klik "Lihat Daftar Nama Undangan"
  │     └── Buka Modal Daftar Undangan (Alpine x-show) -> Cari / Pilih via Paginasi -> Klik "Pilih"
  │
  └── Opsi C: Nama Tidak Ditemukan -> Klik "Nama Saya Belum Terdaftar"
        └── Form Undangan Tambahan (Nama, Jabatan, Instansi, No HP)
  │
  ▼
Layar Tanda Tangan Digital (Alpine.js Canvas Pad — kurva kuadratik halus)
  │
  ▼
Layar Foto Wajah (Opsional, dengan Notifikasi Privasi PDP)
  ├── Ambil Foto Kamera Depan (Alpine + getUserMedia, countdown 3 detik + beep)
  └── atau Klik "Lanjut Tanpa Foto"
  │
  ▼
Kartu Bukti Kehadiran (Nomor Urut, Waktu WITA, Cap Hadir)
  │
  ▼ (Otomatis kembali ke layar awal dalam 8 detik atau tombol Selesai — Alpine setTimeout)
```

---

## 7. Skema Basis Data MySQL

> Diterjemahkan langsung dari skema Postgres/Supabase asli (`supabase/skema_lengkap_terbaru.sql`). Semua nama tabel, kolom, constraint, dan makna bisnis **dipertahankan**; hanya sintaks & tipe data yang disesuaikan ke MySQL 8. UUID Postgres → `CHAR(36)` MySQL diisi via `Str::uuid()` di model (`HasUuids` trait bawaan Laravel 12) agar generator ID tetap kompatibel dengan client Dexie yang membuat ID di sisi kiosk (idempotency key).

### 7.1 Enum → Lookup

MySQL native `ENUM` dipakai langsung (setara Postgres custom type):

```sql
-- peran_pengguna:  ENUM('admin','operator')
-- status_rapat:    ENUM('draft','dibuka','ditutup')
-- sumber_undangan: ENUM('import','tambahan')
-- jalur_checkin:   ENUM('kiosk','mandiri','operator')
```

### 7.2 Struktur Tabel Utama

```sql
-- ============ PROFIL & OPERATOR ============
CREATE TABLE profil (
  id            CHAR(36) PRIMARY KEY,
  user_id       BIGINT UNSIGNED NOT NULL,          -- FK ke tabel users (Laravel auth)
  nama          VARCHAR(255) NOT NULL,
  email         VARCHAR(255),
  peran         ENUM('admin','operator') NOT NULL DEFAULT 'operator',
  aktif         BOOLEAN NOT NULL DEFAULT TRUE,
  dibuat_pada   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============ RAPAT ============
CREATE TABLE rapat (
  id                    CHAR(36) PRIMARY KEY,
  kode                  VARCHAR(4) NOT NULL UNIQUE,   -- validasi regex di App\Rules\KodeRapatValid
  judul                 VARCHAR(255) NOT NULL,
  tanggal               DATE NOT NULL,
  jam_mulai             TIME NULL,
  jam_selesai           TIME NULL,
  tempat                VARCHAR(255) NOT NULL,
  penyelenggara         VARCHAR(255) NOT NULL DEFAULT 'Pemerintah Desa Belega',
  catatan               TEXT NULL,
  status                ENUM('draft','dibuka','ditutup') NOT NULL DEFAULT 'draft',
  pin_kiosk             VARCHAR(255) NULL,             -- hash bcrypt PIN 6 digit
  retensi_hari          INT NOT NULL DEFAULT 90,
  foto_dihapus_pada     TIMESTAMP NULL,
  penandatangan_nama    VARCHAR(255) DEFAULT 'I WAYAN SUDARSANA, S.Sos.',
  penandatangan_jabatan VARCHAR(255) DEFAULT 'Perbekel Belega',
  penandatangan_nip     VARCHAR(50) DEFAULT '',
  penandatangan_lokasi  VARCHAR(255) DEFAULT 'Belega',
  dibuat_oleh           CHAR(36) NOT NULL,
  dibuat_pada           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  diperbarui_pada       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (dibuat_oleh) REFERENCES profil(id)
);

-- ============ UNDANGAN ============
CREATE TABLE undangan (
  id            CHAR(36) PRIMARY KEY,
  rapat_id      CHAR(36) NOT NULL,
  nama          VARCHAR(255) NOT NULL,
  jabatan       VARCHAR(255) DEFAULT '',
  instansi      VARCHAR(255) DEFAULT '',
  hp            VARCHAR(30) DEFAULT '',
  sumber        ENUM('import','tambahan') NOT NULL DEFAULT 'import',
  -- MySQL generated column pengganti Postgres "generated always as ... stored"
  nama_cari     VARCHAR(255) GENERATED ALWAYS AS (
                  LOWER(REGEXP_REPLACE(nama, '[^a-zA-Z0-9 ]', ' '))
                ) STORED,
  urutan        INT NULL,
  dibuat_pada   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rapat_id) REFERENCES rapat(id) ON DELETE CASCADE,
  FULLTEXT KEY ft_nama_cari (nama_cari)              -- untuk pencarian cepat (setara fuzzy search)
);

-- ============ KEHADIRAN ============
CREATE TABLE kehadiran (
  id              CHAR(36) PRIMARY KEY,      -- dibuat client (Alpine/JS UUID), kunci idempoten
  rapat_id        CHAR(36) NOT NULL,
  undangan_id     CHAR(36) NOT NULL,
  ttd_path        VARCHAR(500) NOT NULL,
  foto_path       VARCHAR(500) NULL,
  jalur           ENUM('kiosk','mandiri','operator') NOT NULL DEFAULT 'kiosk',
  perangkat_id    VARCHAR(255) NULL,
  waktu_perangkat TIMESTAMP NULL,
  diwakili_oleh   VARCHAR(255) NULL,
  dibatalkan      BOOLEAN NOT NULL DEFAULT FALSE,
  alasan_batal    TEXT NULL,
  dibuat_pada     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (rapat_id) REFERENCES rapat(id) ON DELETE CASCADE,
  FOREIGN KEY (undangan_id) REFERENCES undangan(id) ON DELETE CASCADE,
  UNIQUE KEY uq_idempoten (id)                        -- menjamin idempotensi check-in
);

-- ============ LOG AUDIT KEAMANAN ============
CREATE TABLE audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  aktor       CHAR(36) NULL,
  aksi        VARCHAR(255) NOT NULL,
  tabel_target VARCHAR(100) NOT NULL,
  baris_id    VARCHAR(100) NULL,
  rincian     JSON NULL,                              -- pengganti jsonb
  dibuat_pada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (aktor) REFERENCES profil(id)
);

-- ============ PENGATURAN SISTEM (Single Row) ============
CREATE TABLE pengaturan_sistem (
  id                INT PRIMARY KEY DEFAULT 1,
  nama_sistem       VARCHAR(255) NOT NULL DEFAULT 'SIABDES Belega',
  subjudul_sistem   VARCHAR(255) NOT NULL DEFAULT 'Sistem Absensi Rapat Kantor Desa Belega',
  nama_desa         VARCHAR(255) NOT NULL DEFAULT 'Pemerintah Desa Belega',
  alamat_desa       VARCHAR(500) DEFAULT 'Jalan Raya Belega, Blahbatuh, Gianyar, Bali — Kode Pos 80581',
  kecamatan         VARCHAR(255) DEFAULT 'Kecamatan Blahbatuh',
  kabupaten         VARCHAR(255) DEFAULT 'Kabupaten Gianyar',
  provinsi          VARCHAR(255) DEFAULT 'Bali',
  logo_url          VARCHAR(500) NULL,
  favicon_url       VARCHAR(500) NULL,
  diperbarui_pada   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  diperbarui_oleh   CHAR(36) NULL,
  FOREIGN KEY (diperbarui_oleh) REFERENCES profil(id)
);

-- ============ RATE LIMIT (opsional, jika masih diperlukan di server Laravel) ============
-- Setara rate_limit_rpc — di Laravel cukup memakai middleware `throttle:` bawaan,
-- tabel ini opsional hanya jika perlu audit granular per-aksi.
CREATE TABLE rate_limit_rpc (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kunci       VARCHAR(255) NOT NULL,
  jumlah      INT NOT NULL DEFAULT 1,
  jendela_mulai TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kunci (kunci)
);
```

> Catatan implementasi: semua tabel di atas dibuat lewat **Laravel Migration files** (`database/migrations/`), bukan ditulis SQL manual — tetapi definisi kolom, default value, constraint, dan cascade behavior di atas **wajib identik** dengan yang tertulis di sini agar tidak ada regresi.

### 7.3 Pemetaan RPC/Prosedur Lama → Service Class Laravel

| RPC/Fungsi Lama (Postgres) | Pengganti di Laravel | Lokasi |
|---|---|---|
| `tambah_operator(email, password, nama, peran)` | `OperatorService::tambahOperator()` | `app/Services/OperatorService.php` |
| `proses_checkin(...)` | `CheckinService::prosesCheckin()` — idempoten via `firstOrCreate` pada `kehadiran.id`, menetapkan `nomor_urut` via `DB::transaction` + `lockForUpdate` | `app/Services/CheckinService.php` |
| `ambil_riwayat_undangan_unik()` | `UndanganRepository::riwayatUnik()` — Eloquent query `distinct()` | `app/Repositories/UndanganRepository.php` |
| `cari_undangan(kode, kueri)` | `PencarianUndanganService::cari()` — full-text `MATCH ... AGAINST` + fallback `LIKE`, tidak mengembalikan kolom `hp` | `app/Services/PencarianUndanganService.php` |
| `daftar_undangan_rapat(kode)` | `UndanganController@daftarPublik()` / Livewire `ModalDaftarUndangan` | `app/Http/Livewire/Kiosk/ModalDaftarUndangan.php` |
| `hapus_foto_rapat_admin(rapat_id)` | `RetensiFotoService::hapusFotoRapat()` | `app/Services/RetensiFotoService.php` |
| `adalah_admin()` (helper RLS) | `App\Policies\*Policy` + `Gate::define('admin', ...)` | `app/Policies/` |

---

## 8. Struktur Direktori Laravel (Target)

```
siabdes-belega-laravel/
├── app/
│   ├── Console/
│   │   └── Commands/RetensiBersihkanFoto.php   (Scheduler harian, ganti cron Edge Function)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── CetakController.php             (Lembar A4 & Ekspor Excel)
│   │   │   └── RegistrasiMandiriController.php (Rute publik /r/{kode})
│   │   ├── Livewire/
│   │   │   ├── Auth/HalamanLogin.php
│   │   │   ├── Dashboard/DashboardRapat.php
│   │   │   ├── Dashboard/TabelKehadiran.php
│   │   │   ├── Dashboard/RingkasanAngka.php
│   │   │   ├── Rapat/DaftarRapat.php
│   │   │   ├── Rapat/FormRapat.php
│   │   │   ├── Rapat/DetailRapat.php
│   │   │   ├── Rapat/ModalDuplikasiRapat.php
│   │   │   ├── Rapat/PanelQr.php
│   │   │   ├── Undangan/TabelUndangan.php
│   │   │   ├── Undangan/FormUndangan.php
│   │   │   ├── Undangan/ImporCsv.php
│   │   │   ├── Undangan/ModalPilihRiwayatUndangan.php
│   │   │   ├── Kiosk/AlurCheckin.php            (state machine: cari→ttd→foto→selesai)
│   │   │   ├── Kiosk/LayarCari.php
│   │   │   ├── Kiosk/LayarTtd.php
│   │   │   ├── Kiosk/LayarFoto.php
│   │   │   ├── Kiosk/LayarSelesai.php
│   │   │   ├── Kiosk/ModalDaftarUndangan.php
│   │   │   ├── Pengaturan/HalamanPengaturan.php
│   │   │   ├── Pengaturan/KelolaOperator.php
│   │   │   ├── Pengaturan/PengaturanIdentitas.php
│   │   │   ├── Pengaturan/PengaturanRetensi.php
│   │   │   └── Pengaturan/TabelAuditLog.php
│   │   └── Middleware/
│   │       ├── PastikanKioskTerkunciPin.php
│   │       └── CatatAuditLog.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Profil.php
│   │   ├── Rapat.php
│   │   ├── Undangan.php
│   │   ├── Kehadiran.php
│   │   ├── AuditLog.php
│   │   └── PengaturanSistem.php
│   ├── Policies/
│   │   ├── RapatPolicy.php
│   │   ├── PengaturanPolicy.php
│   │   └── AuditLogPolicy.php
│   ├── Services/
│   │   ├── OperatorService.php
│   │   ├── CheckinService.php
│   │   ├── PencarianUndanganService.php
│   │   ├── ImporUndanganService.php
│   │   ├── RetensiFotoService.php
│   │   ├── KodeRapatService.php
│   │   └── GambarService.php               (crop tanda tangan, kompresi PNG — porting src/lib/gambar.js)
│   ├── Repositories/
│   │   └── UndanganRepository.php
│   └── Exports/
│       └── RekapKehadiranExport.php        (maatwebsite/excel)
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php           (Footer resmi, header, Tailwind, Alpine, SweetAlert2 CDN)
│   │   ├── layouts/kiosk.blade.php         (Layout khusus fullscreen kiosk)
│   │   ├── livewire/                        (view Blade tiap komponen Livewire di atas)
│   │   ├── cetak/lembar-a4.blade.php       (Format cetak A4 margin 2cm — porting LembarCetak.jsx)
│   │   ├── privasi.blade.php
│   │   └── komponen/
│   │       ├── kartu-bukti-hadir.blade.php
│   │       ├── indikator-langkah.blade.php
│   │       ├── lencana.blade.php
│   │       └── status-jaringan.blade.php
│   ├── css/
│   │   ├── app.css                          (Tailwind entry + token warna kustom)
│   │   └── print.css                        (Aturan cetak A4 — porting src/gaya/index.css)
│   └── js/
│       ├── app.js                           (Alpine.js init, plugin registrasi)
│       ├── alpine/
│       │   ├── tandaTanganPad.js            (Porting KanvasTandaTangan.jsx — kurva kuadratik halus)
│       │   ├── kameraWajah.js               (Porting AmbilFoto.jsx — timer 3 detik, beep, flash)
│       │   └── pencarianDebounce.js
│       └── offline/
│           ├── db.js                        (Dexie.js schema — TETAP dipakai untuk antrean offline kiosk)
│           ├── antrean.js                   (Porting src/lib/antrean.js — sinkronisasi ke Laravel API)
│           └── format.js                    (Locale id-ID & Zona Waktu WITA)
├── database/
│   ├── migrations/                          (lihat §7)
│   └── seeders/
│       ├── ProfilSeeder.php
│       └── PengaturanSistemSeeder.php
├── routes/
│   ├── web.php
│   └── api.php                              (endpoint sinkronisasi antrean offline kiosk)
├── config/
│   └── app.php / filesystems.php (disk storage foto & ttd)
└── tailwind.config.js                       (token warna identik §9)
```

---

## 9. Panduan Desain & Aksesibilitas (Tidak Berubah)

Token dan aturan berikut **wajib dipindahkan apa adanya** ke `tailwind.config.js` sebagai `theme.extend.colors`, tidak ada penyesuaian nilai:

1. **Token Warna Sistem**:
   - `daun` (`#2b6446`): Aksi utama, aksen tombol, status aktif.
   - `daun-tua` (`#1b4530`): Header utama, hover aksi primer.
   - `kertas` (`#f6f7f3`): Latar belakang dokumen dan halaman.
   - `tinta` (`#16241d`): Teks utama berbobot kontras tinggi (≥4.5:1).
   - `pena` (`#1c3a7a`): Tinta tanda tangan dan cap kehadiran stempel dinas.
   - `kuning` (`#9a6408`): Lencana peringatan dan undangan tambahan.
   - `garis` (`#d6d9cd`): Garis batas dan pembagi tabel.
2. **Standar Cetak Fisik**:
   - Kertas: **A4** (210 mm × 297 mm) Portrait.
   - Margin: **2 cm** (20 mm) pada seluruh sisi.
   - Mencegah baris tanda tangan terpotong (`page-break-inside: avoid`).
   - Header tabel berulang otomatis pada setiap halaman lanjutan (`thead` di-`display: table-header-group`).
3. **Paginasi Standar Antarmuka**: diterapkan seragam via Livewire `WithPagination` di seluruh tabel data — Daftar Rapat, Tabel Undangan, Modal Kiosk, Kelola Operator, Log Audit Keamanan. Tampilan tombol paginasi memakai Tailwind styling identik versi lama (`resources/views/vendor/livewire/tailwind.blade.php` dikustomisasi agar sama persis).
4. **Tombol & touch target**: minimum tinggi 48px pada semua kontrol interaktif mode kiosk (kelas Tailwind `min-h-12`).

---

## 10. Daftar Rute Aplikasi

| Rute Laravel | Akses | Deskripsi Halaman | Komponen |
|---|---|---|---|
| `/masuk` | Publik | Login operator & administrator | `HalamanLogin` (Livewire) |
| `/rapat` | `auth` | Daftar rapat desa, buat rapat baru & pencarian | `DaftarRapat` |
| `/rapat/baru` | `auth` | Formulir pembuatan agenda rapat baru | `FormRapat` |
| `/rapat/{id}` | `auth` | Detail rapat, kelola penandatangan, impor undangan | `DetailRapat` |
| `/rapat/{id}/dashboard` | `auth` | Dashboard kehadiran langsung & rekap absensi | `DashboardRapat` |
| `/rapat/{id}/cetak` | `auth` | Lembar cetak laporan daftar hadir resmi A4 | `CetakController@a4` |
| `/kiosk/{kode}` | Middleware `PastikanKioskTerkunciPin` | Mode kiosk meja registrasi peserta rapat | `AlurCheckin` |
| `/r/{kode}` | Publik | Registrasi mandiri via ponsel peserta | `RegistrasiMandiriController` |
| `/pengaturan` | `auth`, `role:admin` | Identitas desa, kelola operator, retensi PDP, audit log | `HalamanPengaturan` |
| `/privasi` | Publik | Dokumen kebijakan privasi & perlindungan data pribadi | Blade statis |
| `/api/sinkron-kehadiran` *(baru)* | Publik (rate-limited) | Endpoint penerima antrean offline dari kiosk (Dexie → MySQL) | `Api\SinkronKehadiranController` |

---

## 11. Strategi Offline-First pada Arsitektur Livewire (Penting)

Perbedaan fundamental yang harus ditangani secara sadar saat migrasi: React SPA bersifat *client-rendered* sehingga alami untuk berjalan offline penuh dengan Dexie.js sebagai sumber kebenaran sementara. **Livewire bersifat server-driven** (setiap interaksi memerlukan round-trip AJAX ke server), sehingga strategi berikut dipakai agar T5 (offline-first) tetap terpenuhi **tanpa mengubah pengalaman pengguna**:

1. **Layar kiosk (`/kiosk/{kode}`) dirender sekali di awal sebagai halaman biasa**, lalu seluruh interaksi kritikal (pencarian nama, kanvas tanda tangan, kamera, penyimpanan sementara) dijalankan **murni di Alpine.js + IndexedDB (Dexie.js tetap dipertahankan)** tanpa bergantung pada Livewire round-trip — persis seperti versi lama, hanya "dibungkus" oleh Blade, bukan React.
2. **Data undangan rapat di-preload** ke IndexedDB saat kiosk dibuka pertama kali (selama online), sehingga pencarian nama tetap berfungsi offline.
3. **Hasil check-in (tanda tangan + foto + metadata) disimpan ke antrean Dexie lokal terlebih dahulu** (`src/js/offline/antrean.js`, logic idem dengan `src/lib/antrean.js` lama), dengan `id` UUID dibuat di client sebagai kunci idempoten.
4. **Sinkronisasi otomatis** berjalan via `navigator.onLine` listener + `setInterval` yang mem-POST antrean ke endpoint `/api/sinkron-kehadiran` (Laravel API, bukan RPC Supabase), yang memanggil `CheckinService::prosesCheckin()` dengan jaminan idempotensi (`INSERT ... ON DUPLICATE KEY UPDATE` berbasis `kehadiran.id`).
5. **Livewire hanya dipakai untuk layar non-kritis-offline**: dashboard operator, manajemen rapat, pengaturan, cetak laporan — semua ini **memang mengasumsikan koneksi tersedia** (operator bekerja di kantor, bukan di meja kiosk saat listrik/internet mati), sesuai perilaku asli.
6. **PWA/Service Worker** tetap dipasang (Laravel + Vite mendukung `vite-plugin-pwa` yang sama) agar shell aplikasi kiosk (HTML/CSS/JS) tetap dapat dimuat tanpa jaringan.

---

## 12. Dependensi & Package Pengganti

| Kebutuhan | Package Lama (React/Supabase) | Package Baru (Laravel) |
|---|---|---|
| Auth & session | `@supabase/supabase-js` (Supabase Auth) | Laravel bawaan (`Auth`, `Hash`) |
| Reactive UI tanpa API terpisah | React state + hooks | **Livewire 3** |
| Interaksi client ringan (canvas, kamera, modal) | React component | **Alpine.js** |
| Styling | Tailwind CSS (Vite) | **Tailwind CSS** (Laravel Vite plugin) — konfigurasi/token identik |
| Import CSV/Excel | `papaparse` | `league/csv` atau parser bawaan `maatwebsite/excel` |
| Export Excel | `xlsx` (client) | `maatwebsite/excel` (server) |
| QR Code | `qrcode` (client) | `simplesoftwareio/simple-qrcode` |
| Notifikasi UI | `sweetalert2` | `sweetalert2` tetap dipakai via CDN/NPM + Alpine trigger (tidak perlu diganti, murni JS library) |
| Offline storage | `dexie` + `dexie-react-hooks` | `dexie` (tanpa `-react-hooks`, dipanggil murni via Alpine/vanilla JS) |
| ID unik client-side | `nanoid` | tetap `nanoid` (via CDN/NPM) atau `crypto.randomUUID()` native |
| Database & ORM | Supabase (Postgres + PostgREST + RLS) | **MySQL 8 + Eloquent ORM** |

---

## 13. Status Migrasi (Rencana per Modul)

| Modul | Status Rencana | Keterangan |
|---|:---:|---|
| Setup Proyek Laravel + Livewire + Alpine + Tailwind + MySQL | **Belum dimulai** | Lihat dokumen prompt bertahap terpisah |
| Migrasi Skema Database (Migration MySQL) | **Belum dimulai** | Sesuai §7 |
| Modul Autentikasi & Operator | **Belum dimulai** | Sesuai §5.1 |
| Modul Rapat & Penandatangan | **Belum dimulai** | Sesuai §5.2 |
| Modul Undangan & Bank Riwayat | **Belum dimulai** | Sesuai §5.3 |
| Modul Registrasi & Kiosk (Offline-First) | **Belum dimulai** | Sesuai §5.4 & §11 — modul paling kompleks, prioritas uji regresi tinggi |
| Modul Cetak & Ekspor | **Belum dimulai** | Sesuai §5.5 |
| Modul Privasi & Audit Log | **Belum dimulai** | Sesuai §5.6 |
| Keseragaman UI & Paginasi | **Belum dimulai** | Sesuai §5.7 & §9 |
| Uji Regresi Paritas Fungsi & Visual | **Belum dimulai** | Bandingkan langsung terhadap versi live sebelum go-live |

---
*Dokumen ini adalah spesifikasi migrasi teknis resmi SIABDES Belega dari stack React + Supabase ke Laravel + Blade + Livewire + Alpine.js + Tailwind CSS + MySQL, disusun berdasarkan pembacaan menyeluruh atas kode sumber dan PRD versi sebelumnya. Tidak ada perubahan logic bisnis maupun desain visual yang dimaksudkan dalam dokumen ini.*
