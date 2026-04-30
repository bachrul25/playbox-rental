# PlayBox Rental Management System

Aplikasi manajemen rental PlayBox **Pribadi** dan **Kerjasama (Cafe / Mitra)** yang dibangun dengan stack:

- **Laravel 12** + **Livewire 3** (full-page components, tanpa controller untuk fitur utama)
- **Bootstrap 5 (CDN)** + **Bootstrap Icons (CDN)** untuk UI
- **MySQL** (kompatibel SQLite untuk dev)
- **Chart.js (CDN)** untuk grafik dashboard
- **SweetAlert2 (CDN)** untuk notifikasi
- **barryvdh/laravel-dompdf** untuk export PDF
- **maatwebsite/excel** untuk export Excel

Pola arsitektur: `Route → Livewire Component → Repository → Model → Database`.

## Fitur

1. **Dashboard** dengan ringkasan pendapatan harian/bulanan, jumlah PlayBox aktif, mitra, biaya maintenance, keuntungan owner, grafik pendapatan bulanan, perbandingan pribadi vs kerjasama, dan transaksi terbaru.
2. **Manajemen PlayBox** (CRUD) lengkap dengan status, kepemilikan, dan relasi mitra.
3. **Manajemen Mitra/Cafe** (CRUD) dengan PJ, kontak, status kerjasama.
4. **Transaksi Rental** dengan perhitungan otomatis durasi, total, dan pratinjau bagi hasil:
   - Pribadi: 20% maintenance, 80% keuntungan owner.
   - Kerjasama: potong biaya staff Rp800.000, sisa dibagi 50:50 antara owner & cafe. Peringatan saat pendapatan < biaya staff.
5. **Manajemen Biaya** (maintenance, perawatan, kerusakan, staff, lainnya) dengan ringkasan per tipe.
6. **Laporan Pribadi & Kerjasama** dengan filter periode (harian, mingguan, bulanan, tahunan, kustom), export PDF & Excel.
7. **Manajemen User** dengan role `admin`, `owner`, `mitra`. Mitra hanya melihat laporan kerjasama miliknya.
8. **Login** Livewire + middleware role.

## Struktur Database

Tabel: `users`, `partners`, `playboxes`, `rentals`, `private_reports`, `partnership_reports`, `expenses`. Lihat `database/migrations` untuk detail kolom dan relasi (FK, enum, decimal 14,2).

## Cara Menjalankan

### Persyaratan
- PHP >= 8.3 (`bcmath`, `mbstring`, `xml`, `gd`, `zip`, `pdo_mysql` / `pdo_sqlite`)
- Composer 2.x
- MySQL 8.x (atau SQLite untuk dev cepat)

### Instalasi
```bash
git clone <repo-url> playbox-rental
cd playbox-rental
composer install
cp .env.example .env
php artisan key:generate
```

### Konfigurasi Database

#### MySQL (rekomendasi produksi)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=playbox_rental
DB_USERNAME=root
DB_PASSWORD=
```
```bash
mysql -u root -e "CREATE DATABASE playbox_rental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### SQLite (dev cepat)
```env
DB_CONNECTION=sqlite
```
```bash
touch database/database.sqlite
```

### Migrasi & Seeder
```bash
php artisan migrate:fresh --seed
```

### Menjalankan Aplikasi
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
Buka `http://127.0.0.1:8000` dan login dengan akun demo.

## Akun Demo

| Role  | Email                | Password   | Akses                                   |
|-------|----------------------|------------|------------------------------------------|
| Admin | admin@playbox.com    | password   | Semua data + transaksi + laporan         |
| Owner | owner@playbox.com    | password   | Dashboard, data master, semua laporan    |
| Mitra | mitra@playbox.com    | password   | Hanya laporan kerjasama miliknya         |

## Routing

Semua route langsung ke Livewire component (tanpa controller untuk fitur utama).

| Path                      | Component                          | Akses               |
|---------------------------|------------------------------------|---------------------|
| `/login`                  | `Auth\LoginComponent`              | guest               |
| `/dashboard`              | `DashboardComponent`               | semua role          |
| `/playboxes`              | `PlayboxComponent`                 | admin, owner        |
| `/partners`               | `PartnerComponent`                 | admin, owner        |
| `/rentals`                | `RentalComponent`                  | admin               |
| `/expenses`               | `ExpenseComponent`                 | admin               |
| `/reports/private`        | `PrivateReportComponent`           | admin, owner        |
| `/reports/partnership`    | `PartnershipReportComponent`       | semua (mitra: filter sendiri) |
| `/users`                  | `UserManagementComponent`          | admin               |

## Aturan Perhitungan

Lihat `app/Support/RentalCalculator.php`:
- Pribadi: `maintenance = total × 20%`, `owner = total × 80%`.
- Kerjasama: `staff_cost = Rp800.000`, `net = max(0, total - staff)`, `owner = net × 50%`, `cafe = net - owner`.

## Struktur Folder Penting
```
app/
├── Exports/                # Excel exports (PrivateReportsExport, PartnershipReportsExport)
├── Http/Middleware/        # EnsureRole
├── Livewire/               # Full-page components
│   └── Auth/LoginComponent.php
├── Models/                 # User, Partner, Playbox, Rental, PrivateReport, PartnershipReport, Expense
├── Repositories/           # Repository pattern (DashboardRepository, PlayboxRepository, dll.)
└── Support/                # Rupiah formatter & RentalCalculator
resources/views/
├── exports/                # Blade template untuk PDF (DomPDF)
├── layouts/                # app.blade.php (sidebar+topbar) & guest.blade.php
└── livewire/               # View untuk setiap Livewire component
routes/web.php              # Route langsung ke Livewire component
```
