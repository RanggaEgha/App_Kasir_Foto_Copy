# Kasir Foto Copy

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
