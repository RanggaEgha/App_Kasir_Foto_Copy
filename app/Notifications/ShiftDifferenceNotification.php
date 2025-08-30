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
        $nama = $s->user?->name ?? '—';
        $subject = 'Selisih kas saat tutup shift';
        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subject'       => $subject,
                'title'         => 'Selisih kas saat tutup shift',
                'intro'         => 'Terjadi selisih kas pada saat penutupan shift.',
                'details_title' => 'Detail Shift',
                'details'       => [
                    ['label'=>'Shift',          'value'=> '#'.$s->id],
                    ['label'=>'Kasir',          'value'=> e($nama)],
                    ['label'=>'Kas Ekspektasi', 'value'=> 'Rp'.number_format((int)$s->expected_cash,0,',','.')],
                    ['label'=>'Kas Akhir',      'value'=> 'Rp'.number_format((int)$s->closing_cash,0,',','.')],
                    ['label'=>'Selisih',        'value'=> 'Rp'.number_format((int)$s->difference,0,',','.')],
                ],
                'accent'        => '#ef4444',
            ]);
    }
}
