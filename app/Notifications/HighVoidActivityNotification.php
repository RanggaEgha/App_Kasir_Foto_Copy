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
        return (new MailMessage)
            ->subject('⚠️ Void/refund berlebih hari ini')
            ->line("Total void/refund hari ini: {$this->count}");
    }
}
