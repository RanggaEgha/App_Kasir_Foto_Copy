<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class Transaksi extends Model
{
    use HasAuditLogs;
    /** Kolom yang boleh mass-assign */
    protected $fillable = [
        'kode_transaksi',
        'tanggal',
        'metode_bayar',

        'status',           // draft | posted | void
        'payment_status',   // unpaid | partial | paid

        // diskon invoice
        'discount_type',    // percent | amount | null
        'discount_value',
        'discount_amount',
        'discount_reason',
        'coupon_code',

        'total_harga',
        'dibayar',
        'kembalian',

        'posted_at',
        'voided_at',
        'void_reason',

        'shift_id',
    ];

    /** Cast atribut */
    protected $casts = [
        'tanggal'     => 'datetime',
        'posted_at'   => 'datetime',
        'voided_at'   => 'datetime',
        'total_harga' => 'integer',
        'dibayar'     => 'integer',
        'kembalian'   => 'integer',
        'shift_id'    => 'integer',
        'discount_value'  => 'integer',
        'discount_amount' => 'integer',
    ];

    /** ========== RELASI ========== */
    public function items()
    {
        return $this->hasMany(TransaksiItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentRecord::class, 'transaksi_id');
    }

    public function shift()
    {
        return $this->belongsTo(KasirShift::class, 'shift_id');
    }

    /** ========== SCOPES ========== */

    /**
     * Filter sederhana berdasarkan status & payment_status.
     * Contoh: Transaksi::filter('posted','partial')->get();
     */
    public function scopeFilter($query, ?string $status = null, ?string $payment = null)
    {
        if ($status)  $query->where('status', $status);
        if ($payment) $query->where('payment_status', $payment);
        return $query;
    }

    /** Beberapa helper scope opsional */
    public function scopeDraft($q)   { return $q->where('status','draft'); }
    public function scopePosted($q)  { return $q->where('status','posted'); }
    public function scopeVoid($q)    { return $q->where('status','void'); }
    public function scopePaid($q)    { return $q->where('payment_status','paid'); }
    public function scopePartial($q) { return $q->where('payment_status','partial'); }
    public function scopeUnpaid($q)  { return $q->where('payment_status','unpaid'); }
}
