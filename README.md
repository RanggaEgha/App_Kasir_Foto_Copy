# Kasir Foto Copy

Aplikasi kasir untuk usaha fotokopi/print & penjualan ATK. Fokus pada input transaksi cepat, kontrol stok per-unit, dan laporan yang mudah dibaca.

> **Catatan ejaan:** proyek ini menggunakan istilah **Omset**.

---

## Fitur Utama

- **Transaksi**
  - Barang & jasa dalam satu struk
  - Diskon, retur/void, pembatalan
- **Stok**
  - Stok per unit (pcs/pack/box) + peringatan **stok rendah**/**stok habis**
- **Dashboard & Laporan**
  - Omset **harian, mingguan, bulanan, tahunan**
  - Perbandingan: hari ini vs kemarin, minggu ini vs minggu lalu, bulan ini vs bulan lalu, tahun ini vs tahun lalu
  - **Top 10** item (harian/bulanan/tahunan) — barang & jasa
  - Grafik: harian (Senin–Minggu), bulanan (Jan–Des), **tahunan otomatis mengikuti tahun berjalan** (mis. 2025–2029 bila tahun sekarang 2025)
  - Ekspor **PDF** & **Excel**
- **Notifikasi**
  - stok_out, stock_low, cash_diff, below_cost, void_burst, daily_summary

---

## Teknologi

- **Laravel** (PHP 8.1+), **MySQL/MariaDB**
- Blade, Bootstrap/CSS kustom, **Chart.js**
- **Carbon** (tanggal), **barryvdh/laravel-dompdf** (PDF), **maatwebsite/excel** (Excel)

---

## Prasyarat

- PHP 8.1+ dan Composer
- MySQL/MariaDB
- Ekstensi PHP yang umum untuk Laravel (OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath)
