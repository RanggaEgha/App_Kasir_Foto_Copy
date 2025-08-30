<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class HighVoidActivityNotification extends Notification
{
    use Queueable;

    public function __construct(public string $dateKey, public int $count) {}

    public function via($n): array { return ['database','mail']; }

    public function toDatabase($n): array {
        return [
            'key'   => "void_burst:{$this->dateKey}",
            'title' => 'Void/refund berlebih hari ini',
            'count' => $this->count,
            'type'  => 'void_burst',
        ];
    }

    public function toMail($n): MailMessage {
        $subject = '⚠️ Void/refund berlebih hari ini';
        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subject'       => $subject,
                'title'         => 'Void/refund berlebih',
                'intro'         => 'Aktivitas void/refund hari ini melebihi ambang yang ditentukan.',
                'details_title' => 'Ringkasan Hari Ini',
                'details'       => [
                    ['label'=>'Tanggal', 'value'=> e($this->dateKey)],
                    ['label'=>'Total Void/Refund', 'value'=> (int)$this->count],
                ],
                'accent'        => '#f59e0b',
            ]);
    }
}
