<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class CashMovement extends Model
{
    use HasAuditLogs;

    protected $fillable = [
        'shift_id', 'direction', 'amount', 'reference', 'note', 'occurred_at', 'created_by'
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function shift(){ return $this->belongsTo(KasirShift::class, 'shift_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
}

