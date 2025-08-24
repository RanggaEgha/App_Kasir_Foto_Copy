<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{PaymentRecord, TransaksiItem, User};
use App\Notifications\DailySalesSummaryNotification;

class SendDailySalesSummary extends Command
{
    protected $signature = 'sales:daily-summary';
    protected $description = 'Kirim ringkasan penjualan harian ke admin';

    public function handle(): int
    {
        $today = now()->toDateString();

        $payments = PaymentRecord::where('direction','in')->whereDate('paid_at',$today);
        $revenue  = (int) $payments->sum('amount');
        $trxCount = (int) $payments->distinct('transaksi_id')->count('transaksi_id');
        $avg      = $trxCount ? (int) floor($revenue / $trxCount) : 0;

        $top = TransaksiItem::with('barang')
            ->whereHas('transaksi', fn($q) => $q->whereDate('created_at',$today))
            ->selectRaw('tipe_item, COALESCE(barang_id, jasa_id) as id, SUM(qty) as qty, SUM(subtotal) as rev')
            ->groupBy('tipe_item','id')
            ->orderByDesc('rev')
            ->limit(5)
            ->get()
            ->map(function($r){
                $label = $r->tipe_item === 'barang'
                    ? ($r->barang->nama ?? 'Barang #'.$r->id)
                    : 'Jasa #'.$r->id;
                return ['label'=>$label, 'qty'=>(float)$r->qty, 'rev'=>(float)$r->rev];
            })->all();

        $summary = [
            'date'            => $today,
            'revenue_in'      => $revenue,
            'transaksi_count' => $trxCount,
            'avg_basket'      => $avg,
            'top_items'       => $top,
        ];

        $admin = User::where('role','admin')->where('is_active',1)->first();
        if ($admin) {
            $notif = new DailySalesSummaryNotification($summary);
            $admin->notify($notif);
            if ($to = config('alerts.email_to')) {
                \Notification::route('mail',$to)->notify($notif);
            }
        }

        $this->info('Daily summary sent.');
        return self::SUCCESS;
    }
}
