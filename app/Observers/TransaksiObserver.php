<?php

namespace App\Observers;

use App\Models\{Transaksi, User};
use App\Notifications\HighVoidActivityNotification;
use Illuminate\Notifications\DatabaseNotification;

class TransaksiObserver
{
    public function updated(Transaksi $t): void
    {
        $orig = $t->getOriginal('status');
        if ($orig === 'void' || $t->status !== 'void') return;

        $today = now()->toDateString();
        $count = Transaksi::whereDate('updated_at',$today)->where('status','void')->count();
        $threshold = (int) config('alerts.void_threshold_per_day', 3);

        if ($count < $threshold) return;

        $admin = User::where('role','admin')->where('is_active',1)->first();
        if (!$admin) return;

        $key = "void_burst:{$today}";
        $cool= (int) config('alerts.cooldowns.void_burst', 60);

        $recent = DatabaseNotification::where('type', HighVoidActivityNotification::class)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $admin->id)
            ->where('created_at','>=', now()->subMinutes($cool))
            ->where('data->key', $key)
            ->exists();
        if ($recent) return;

        $notif = new HighVoidActivityNotification($today, $count);
        $admin->notify($notif);
        if ($to = config('alerts.email_to')) {
            \Notification::route('mail',$to)->notify($notif);
        }
    }
}
