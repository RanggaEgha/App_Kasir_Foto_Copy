<?php

namespace App\Notifications;

use App\Models\TransaksiItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BelowCostSaleNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TransaksiItem $item,
        public float $hpp,
        public string $key // "below_cost:trx#ID:item#ID"
    ) {}

    public function via($n): array { return ['database','mail']; }

    public function toDatabase($n): array {
        $i = $this->item->loadMissing('barang','unit','transaksi');
        return [
            'key'     => $this->key,
            'title'   => 'Penjualan di bawah HPP',
            'trx_id'  => $i->transaksi_id,
            'item_id' => $i->id,
            'barang'  => $i->barang?->nama,
            'unit'    => $i->unit?->kode,
            'harga'   => (float)$i->harga,
            'hpp'     => (float)$this->hpp,
            'type'    => 'below_cost',
        ];
    }

    public function toMail($n): MailMessage {
        $i    = $this->item->loadMissing('barang','unit','transaksi');
        $name = $i->barang?->nama ?? '—';
        $unit = $i->unit?->kode ?? '—';
        $subject = '⚠️ Penjualan di bawah HPP';

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subject'       => $subject,
                'title'         => 'Penjualan di bawah HPP',
                'intro'         => 'Ditemukan transaksi dengan harga jual di bawah HPP.',
                'details_title' => 'Detail Transaksi',
                'details'       => [
                    ['label'=>'Transaksi', 'value'=> '#'.$i->transaksi_id],
                    ['label'=>'Barang',    'value'=> e($name)],
                    ['label'=>'Unit',      'value'=> e($unit)],
                    ['label'=>'Harga',     'value'=> 'Rp'.number_format((float)$i->harga,0,',','.')],
                    ['label'=>'HPP',       'value'=> 'Rp'.number_format($this->hpp,0,',','.')],
                ],
                'accent'        => '#f59e0b',
            ]);
    }
}
