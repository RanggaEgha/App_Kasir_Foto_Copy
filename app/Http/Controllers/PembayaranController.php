<?php

namespace App\Http\Controllers;

use App\Models\{
    Transaksi, TransaksiItem, BarangUnitPrice, KasirShift,
    PaymentRecord, Barang, Jasa, Unit
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Audit;

class PembayaranController extends Controller
{
    public function create()
    {
        // Data master
        $barangs = Barang::orderBy('nama')->get(['id','nama']);
        $jasas   = Jasa::orderBy('nama')->get(['id','nama','harga_per_satuan']);
        $units   = Unit::orderBy('kode')->get(['id','kode']);

        // Peta unit/harga/stok per barang (untuk dropdown dinamis)
        $unitPrices = BarangUnitPrice::with('unit:id,kode')
            ->whereIn('barang_id', $barangs->pluck('id'))
            ->get()
            ->groupBy('barang_id')
            ->map(function ($rows) {
                return $rows->map(fn($r) => [
                    'unit_id'   => $r->unit_id,
                    'unit_kode' => $r->unit?->kode,
                    'harga'     => (int)$r->harga,
                    'stok'      => (int)$r->stok,
                ])->values();
            });

        return view('pos.create', [
            'barangs'            => $barangs,
            'jasas'              => $jasas,
            'units'              => $units, // fallback
            'unitPricesByBarang' => $unitPrices,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'metode_bayar' => ['required','in:cash,transfer,qris'],
            'dibayar'      => ['required','integer','min:0'],
            'reference'    => ['nullable','string','max:100'],
            'items'        => ['required','array','min:1'],

            // Skema item (barang/jasa)
            'items.*.tipe_item'    => ['required','in:barang,jasa'],
            'items.*.barang_id'    => ['nullable','integer','min:1','required_if:items.*.tipe_item,barang'],
            'items.*.jasa_id'      => ['nullable','integer','min:1','required_if:items.*.tipe_item,jasa'],
            'items.*.unit_id'      => ['nullable','integer','required_if:items.*.tipe_item,barang'],
            'items.*.jumlah'       => ['required','integer','min:1'],
            'items.*.harga_satuan' => ['required','integer','min:0'],
        ],[
            'items.*.unit_id.required_if'   => 'Unit wajib dipilih untuk barang.',
            'items.*.barang_id.required_if' => 'Barang belum dipilih.',
            'items.*.jasa_id.required_if'   => 'Jasa belum dipilih.',
        ]);

        $userId = auth()->id();
        $shiftId = ($data['metode_bayar'] === 'cash')
            ? KasirShift::openBy($userId)->value('id')
            : null;

        // Wajib ada shift saat cash
        if ($data['metode_bayar'] === 'cash' && !$shiftId) {
            return back()->withErrors('Shift kasir belum dibuka untuk pembayaran CASH.')->withInput();
        }

        try {
            $trx = DB::transaction(function() use ($data, $userId, $shiftId) {

                // 1) Header transaksi (awal sebagai draft)
                $trx = Transaksi::create([
                    'kode_transaksi' => $this->generateKode(),
                    'tanggal'        => now(),
                    'metode_bayar'   => $data['metode_bayar'],  // last method (tercatat)
                    'status'         => 'draft',
                    'payment_status' => 'unpaid',
                    'total_harga'    => 0,
                    'dibayar'        => 0,
                    'kembalian'      => 0,
                    'shift_id'       => $data['metode_bayar'] === 'cash' ? $shiftId : null,
                ]);

                // Log: membuat draft transaksi
                Audit::log(
                    event: 'transaksi.created',
                    subject: $trx,
                    description: "Membuat draft transaksi {$trx->kode_transaksi}"
                );

                // 2) Detail + potong stok untuk barang
                $grand = 0;
                $itemsSummary = []; // ringkasan item untuk audit

                foreach ($data['items'] as $row) {
                    $qty   = (int)$row['jumlah'];
                    $harga = (int)$row['harga_satuan'];
                    $sub   = $qty * $harga;
                    $grand += $sub;

                    if ($row['tipe_item'] === 'barang') {
                        // Ambil pivot stok & kunci baris untuk mencegah race
                        $pivot = BarangUnitPrice::where('barang_id', $row['barang_id'])
                            ->where('unit_id',   $row['unit_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

                        if ($pivot->stok < $qty) {
                            throw new \Exception('Stok tidak cukup untuk salah satu barang.');
                        }

                        $oldStock = (int)$pivot->stok;
                        $pivot->decrement('stok', $qty);
                        $newStock = $oldStock - $qty;

                        // Ambil label untuk menambah detail di log
                        $barangNama = Barang::find($pivot->barang_id)?->nama;
                        $unitKode   = Unit::find($pivot->unit_id)?->kode;

                        // Log: potong stok barang-unit (manual, karena decrement tidak memicu event model)
                        Audit::log(
                            event: 'stock.decremented',
                            subject: $pivot,
                            description: "Potong stok {$barangNama} ({$unitKode}) sebanyak {$qty}",
                            properties: [
                                'barang_id'   => (int)$pivot->barang_id,
                                'barang_name' => $barangNama,
                                'unit_id'     => (int)$pivot->unit_id,
                                'unit_kode'   => $unitKode,
                                'qty'         => $qty,
                                'old_stok'    => $oldStock,
                                'new_stok'    => $newStock,
                            ]
                        );

                        // Tambahkan ke ringkasan item (barang)
                        $itemsSummary[] = [
                            'type'     => 'barang',
                            'nama'     => $barangNama ?? ('Barang#'.$row['barang_id']),
                            'unit'     => $unitKode,
                            'qty'      => $qty,
                            'harga'    => $harga,
                            'subtotal' => $sub,
                        ];
                    } else {
                        // Ringkasan item (jasa)
                        $jasaNama = Jasa::find($row['jasa_id'])?->nama;

                        $itemsSummary[] = [
                            'type'     => 'jasa',
                            'nama'     => $jasaNama ?? ('Jasa#'.$row['jasa_id']),
                            'unit'     => null,
                            'qty'      => $qty,
                            'harga'    => $harga,
                            'subtotal' => $sub,
                        ];
                    }

                    $trx->items()->create([
                        'tipe_item'    => $row['tipe_item'], // barang | jasa
                        'barang_id'    => $row['tipe_item'] === 'barang' ? (int)$row['barang_id'] : null,
                        'jasa_id'      => $row['tipe_item'] === 'jasa'   ? (int)$row['jasa_id']   : null,
                        'unit_id'      => $row['tipe_item'] === 'barang' ? (int)$row['unit_id']   : null,
                        'jumlah'       => $qty,
                        'harga_satuan' => $harga,
                        'subtotal'     => $sub,
                    ]);
                }

                // 3) Finalisasi header: posted + payment status
                $dibayar = (int)$data['dibayar'];
                $trx->update([
                    'status'         => 'posted',
                    'posted_at'      => now(),
                    'total_harga'    => $grand,
                    'dibayar'        => $dibayar,
                    'kembalian'      => max(0, $dibayar - $grand),
                    'payment_status' => $dibayar >= $grand ? 'paid' : ($dibayar > 0 ? 'partial' : 'unpaid'),
                ]);

                // Log: posting transaksi (sertakan ringkasan item terjual)
                Audit::log(
                    event: 'transaksi.posted',
                    subject: $trx,
                    description: "Posting transaksi {$trx->kode_transaksi}",
                    properties: [
                        'total_harga' => (int)$grand,
                        'dibayar'     => $dibayar,
                        'kembalian'   => max(0, $dibayar - $grand),
                        'status_bayar'=> $dibayar >= $grand ? 'paid' : ($dibayar > 0 ? 'partial' : 'unpaid'),
                        'metode'      => $data['metode_bayar'],
                        'items'       => $itemsSummary, // <--- ringkasan item untuk tampilan Aktivitas
                    ]
                );

                // 4) Catat payment record jika ada pembayaran
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

                    // Log: pembayaran awal
                    Audit::log(
                        event: 'payment.added',
                        subject: $trx,
                        description: "Menambahkan pembayaran {$payment->method} sebesar ".number_format($payment->amount,0,',','.'),
                        properties: [
                            'payment_id'     => (int)$payment->id,
                            'method'         => $payment->method,
                            'amount'         => (int)$payment->amount,
                            'reference'      => $payment->reference,
                            'shift_id'       => $payment->shift_id,
                            'transaksi_kode' => $trx->kode_transaksi,
                        ]
                    );
                }

                return $trx;
            });

            return redirect()->route('history.show', $trx)->with('success','Transaksi tersimpan.');

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
            DB::transaction(function() use ($transaksi, $data) {
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
                    'amount'       => (int)$data['amount'],
                    'reference'    => $data['reference'] ?? null,
                    'paid_at'      => now(),
                    'shift_id'     => $shiftId,
                    'created_by'   => auth()->id(),
                ]);

                $dibayarBaru = (int)$transaksi->dibayar + (int)$data['amount'];
                $transaksi->update([
                    'metode_bayar'   => $data['method'], // update last method (opsional)
                    'dibayar'        => $dibayarBaru,
                    'kembalian'      => max(0, $dibayarBaru - (int)$transaksi->total_harga),
                    'payment_status' => $dibayarBaru >= (int)$transaksi->total_harga ? 'paid' : 'partial',
                ]);

                // Log: pembayaran tambahan
                Audit::log(
                    event: 'payment.added',
                    subject: $transaksi,
                    description: "Menambahkan pembayaran {$payment->method} sebesar ".number_format($payment->amount,0,',','.'),
                    properties: [
                        'payment_id'     => (int)$payment->id,
                        'method'         => $payment->method,
                        'amount'         => (int)$payment->amount,
                        'reference'      => $payment->reference,
                        'shift_id'       => $payment->shift_id,
                        'transaksi_kode' => $transaksi->kode_transaksi,
                    ]
                );
            });

            return back()->with('success','Pembayaran tambahan tercatat.');

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
            DB::transaction(function() use ($transaksi, $data) {
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
                            $oldStock = (int)$pivot->stok;
                            $pivot->increment('stok', (int)$it->jumlah);
                            $newStock = $oldStock + (int)$it->jumlah;

                            // Ambil label
                            $barangNama = Barang::find($pivot->barang_id)?->nama;
                            $unitKode   = Unit::find($pivot->unit_id)?->kode;

                            // Log: kembalikan stok (void)
                            Audit::log(
                                event: 'stock.incremented',
                                subject: $pivot,
                                description: "Kembalikan stok {$barangNama} ({$unitKode}) karena void transaksi {$transaksi->kode_transaksi}",
                                properties: [
                                    'barang_id'   => (int)$pivot->barang_id,
                                    'barang_name' => $barangNama,
                                    'unit_id'     => (int)$pivot->unit_id,
                                    'unit_kode'   => $unitKode,
                                    'qty'         => (int)$it->jumlah,
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

                // Log: transaksi void
                Audit::log(
                    event: 'transaksi.voided',
                    subject: $transaksi,
                    description: "Membatalkan transaksi {$transaksi->kode_transaksi}",
                    properties: [
                        'reason' => $data['reason']
                    ]
                );
            });

            return back()->with('success','Transaksi di-void & stok dikembalikan.');

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    protected function generateKode(): string
    {
        return 'TRX' . now()->format('YmdHis') . Str::upper(Str::random(3));
    }
}
