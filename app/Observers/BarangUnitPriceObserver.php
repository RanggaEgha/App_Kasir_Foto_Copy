<?php

namespace App\Observers;

use App\Models\BarangUnitPrice;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use App\Notifications\StockLowNotification;

class BarangUnitPriceObserver
{
    public function saved(BarangUnitPrice $p): void
    {
        $p->loadMissing('unit','barang');

        // Ambil old→new untuk pastikan hanya saat TURUN
        $old = (int) $p->getOriginal('stok');
        $new = (int) ($p->stok ?? 0);
        if ($new >= $old) return; // naik / tidak berubah → abaikan

        // Ambang per unit + fallback
        $map      = (array) config('alerts.stock_low_thresholds', []);
        $fallback = (int) config('alerts.stock_low_threshold', 5);
        $unitCode = $p->unit->kode ?? null;
        $limit    = $unitCode && array_key_exists($unitCode, $map)
            ? (int) $map[$unitCode]
            : $fallback;

        // Tentukan event yang valid (hanya crossing threshold)
        $severity = null;
        if ($new <= 0 && $old > 0) {
            $severity = 'out'; // baru habis
        } elseif ($new <= $limit && $old > $limit) {
            $severity = 'low'; // baru menipis
        }
        if (!$severity) return;

        $admin = User::where('role','admin')->where('is_active',1)->first();
        if (!$admin) return;

        // De-dupe key & cooldown
        $key  = "stock_{$severity}:barang#{$p->barang_id}:unit#{$p->unit_id}";
        $cool = $severity === 'out'
            ? (int) config('alerts.cooldowns.stock_out', 60)   // default 60 menit
            : (int) config('alerts.cooldowns.stock_low', 1440);

        $recent = DatabaseNotification::query()
            ->where('type', StockLowNotification::class)
            ->where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $admin->id)
            ->where('created_at', '>=', now()->subMinutes($cool))
            ->where('data->key', $key)
            ->exists();
        if ($recent) return;

        try {
            $notif = new StockLowNotification($p, $key, $severity);

            // 1) simpan di database untuk panel
            $admin->notify($notif);

            // 2) kirim email ke ADMIN_EMAIL jika berbeda dengan email admin aktif (hindari dobel)
            if ($to = config('alerts.email_to')) {
                if (strcasecmp(trim($to), trim((string)$admin->email)) !== 0) {
                    \Notification::route('mail', $to)->notify($notif);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
