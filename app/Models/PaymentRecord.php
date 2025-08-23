<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class PaymentRecord extends Model
{
    use HasAuditLogs;
    
    protected $fillable = [
        'transaksi_id','direction','method','amount','reference',
        'paid_at','shift_id','created_by'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function transaksi(){ return $this->belongsTo(Transaksi::class); }
    public function shift(){ return $this->belongsTo(KasirShift::class, 'shift_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
}
