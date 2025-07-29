<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;   // ← tambahkan
use App\Models\Supplier;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /* ───── LIST ───── */
    public function index()
    {
        $orders = PurchaseOrder::with('supplier')
                    ->latest()
                    ->get();

        return view('purchases.index', compact('orders'));
    }

    /* ───── FORM TAMBAH ───── */
    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::pluck('name','id'),
            'barangs'   => Barang::pluck('nama','id'),   // ← sudah benar
        ]);
    }

    /* ───── SIMPAN ───── */
   // app/Http/Controllers/PurchaseOrderController.php
public function store(Request $r)
{
    /** 1. VALIDASI ------------------------------------------------------- */
    $data = $r->validate([
        'supplier_id'            => 'required|exists:suppliers,id',
        'invoice_no'             => 'required|string|max:50|unique:purchase_orders,invoice_no',
        'purchase_date'          => 'required|date',
        'payment_method'         => 'required|in:cash,transfer,qris,credit',
        'amount_paid'            => 'nullable|numeric|min:0',
        'notes'                  => 'nullable|string',

        // items.* akan divalidasi manual di bawah
        'items'                  => 'required|array|min:1',
        'items.*.barang_id'      => 'required|exists:barangs,id',
        'items.*.qty'            => 'required|integer|min:1',
        'items.*.price'          => 'required|numeric|min:0',
    ]);

    /** 2. PROSES DALAM TRANSAKSI DB ------------------------------------- */
    DB::transaction(function () use ($data) {

        /* a. Hitung subtotal/total */
        $items    = collect($data['items']);
        $subtotal = $items->sum(fn ($it) => $it['qty'] * $it['price']);
        $discount = 0;     // nanti bisa ambil dari $data jika diperlukan
        $tax      = 0;
        $total    = $subtotal - $discount + $tax;

        /* b. Buat header PO terlebih dahulu */
        $order = PurchaseOrder::create([
            'supplier_id'    => $data['supplier_id'],
            'invoice_no'     => $data['invoice_no'],
            'purchase_date'  => $data['purchase_date'],
            'payment_method' => $data['payment_method'],
            'amount_paid'    => $data['amount_paid'],
            'notes'          => $data['notes'] ?? null,

            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'tax'            => $tax,
            'total'          => $total,
            'change_amount'  => max(0, ($data['amount_paid'] ?? 0) - $total),
            'status'         => ($data['amount_paid'] ?? 0) >= $total
                                ? 'paid' : 'received',
        ]);

        /* c. Simpan item + stock-in */
        foreach ($items as $it) {
            $order->items()->create([
                'barang_id'  => $it['barang_id'],
                'quantity'   => $it['qty'],
                'unit_price' => $it['price'],
                'subtotal'   => $it['qty'] * $it['price'],
            ]);

            // update stok barang
           // jika barang masuk satuan
\App\Models\Barang::find($it['barang_id'])
->increment('stok_satuan', $it['qty']);

        }
    });

    return redirect()->route('purchases.index')
                     ->with('success', 'Purchase Order berhasil disimpan');
}

}
