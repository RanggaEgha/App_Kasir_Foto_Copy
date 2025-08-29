<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DailySalesSummaryNotification extends Notification
{
    use Queueable;

    public function __construct(public array $summary) {}

    public function via($n): array { return ['database','mail']; }

    public function toDatabase($n): array {
        return [
            'key'   => 'daily_summary:'.date('Y-m-d'),
            'title' => 'Ringkasan penjualan harian',
            'data'  => $this->summary,
            'type'  => 'daily_summary',
        ];
    }

    public function toMail($n): MailMessage {
        $s = $this->summary;
        $msg = (new MailMessage)
          ->subject('🧾 Daily Sales Summary')
          ->line('Tanggal: '.$s['date'])
          ->line('Omzet (masuk): Rp'.number_format($s['revenue_in'],0,',','.'))
          ->line('Transaksi unik : '.$s['transaksi_count'])
          ->line('Avg basket     : Rp'.number_format($s['avg_basket'],0,',','.'));

        if (!empty($s['top_items'])) {
            $msg->line('Top Items (5):');
            foreach ($s['top_items'] as $row) {
                $msg->line('- '.$row['label'].' — qty '.$row['qty'].' (Rp'.number_format($row['rev'],0,',','.').')');
            }
        }
        return $msg;
    }
}
