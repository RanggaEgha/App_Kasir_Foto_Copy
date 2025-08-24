<?php

namespace App\Observers;

use App\Models\KasirShift;
use App\Models\User;
use App\Notifications\ShiftDifferenceNotification;

class KasirShiftObserver
{
    public function updated(KasirShift $s): void
    {
        // trigger saat status berubah jadi 'closed'
        $original = $s->getOriginal('status');
        if ($original === 'closed' || $s->status !== 'closed') return;

        $th = (int) config('alerts.cash_diff_threshold', 10000);
        if (abs((int)$s->difference) <= $th) return;

        $key   = "cash_diff:shift#{$s->id}";
        $notif = new ShiftDifferenceNotification($s, $key);

        $admin = User::where('role','admin')->where('is_active',1)->first();
        if (!$admin) return;

        $admin->notify($notif);
        if ($to = config('alerts.email_to')) {
            \Notification::route('mail', $to)->notify($notif);
        }
    }
}
