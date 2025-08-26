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

---

## Fitur

- **Transaksi (POS)**
  - Barang & jasa dalam satu struk, diskon/retur/void.
  - Shift kasir & pencatatan pembayaran (cash/transfer/QRIS).
- **Master Data**
  - Barang dengan **unit harga** (pcs/pack/box, dst), Jasa, Supplier.
  - Purchase Order & Pembelian.
- **Dashboard & Laporan**
  - KPI: Omset **harian** & **mingguan**, item terlaris, stok kritis.
  - **Top 10** item (hari ini) + ekspor **PDF**/**Excel**.
  - Grafik: Chart.js (harian/mingguan/bulanan/tahunan dapat diperluas).
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
- **Carbon**, **barryvdh/laravel-dompdf**, **maatwebsite/excel**
- 
## Tech Stack

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white&style=for-the-badge" alt="Laravel" />
  <img src="https://img.shields.io/badge/React-18-61DAFB?logo=react&logoColor=black&style=for-the-badge" alt="React" />
  <img src="https://img.shields.io/badge/Vite-5-646CFF?logo=vite&logoColor=white&style=for-the-badge" alt="Vite" />
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white&style=for-the-badge" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL%2F-MariaDB-4479A1?logo=mysql&logoColor=white&style=for-the-badge" alt="MySQL/MariaDB" />
  <img src="https://img.shields.io/badge/Chart.js-4-FF6384?logo=chartdotjs&logoColor=white&style=for-the-badge" alt="Chart.js" />
  <img src="https://img.shields.io/badge/Blade-Templating-0A0A0A?logo=laravel&logoColor=white&style=for-the-badge" alt="Blade" />
  <img src="https://img.shields.io/badge/CoreUI-Admin-2CA5E0?logo=bootstrap&logoColor=white&style=for-the-badge" alt="CoreUI" />
</p>
