<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alert & Notification Settings
    |--------------------------------------------------------------------------
    | Dipakai oleh observer stok, notifikasi, dan widget Dashboard.
    | - stock_low_threshold : fallback global jika unit tidak punya ambang khusus
    | - stock_low_thresholds: ambang per unit; tambahkan/ubah sesuai kebutuhan
    */

    // Fallback ambang stok rendah (jika unit belum dispesifik)
    'stock_low_threshold' => (int) env('STOCK_LOW_THRESHOLD', 5),

    // Ambang stok rendah per unit (override fallback di atas)
    'stock_low_thresholds' => [
        'pcs'   => (int) env('STOCK_LOW_THRESHOLD_PCS', env('STOCK_LOW_THRESHOLD', 5)),
        'paket' => (int) env('STOCK_LOW_THRESHOLD_PAKET', 2),
        'lusin' => (int) env('STOCK_LOW_THRESHOLD_LUSIN', 1),
        'box'   => (int) env('STOCK_LOW_THRESHOLD_BOX', 1),
    ],

    // Ambang & toleransi lain
    'cash_diff_threshold'    => (int) env('CASH_DIFF_THRESHOLD', 10000), // selisih kas > Rp10.000
    'void_threshold_per_day' => (int) env('VOID_THRESHOLD_PER_DAY', 3),  // ≥3 void/hari → alert
    'below_cost_tolerance'   => (int) env('BELOW_COST_TOLERANCE', 0),    // jual < HPP + tolerance

    // Cooldowns (menit) untuk mencegah spam notifikasi
    'cooldowns' => [
        'stock_low'  => (int) env('COOLDOWN_STOCK_LOW', 1440), // 1×/hari per SKU+unit
        'stock_out'  => (int) env('COOLDOWN_STOCK_OUT', 60),   // default 60 menit
        'below_cost' => (int) env('COOLDOWN_BELOW_COST', 240), // 4 jam
        'void_burst' => (int) env('COOLDOWN_VOID_BURST', 60),  // 1 jam
        'cash_diff'  => (int) env('COOLDOWN_CASH_DIFF', 0),    // selalu kirim saat terjadi
    ],

    // Ringkasan harian (Scheduler)
    'daily_summary_time' => env('DAILY_SUMMARY_TIME', '21:00'), // WIB

    // Penerima email (opsional). Notifikasi database tetap ke admin.
    'email_to' => env('ADMIN_EMAIL'),
];
