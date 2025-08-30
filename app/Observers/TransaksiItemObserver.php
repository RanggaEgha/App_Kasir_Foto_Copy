<?php

namespace App\Observers;

use App\Models\{TransaksiItem, PurchaseItem, User};
use App\Notifications\BelowCostSaleNotification;
use Illuminate\Notifications\DatabaseNotification;

class TransaksiItemObserver
{
    public function created(TransaksiItem $i): void
    {
        if ($i->tipe_item !== 'barang' || !$i->barang_id || !$i->unit_id) return;

        // Ambil HPP: harga beli terakhir utk (barang, unit)
        $hpp = (float) PurchaseItem::where('barang_id',$i->barang_id)
                ->where('unit_id',$i->unit_id)
                ->orderByDesc('id')
                ->value('unit_price');

        if ($hpp <= 0) return; // belum ada riwayat beli
        $tol = (float) config('alerts.below_cost_tolerance', 0);
        if ((float)$i->harga >= $hpp - $tol) return; // aman

        $admin = User::where('role','admin')->where('is_active',1)->first();
        if (!$admin) return;

        $key = "below_cost:trx#{$i->transaksi_id}:item#{$i->id}";
        $cool= (int) config('alerts.cooldowns.below_cost', 240);

        $recent = DatabaseNotification::where('type', BelowCostSaleNotification::class)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $admin->id)
            ->where('created_at','>=', now()->subMinutes($cool))
            ->where('data->key',$key)
            ->exists();
        if ($recent) return;

        $notif = new BelowCostSaleNotification($i, $hpp, $key);
        $admin->notify($notif);
        if ($to = config('alerts.email_to')) {
            if (strcasecmp(trim($to), trim((string)$admin->email)) !== 0) {
                \Notification::route('mail',$to)->notify($notif);
            }
        }
    }
}
