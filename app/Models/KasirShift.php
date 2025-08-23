<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class KasirShift extends Model
{
    use HasAuditLogs;
    
    protected $fillable = [
        'user_id','opened_at','opening_cash','closed_at',
        'closing_cash','expected_cash','difference','status','notes'
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


     public function payments()
     {
         return $this->hasMany(PaymentRecord::class, 'shift_id');
     }

     public function transaksis()
     {
         return $this->hasMany(Transaksi::class, 'shift_id');
     }

    public function scopeOpenBy($q, $userId)
    {
        return $q->where('user_id', $userId)->where('status', 'open');
    }
}
