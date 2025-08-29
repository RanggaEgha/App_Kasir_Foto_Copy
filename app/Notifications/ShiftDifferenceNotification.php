<?php

namespace App\Notifications;

use App\Models\KasirShift;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ShiftDifferenceNotification extends Notification
{
    use Queueable;

    public function __construct(public KasirShift $shift, public string $key) {}

    public function via($n): array { return ['database','mail']; }

    public function toDatabase($n): array {
        return [
            'key'       => $this->key, // "cash_diff:shift#ID"
            'title'     => 'Selisih kas saat tutup shift',
            'shift_id'  => $this->shift->id,
            'expected'  => (int)$this->shift->expected_cash,
            'closing'   => (int)$this->shift->closing_cash,
            'difference'=> (int)$this->shift->difference,
            'type'      => 'cash_diff',
        ];
    }

    public function toMail($n): MailMessage {
        $s = $this->shift;
        return (new MailMessage)
            ->subject('⚠️ Selisih kas saat tutup shift')
            ->line("Shift #{$s->id} oleh user #{$s->user_id}")
            ->line('Expected: Rp. '.number_format((int)$s->expected_cash,0,',','.'))
            ->line('Closing : Rp. '.number_format((int)$s->closing_cash,0,',','.'))
            ->line('Selisih : Rp. '.number_format((int)$s->difference,0,',','.'));
    }
}
