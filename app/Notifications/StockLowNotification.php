<?php

namespace App\Notifications;

use App\Models\BarangUnitPrice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\AnonymousNotifiable;

class StockLowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public BarangUnitPrice $pivot,
        public string $key,
        public string $severity = 'low' // 'low' | 'out'
    ) {}

    public function via($notifiable): array
    {
        // On-demand route → kirim email
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }
        // Untuk model User: hanya database (panel). Hindari dobel email.
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $p   = $this->pivot->loadMissing('barang','unit');
        $brg = $p->barang?->nama ?? "Barang #{$p->barang_id}";
        $u   = $p->unit?->kode ?? '-';

        $title = $this->severity === 'out' ? 'Stok habis' : 'Stok menipis';
        $type  = $this->severity === 'out' ? 'stock_out'  : 'stock_low';

        return [
            'type'      => $type,
            'title'     => $title,
            'severity'  => $this->severity,
            'key'       => $this->key,
            'barang'    => $brg,
            'unit'      => $u,
            'stok'      => (int) $p->stok,
            'barang_id' => (int) $p->barang_id,
            'unit_id'   => (int) $p->unit_id,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $p   = $this->pivot->loadMissing('barang','unit');
        $brg = $p->barang?->nama ?? "Barang #{$p->barang_id}";
        $u   = $p->unit?->kode ?? '-';

        $subject = $this->severity === 'out' ? '⛔ Stok habis' : '⚠️ Stok menipis';

        return (new MailMessage)
            ->subject($subject)
            ->line("Barang : {$brg}")
            ->line("Unit   : {$u}")
            ->line('Sisa   : '.(int) $p->stok);
    }
}
