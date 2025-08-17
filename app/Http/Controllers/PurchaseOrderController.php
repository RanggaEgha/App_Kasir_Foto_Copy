<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /** Utility: normalisasi "Rp 5.000.000,50" -> 5000000.50 */
    private function toNumber($v): float {
        if ($v === null || $v === '') return 0.0;
        $v = preg_replace('/[^0-9,.\-]/','', (string)$v); // buang "Rp" & lain2
        $v = str_replace('.','', $v);                     // hapus pemisah ribuan
        $v = str_replace(',','.', $v);                    // koma -> titik
        return (float)$v;
    }

  public function index()
{
    // Ambil supplier + items (beserta unit) dan hitung jumlah item per PO
    $orders = \App\Models\PurchaseOrder::with(['supplier', 'items.unit'])
        ->withCount('items')
        ->latest('id')
        ->paginate(15);

    return view('purchases.index', compact('orders'));
}


    public function create() {
        // >>> INI PENTING: kirim data untuk dropdown <<<
        $suppliers = Supplier::orderBy('name')->get(['id','name']);
        $barangs   = Barang::orderBy('nama')->get(['id','nama']);
        $units     = Unit::orderBy('kode')->get(['id','kode']);

        return view('purchases.create', compact('suppliers','barangs','units'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_no'  => 'required|string|max:100',
            'tanggal'     => 'nullable|date',
            'metode_bayar'=> 'required|in:tunai,transfer,tempo',
            'discount'    => 'nullable',
            'tax_percent' => 'nullable',
            'items'       => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.unit_id'   => 'required|exists:units,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.price'     => 'required',
        ]);

        DB::transaction(function () use ($data) {
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'invoice_no'  => $data['invoice_no'],
                'tanggal'     => $data['tanggal'] ?? now(),
                'metode_bayar'=> $data['metode_bayar'],
                'discount'    => $this->toNumber($data['discount'] ?? 0),
                'tax_percent' => (float)($data['tax_percent'] ?? 0),
            ]);

            // Simpan item
            $itemsPayload = [];
            foreach ($data['items'] as $it) {
                $qty   = (int)$it['qty'];
                $price = $this->toNumber($it['price']);
                $itemsPayload[] = new PurchaseItem([
                    'barang_id'  => $it['barang_id'],
                    'unit_id'    => $it['unit_id'],
                    'qty'        => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $qty * $price,
                ]);
            }
            $po->items()->saveMany($itemsPayload);

            // Hitung total
            $po->load('items');
            $sub = $po->items->sum('subtotal');
            $disc = $po->discount ?? 0;
            $taxPct = $po->tax_percent ?? 0;

            $taxBase = max(0, $sub - $disc);
            $taxAmt  = round($taxBase * ($taxPct/100), 2);
            $po->subtotal    = $sub;
            $po->tax_amount  = $taxAmt;
            $po->grand_total = $taxBase + $taxAmt;
            $po->save();

            // Update harga & stok di barang_unit_prices
            foreach ($po->items as $it) {
                DB::table('barang_unit_prices')->updateOrInsert(
                    ['barang_id' => $it->barang_id, 'unit_id' => $it->unit_id],
                    [
                        'harga'      => $it->unit_price,
                        'stok'       => DB::raw('COALESCE(stok,0) + '.(int)$it->qty),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        });

        return redirect()->route('purchases.index')->with('success','Purchase Order tersimpan.');
    }

  // app/Http/Controllers/PurchaseOrderController.php
public function show(PurchaseOrder $purchase)
{
    $purchase->load(['supplier','items.barang','items.unit']);
    return view('purchases.show', ['po' => $purchase]);
}


    public function edit(PurchaseOrder $purchase) {
        $purchase->load(['items.barang','items.unit']);

        // >>> INI PENTING: kirim data untuk dropdown <<<
        $suppliers = Supplier::orderBy('name')->get(['id','name']);
        $barangs   = Barang::orderBy('nama')->get(['id','nama']);
        $units     = Unit::orderBy('kode')->get(['id','kode']);

        return view('purchases.edit', [
            'po'        => $purchase,
            'suppliers' => $suppliers,
            'barangs'   => $barangs,
            'units'     => $units,
        ]);
    }

    public function update(Request $r, PurchaseOrder $purchase) {
        $data = $r->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_no'  => 'required|string|max:100',
            'tanggal'     => 'nullable|date',
            'metode_bayar'=> 'required|in:tunai,transfer,tempo',
            'discount'    => 'nullable',
            'tax_percent' => 'nullable',
            'items'       => 'required|array|min:1',
            'items.*.id'        => 'nullable|exists:purchase_items,id',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.unit_id'   => 'required|exists:units,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.price'     => 'required',
        ]);

        DB::transaction(function () use ($purchase, $data) {
            $purchase->update([
                'supplier_id' => $data['supplier_id'],
                'invoice_no'  => $data['invoice_no'],
                'tanggal'     => $data['tanggal'] ?? $purchase->tanggal,
                'metode_bayar'=> $data['metode_bayar'],
                'discount'    => $this->toNumber($data['discount'] ?? 0),
                'tax_percent' => (float)($data['tax_percent'] ?? 0),
            ]);

            // Sinkronisasi items
            $keepIds = [];
            foreach ($data['items'] as $it) {
                $payload = [
                    'barang_id'  => $it['barang_id'],
                    'unit_id'    => $it['unit_id'],
                    'qty'        => (int)$it['qty'],
                    'unit_price' => $this->toNumber($it['price']),
                ];
                if (!empty($it['id'])) {
                    $item = PurchaseItem::find($it['id']);
                    $item->update($payload);
                    $keepIds[] = $item->id;
                } else {
                    $item = $purchase->items()->create($payload);
                    $keepIds[] = $item->id;
                }
            }
            $purchase->items()->whereNotIn('id',$keepIds)->delete();

            // Recalc totals
            $purchase->load('items');
            $sub = $purchase->items->sum('subtotal');
            $disc = $purchase->discount ?? 0;
            $taxPct = $purchase->tax_percent ?? 0;
            $taxBase = max(0, $sub - $disc);
            $taxAmt  = round($taxBase * ($taxPct/100), 2);
            $purchase->subtotal    = $sub;
            $purchase->tax_amount  = $taxAmt;
            $purchase->grand_total = $taxBase + $taxAmt;
            $purchase->save();
        });

        return redirect()->route('purchases.show',$purchase)->with('success','Purchase Order diperbarui.');
    }

    public function destroy(PurchaseOrder $purchase) {
        DB::transaction(function () use ($purchase) {
            // Kembalikan stok (kurangi)
            foreach ($purchase->items as $it) {
                DB::table('barang_unit_prices')
                    ->where(['barang_id' => $it->barang_id, 'unit_id' => $it->unit_id])
                    ->update([
                        'stok'       => DB::raw('GREATEST(COALESCE(stok,0) - '.(int)$it->qty.', 0)'),
                        'updated_at' => now(),
                    ]);
            }

            // Hapus anak lalu header (aman walau FK sudah cascade)
            $purchase->items()->delete();
            $purchase->delete();
        });

        return back()->with('success','Purchase Order dihapus.');
    }
}
