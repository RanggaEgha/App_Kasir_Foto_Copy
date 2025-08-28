<?php

namespace App\Http\Controllers;

use App\Models\{
    Transaksi, TransaksiItem, BarangUnitPrice, KasirShift,
    PaymentRecord, Barang, Jasa, Unit
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use App\Services\Audit;
use App\Models\DiscountRule;

class PembayaranController extends Controller
{
    public function create()
    {
        // Data master
        // Sertakan image_path agar accessor image_url pada model bisa bekerja
        $barangs = Barang::orderBy('nama')->get(['id','nama','image_path']);
        $jasas   = Jasa::orderBy('nama')->get(['id','nama','harga_per_satuan','image_path']);
        $units   = Unit::orderBy('kode')->get(['id','kode']); // fallback/unit default

        // Peta unit/harga/stok per barang (untuk dropdown dinamis)
        $unitPrices = BarangUnitPrice::with('unit:id,kode')
            ->whereIn('barang_id', $barangs->pluck('id'))
            ->get()
            ->groupBy('barang_id')
            ->map(function ($rows) {
                return $rows->map(fn($r) => [
                    'unit_id'   => $r->unit_id,
                    'unit_kode' => $r->unit?->kode,
                    'harga'     => (int) $r->harga,
                    'stok'      => (int) $r->stok,
                ])->values();
            });

        // Diskon otomatis (aturan kuantitas)
        $rules = DiscountRule::where('is_active', true)
            ->orderBy('min_qty')
            ->get(['target_type','target_id','min_qty','discount_type','discount_value']);
        $rulesMap = ['barang'=>[], 'jasa'=>[]];
        foreach ($rules as $r) {
            $key = $r->target_id ?: '*';
            $rulesMap[$r->target_type][$key][] = [
                'min_qty' => (int)$r->min_qty,
                'type'    => $r->discount_type,
                'value'   => (int)$r->discount_value,
            ];
        }

        return view('pos.create', [
            'barangs'            => $barangs,
            'jasas'              => $jasas,
            'units'              => $units,
            'unitPricesByBarang' => $unitPrices,
            'discountRules'      => $rulesMap,
        ]);
    }

    public function store(Request $r)
    {
        // Validasi input
        $data = $r->validate([
            'metode_bayar'        => ['required','in:cash,transfer,qris'],
            'dibayar'             => ['required','integer','min:0'],
            'reference'           => ['nullable','string','max:100'],
            'items'               => ['required','array','min:1'],
            // Diskon invoice (opsional)
            'discount_type'       => ['nullable','in:percent,amount'],
            'discount_value'      => ['nullable','integer','min:0'],
            'discount_reason'     => ['nullable','string','max:255'],
            'coupon_code'         => ['nullable','string','max:50'],

            // Skema item (barang/jasa)
            'items.*.tipe_item'    => ['required','in:barang,jasa'],
            'items.*.barang_id'    => ['nullable','integer','min:1','required_if:items.*.tipe_item,barang'],
            'items.*.jasa_id'      => ['nullable','integer','min:1','required_if:items.*.tipe_item,jasa'],
            'items.*.unit_id'      => ['nullable','integer','required_if:items.*.tipe_item,barang'],
            'items.*.jumlah'       => ['required','integer','min:1'],
            'items.*.harga_satuan' => ['required','integer','min:0'],
            'items.*.discount_type'=> ['nullable','in:percent,amount'],
            'items.*.discount_value'=> ['nullable','integer','min:0'],
        ],[
            'items.*.unit_id.required_if'   => 'Unit wajib dipilih untuk barang.',
            'items.*.barang_id.required_if' => 'Barang belum dipilih.',
            'items.*.jasa_id.required_if'   => 'Jasa belum dipilih.',
        ]);

        $userId  = auth()->id();
        $shiftId = ($data['metode_bayar'] === 'cash')
            ? KasirShift::openBy($userId)->value('id')
            : null;

        // Wajib ada shift saat cash
        if ($data['metode_bayar'] === 'cash' && !$shiftId) {
            return back()->withErrors('Shift kasir belum dibuka untuk pembayaran CASH.')->withInput();
        }

        try {
            $trx = DB::transaction(function () use ($data, $userId, $shiftId) {

                // 1) Header transaksi (awal sebagai draft)
                $trx = Transaksi::create([
                    'kode_transaksi' => $this->generateKode(),
                    'tanggal'        => now(),
                    'metode_bayar'   => $data['metode_bayar'],
                    'status'         => 'draft',
                    'payment_status' => 'unpaid',
                    'total_harga'    => 0,
                    'dibayar'        => 0,
                    'kembalian'      => 0,
                    'shift_id'       => $data['metode_bayar'] === 'cash' ? $shiftId : null,
                ]);

                Audit::log('transaksi.created', $trx, "Membuat draft transaksi {$trx->kode_transaksi}");

                // 2) Detail + potong stok untuk barang
                $grand         = 0; // total setelah diskon per item
                $itemDiscSum   = 0; // akumulasi diskon item
                $itemsSummary  = [];

                // siapkan aturan diskon untuk server-side auto apply
                $ruleRows = DiscountRule::where('is_active', true)
                    ->get(['target_type','target_id','min_qty','discount_type','discount_value']);
                $ruleMap = ['barang'=>[], 'jasa'=>[]];
                foreach ($ruleRows as $rr) {
                    $rk = $rr->target_id ?: '*';
                    $ruleMap[$rr->target_type][$rk][] = $rr;
                }

                foreach ($data['items'] as $row) {
                    $qty   = (int) $row['jumlah'];
                    $harga = (int) $row['harga_satuan'];
                    $gross = $qty * $harga;
                    // diskon per item (manual vs. auto dari aturan — ambil nilai terbesar)
                    $iTypeManual = $row['discount_type'] ?? null;
                    $iValManual  = (int) ($row['discount_value'] ?? 0);
                    $iDiscManual = 0;
                    if ($iTypeManual === 'percent') {
                        $iDiscManual = (int) floor($gross * min(100, max(0,$iValManual)) / 100);
                    } elseif ($iTypeManual === 'amount') {
                        $iDiscManual = min($gross, max(0,$iValManual));
                    }

                    // cari aturan yg cocok (prioritas: target_id spesifik lalu wildcard '*')
                    $rulesSpecific = [];
                    if (($row['tipe_item'] ?? 'barang') === 'barang') {
                        $rulesSpecific = array_merge(
                            $ruleMap['barang'][$row['barang_id'] ?? 0] ?? [],
                            $ruleMap['barang']['*'] ?? []
                        );
                    } else {
                        $rulesSpecific = array_merge(
                            $ruleMap['jasa'][$row['jasa_id'] ?? 0] ?? [],
                            $ruleMap['jasa']['*'] ?? []
                        );
                    }
                    $iTypeAuto = null; $iValAuto = 0; $iDiscAuto = 0;
                    foreach ($rulesSpecific as $rule) {
                        if ($qty < (int)$rule->min_qty) continue;
                        if ($rule->discount_type === 'percent') {
                            $tmp = (int) floor($gross * min(100, max(0,(int)$rule->discount_value)) / 100);
                            if ($tmp > $iDiscAuto) { $iDiscAuto = $tmp; $iTypeAuto='percent'; $iValAuto=(int)$rule->discount_value; }
                        } else {
                            $tmp = min($gross, max(0,(int)$rule->discount_value));
                            if ($tmp > $iDiscAuto) { $iDiscAuto = $tmp; $iTypeAuto='amount'; $iValAuto=(int)$rule->discount_value; }
                        }
                    }

                    // pilih yang terbesar
                    if ($iDiscAuto > $iDiscManual) { $iType = $iTypeAuto; $iVal = $iValAuto; $iDisc = $iDiscAuto; }
                    else { $iType = $iTypeManual; $iVal = $iValManual; $iDisc = $iDiscManual; }
                    $sub = max(0, $gross - $iDisc);
                    $itemDiscSum += $iDisc;
                    $grand += $sub;

                    if ($row['tipe_item'] === 'barang') {
                        // Kunci baris pivot
                        $pivot = BarangUnitPrice::where('barang_id', $row['barang_id'])
                            ->where('unit_id',   $row['unit_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

                        if ($pivot->stok < $qty) {
                            throw new \Exception('Stok tidak cukup untuk salah satu barang.');
                        }

                        // Pakai save() agar event Eloquent terpanggil → observer jalan
                        $oldStock    = (int) $pivot->stok;
                        $pivot->stok = $oldStock - $qty;
                        $pivot->save();
                        $newStock    = (int) $pivot->stok;

                        $pivot->loadMissing('barang:id,nama','unit:id,kode');
                        $barangNama = $pivot->barang?->nama;
                        $unitKode   = $pivot->unit?->kode;

                        Audit::log(
                            event: 'stock.decremented',
                            subject: $pivot,
                            description: "Potong stok {$barangNama} ({$unitKode}) sebanyak {$qty}",
                            properties: [
                                'barang_id'   => (int) $pivot->barang_id,
                                'barang_name' => $barangNama,
                                'unit_id'     => (int) $pivot->unit_id,
                                'unit_kode'   => $unitKode,
                                'qty'         => $qty,
                                'old_stok'    => $oldStock,
                                'new_stok'    => $newStock,
                            ]
                        );

                        $itemsSummary[] = [
                            'type'     => 'barang',
                            'nama'     => $barangNama ?? ('Barang#'.$row['barang_id']),
                            'unit'     => $unitKode,
                            'qty'      => $qty,
                            'harga'    => $harga,
                            'subtotal' => $sub,
                            'discount' => $iDisc,
                        ];
                    } else {
                        $jasaNama = Jasa::find($row['jasa_id'])?->nama;

                        $itemsSummary[] = [
                            'type'     => 'jasa',
                            'nama'     => $jasaNama ?? ('Jasa#'.$row['jasa_id']),
                            'unit'     => null,
                            'qty'      => $qty,
                            'harga'    => $harga,
                            'subtotal' => $sub,
                            'discount' => $iDisc,
                        ];
                    }

                    // Simpan item detail
                    $trx->items()->create([
                        'tipe_item'    => $row['tipe_item'], // barang | jasa
                        'barang_id'    => $row['tipe_item'] === 'barang' ? (int) $row['barang_id'] : null,
                        'jasa_id'      => $row['tipe_item'] === 'jasa'   ? (int) $row['jasa_id']   : null,
                        'unit_id'      => $row['tipe_item'] === 'barang' ? (int) $row['unit_id']   : null,
                        'jumlah'       => $qty,
                        'harga_satuan' => $harga,
                        'discount_type'  => $iType,
                        'discount_value' => $iVal,
                        'discount_amount'=> $iDisc,
                        'subtotal'     => $sub,
                    ]);
                }

                // 2b) Diskon invoice (manual + kupon)
                $invType = $data['discount_type'] ?? null;
                $invVal  = (int) ($data['discount_value'] ?? 0);
                $invDiscManual = 0;
                if ($invType === 'percent')       $invDiscManual = (int) floor($grand * min(100, max(0,$invVal)) / 100);
                elseif ($invType === 'amount')    $invDiscManual = min($grand, max(0,$invVal));
                $coupon = trim((string)($data['coupon_code'] ?? ''));
                $invDiscCoupon = 0;
                if ($coupon !== '') {
                    // Contoh rule sederhana; sesuaikan nanti dg tabel kupon
                    if (strcasecmp($coupon, 'HEMAT10')===0) {
                        $invDiscCoupon = (int) floor($grand * 0.10);
                    } elseif (strcasecmp($coupon, 'POTONG1000')===0) {
                        $invDiscCoupon = min($grand, 1000);
                    }
                }
                $invDiscTotal = min($grand, $invDiscManual + $invDiscCoupon);
                $grandNet     = max(0, $grand - $invDiscTotal);

                // 3) Finalisasi header
                $dibayar = (int) $data['dibayar'];
                $trx->update([
                    'status'         => 'posted',
                    'posted_at'      => now(),
                    'total_harga'    => $grandNet,
                    'dibayar'        => $dibayar,
                    'kembalian'      => max(0, $dibayar - $grandNet),
                    'payment_status' => $dibayar >= $grandNet ? 'paid' : ($dibayar > 0 ? 'partial' : 'unpaid'),
                    'discount_type'  => $invType,
                    'discount_value' => $invVal,
                    'discount_amount'=> $invDiscTotal,
                    'discount_reason'=> $data['discount_reason'] ?? null,
                    'coupon_code'    => $coupon ?: null,
                ]);

                Audit::log(
                    event: 'transaksi.posted',
                    subject: $trx,
                    description: "Posting transaksi {$trx->kode_transaksi}",
                    properties: [
                        'total_harga' => (int) $grandNet,
                        'dibayar'     => $dibayar,
                        'kembalian'   => max(0, $dibayar - $grandNet),
                        'status_bayar'=> $dibayar >= $grandNet ? 'paid' : ($dibayar > 0 ? 'partial' : 'unpaid'),
                        'metode'      => $data['metode_bayar'],
                        'items'       => $itemsSummary,
                        'discount_item_total' => (int) $itemDiscSum,
                        'discount_invoice'    => (int) $invDiscTotal,
                        'coupon_code'         => $coupon ?: null,
                        'discount_reason'     => $data['discount_reason'] ?? null,
                    ]
                );

                // 4) Payment record (jika ada pembayaran langsung)
                // Catatan: untuk QRIS dinamis, biasanya dibayar=0 di sini dan akan dilunasi lewat webhook.
                if ($dibayar > 0) {
                    $payment = PaymentRecord::create([
                        'transaksi_id' => $trx->id,
                        'direction'    => 'in',
                        'method'       => $data['metode_bayar'],
                        'amount'       => $dibayar,
                        'reference'    => $data['reference'] ?? null,
                        'paid_at'      => now(),
                        'shift_id'     => $data['metode_bayar'] === 'cash' ? $shiftId : null,
                        'created_by'   => $userId,
                    ]);

                    Audit::log(
                        event: 'payment.added',
                        subject: $trx,
                        description: "Menambahkan pembayaran {$payment->method} sebesar ".number_format($payment->amount,0,',','.'),
                        properties: [
                            'payment_id'     => (int) $payment->id,
                            'method'         => $payment->method,
                            'amount'         => (int) $payment->amount,
                            'reference'      => $payment->reference,
                            'shift_id'       => $payment->shift_id,
                            'transaksi_kode' => $trx->kode_transaksi,
                        ]
                    );
                }

                return $trx;
            });

            // Opsi: jika metode QRIS dan belum ada nominal masuk, arahkan ke halaman QR (jika route tersedia)
            if (($r->input('metode_bayar') === 'qris') && (int) $r->input('dibayar', 0) === 0 && Route::has('pembayaran.qris')) {
                return redirect()->route('pembayaran.qris', $trx->id)
                    ->with('success', 'QRIS dibuat. Silakan scan untuk membayar.');
            }

            // Default: ke struk
            return redirect()->route('history.receipt', ['transaksi' => $trx, 'print' => 1]);

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function pay(Request $r, Transaksi $transaksi)
    {
        $data = $r->validate([
            'amount'    => ['required','integer','min:1'],
            'method'    => ['required','in:cash,transfer,qris'],
            'reference' => ['nullable','string','max:100'],
        ]);

        try {
            DB::transaction(function () use ($transaksi, $data) {
                $shiftId = $data['method'] === 'cash'
                    ? KasirShift::openBy(auth()->id())->value('id')
                    : null;

                if ($data['method'] === 'cash' && !$shiftId) {
                    throw new \Exception('Shift kasir belum dibuka untuk pembayaran CASH.');
                }

                $payment = PaymentRecord::create([
                    'transaksi_id' => $transaksi->id,
                    'direction'    => 'in',
                    'method'       => $data['method'],
                    'amount'       => (int) $data['amount'],
                    'reference'    => $data['reference'] ?? null,
                    'paid_at'      => now(),
                    'shift_id'     => $shiftId,
                    'created_by'   => auth()->id(),
                ]);

                $dibayarBaru = (int) $transaksi->dibayar + (int) $data['amount'];
                $transaksi->update([
                    'metode_bayar'   => $data['method'],
                    'dibayar'        => $dibayarBaru,
                    'kembalian'      => max(0, $dibayarBaru - (int) $transaksi->total_harga),
                    'payment_status' => $dibayarBaru >= (int) $transaksi->total_harga ? 'paid' : 'partial',
                ]);

                Audit::log(
                    event: 'payment.added',
                    subject: $transaksi,
                    description: "Menambahkan pembayaran {$payment->method} sebesar ".number_format($payment->amount,0,',','.'),
                    properties: [
                        'payment_id'     => (int) $payment->id,
                        'method'         => $payment->method,
                        'amount'         => (int) $payment->amount,
                        'reference'      => $payment->reference,
                        'shift_id'       => $payment->shift_id,
                        'transaksi_kode' => $transaksi->kode_transaksi,
                    ]
                );
            });

            return back()->with('success', 'Pembayaran tambahan tercatat.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    public function void(Request $r, Transaksi $transaksi)
    {
        $data = $r->validate([
            'reason' => ['required','string','max:255']
        ]);

        try {
            DB::transaction(function () use ($transaksi, $data) {
                if ($transaksi->status === 'void') {
                    throw new \Exception('Transaksi sudah dibatalkan.');
                }

                $transaksi->loadMissing('items');

                // Kembalikan stok untuk barang
                foreach ($transaksi->items as $it) {
                    if ($it->tipe_item === 'barang' && $it->unit_id) {
                        $pivot = BarangUnitPrice::where('barang_id', $it->barang_id)
                            ->where('unit_id',   $it->unit_id)
                            ->lockForUpdate()
                            ->first();

                        if ($pivot) {
                            $oldStock = (int) $pivot->stok;
                            $pivot->increment('stok', (int) $it->jumlah); // naik stok (observer tidak wajib)
                            $newStock = $oldStock + (int) $it->jumlah;

                            $pivot->loadMissing('barang:id,nama','unit:id,kode');
                            $barangNama = $pivot->barang?->nama;
                            $unitKode   = $pivot->unit?->kode;

                            Audit::log(
                                event: 'stock.incremented',
                                subject: $pivot,
                                description: "Kembalikan stok {$barangNama} ({$unitKode}) karena void transaksi {$transaksi->kode_transaksi}",
                                properties: [
                                    'barang_id'   => (int) $pivot->barang_id,
                                    'barang_name' => $barangNama,
                                    'unit_id'     => (int) $pivot->unit_id,
                                    'unit_kode'   => $unitKode,
                                    'qty'         => (int) $it->jumlah,
                                    'old_stok'    => $oldStock,
                                    'new_stok'    => $newStock,
                                ]
                            );
                        }
                    }
                }

                // Tandai void
                $transaksi->update([
                    'status'      => 'void',
                    'void_reason' => $data['reason'],
                    'voided_at'   => now(),
                ]);

                Audit::log('transaksi.voided', $transaksi, "Membatalkan transaksi {$transaksi->kode_transaksi}", [
                    'reason' => $data['reason']
                ]);
            });

            return back()->with('success', 'Transaksi di-void & stok dikembalikan.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    protected function generateKode(): string
    {
        // TRX + timestamp + 3 huruf acak → probabilitas tabrakan sangat kecil.
        return 'TRX'.now()->format('YmdHis').Str::upper(Str::random(3));
    }
}
