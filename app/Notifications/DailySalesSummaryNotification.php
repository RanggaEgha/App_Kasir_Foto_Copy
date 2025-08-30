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
        $subject = '🧾 Ringkasan Penjualan Harian';
        $details = [
            ['label'=>'Tanggal',          'value'=> e($s['date'])],
            ['label'=>'Omzet (masuk)',    'value'=> 'Rp'.number_format((int)$s['revenue_in'],0,',','.')],
            ['label'=>'Transaksi unik',   'value'=> (int)$s['transaksi_count']],
            ['label'=>'Avg basket',       'value'=> 'Rp'.number_format((int)$s['avg_basket'],0,',','.')],
        ];
        if (!empty($s['top_items'])) {
            $list = '';
            foreach ($s['top_items'] as $row) {
                $list .= '<div>- '.e($row['label']).' — qty '.(float)$row['qty'].' (Rp'.number_format((float)$row['rev'],0,',','.').')</div>';
            }
            $details[] = ['label' => 'Top Items', 'value' => $list];
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subject'       => $subject,
                'title'         => 'Ringkasan penjualan harian',
                'intro'         => 'Berikut ringkasan performa penjualan hari ini.',
                'details_title' => 'Detail Ringkas',
                'details'       => $details,
                'accent'        => '#22c55e',
            ]);
    }
}
