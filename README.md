# Kasir Foto Copy
<p align="left">
  <a href="#"><img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white" alt="Laravel"></a>
  <a href="#"><img src="https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black" alt="React"></a>
  <a href="#"><img src="https://img.shields.io/badge/Vite-5-646CFF?logo=vite&logoColor=white" alt="Vite"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white" alt="PHP"></a>
  <a href="#"><img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white" alt="MySQL/MariaDB"></a>
  <a href="#"><img src="https://img.shields.io/badge/Chart.js-4-FF6384?logo=chartdotjs&logoColor=white" alt="Chart.js"></a>
  <a href="#"><img src="https://img.shields.io/badge/Blade-Templating-0A0A0A?logo=laravel&logoColor=white" alt="Blade"></a>
  <a href="#"><img src="https://img.shields.io/badge/CoreUI-Admin-2CA5E0?logo=bootstrap&logoColor=white" alt="CoreUI"></a>
</p>

Aplikasi kasir untuk usaha fotokopi/print & penjualan ATK. UI utama pakai **Blade + CoreUI**, autentikasi pakai **React/Inertia (Breeze)**. Laporan memakai **Chart.js**.


![License: MIT](https://img.shields.io/badge/License-MIT-green)

---

## Fitur

- **Transaksi (POS)**
  - Barang & jasa dalam satu struk; diskon/retur/void.
  - Shift kasir & pencatatan pembayaran (cash/transfer/QRIS).
- **Master Data**
  - Barang dengan **unit harga** (pcs/pack/box, dst), Jasa, Supplier.
  - Purchase Order & Pembelian.
- **Dashboard & Laporan**
  - KPI: Omset **harian** & **mingguan**, item terlaris, stok kritis.
  - **Top 10** item (hari ini) + ekspor **PDF**/**Excel**.
  - Grafik: Chart.js; dapat diperluas ke harian (Senin–Minggu), bulanan (Jan–Des), tahunan (rentang dinamis).
- **Notifikasi**
  - Stok rendah/habis, penjualan di bawah HPP, void tinggi, selisih kas, ringkasan harian (email + database).
- **Penjadwalan**
  - `sales:daily-summary` harian (jam diatur di `config/alerts.php`).

---

## Teknologi

- **Laravel 10**, PHP 8.1+, MySQL/MariaDB
- **Blade + CoreUI** untuk admin/kasir
- **React + Inertia (Breeze)** untuk halaman autentikasi
- **Chart.js** (via CDN) untuk grafik
- **Carbon**, **barryvdh/laravel-dompdf** (PDF), **maatwebsite/excel** (Excel)

---

## Peran & Hak Akses

Aplikasi menggunakan kolom `users.role` (`admin` / `kasir`) dan `users.is_active` untuk mengatur akses.  
Gate: `admin` dan `kasir` (lihat `App\Providers\AuthServiceProvider`).  
Middleware: `role` (404 bila tak berhak) & `active` (paksa logout jika nonaktif).

| Fitur / Menu                                        | Kasir | Admin |
| ---                                                 | :--:  | :--:  |
| **Dashboard (analitik + ekspor PDF/Excel)**         |  —    |  ✅   |
| **Barang** (CRUD + unit harga)                      |  ✅   |  ✅   |
| **Jasa** (CRUD)                                     |  ✅   |  ✅   |
| **POS / Pembayaran** (buat/bayar/void struk)        |  ✅   |  ✅   |
| **Shift Kasir** (buka/tutup, selisih kas)           |  ✅   |  ✅   |
| **History Transaksi** + **Cetak Struk**             |  ✅   |  ✅   |
| **Supplier** (CRUD)                                 |  —    |  ✅   |
| **Purchase Order** (CRUD)                           |  —    |  ✅   |
| **Manajemen User** (buat/edit/role/aktif-nonaktif)  |  —    |  ✅   |
| **Audit Log – Global**                              |  —    |  ✅   |
| **Audit Log – Aktivitas Saya**                      |  ✅   |  ✅   |
| **Notifikasi (in-app) & tandai dibaca**             |  ✅   |  ✅   |
| **Ringkasan Harian via Email (`ADMIN_EMAIL`)**      |  —    |  ✅   |

> Sidebar Blade menggunakan `@can('admin')`, `@can('kasir')`, dan `@canany(...)` untuk menampilkan menu sesuai role.  
> Rute `admin-only`: `/dashboard*`, `/suppliers*`, `/purchases*`, `/users*`, `/audit-logs*`.  
> Rute `kasir,admin`: `/barang*`, `/jasa*`, `/pembayaran*`, `/shift*`, `/history*`, notifikasi, audit pribadi.

---

## Manajemen Pengguna & Role

- **Tambah Admin via Seeder (opsional)**
  1. Set di `.env`:
     ```
     ADMIN_EMAIL=admin@gmail.com
     ADMIN_PASSWORD=123
     ```
  2. Jalankan:
     ```bash
     php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
     ```
  Seeder membuat/menyetel user admin (nama: *Administrator*).

- **Buat/Kelola User (Admin)**
  - Menu **Users** → buat/edit user, pilih **Role** (`admin`/`kasir`) dan **Status** (`Aktif`/`Nonaktif`).
  - `is_active=false` akan memaksa logout user saat login (middleware `active`).

---
