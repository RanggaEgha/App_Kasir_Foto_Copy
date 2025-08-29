<?php

namespace App\Http\Controllers;

use App\Models\{Transaksi, BarangUnitPrice};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryTransaksiController extends Controller
{
    /**
     * Daftar transaksi + filter ringkas via query string.
     */
    public function index(Request $r)
    {
        // Filter/kelompok dari query string
        $status  = $r->string('status')->toString() ?: null;     // draft|posted|void
        $payment = $r->string('payment')->toString() ?: null;    // unpaid|partial|paid
        $group   = $r->string('group')->toString() ?: null;      // draft|not_paid|posted|paid|void

        $q = Transaksi::query();

        // Kelompok cepat
        if ($group === 'draft') {
            $q->where('status', 'draft');
        } elseif ($group === 'not_paid') {
            $q->whereIn('payment_status', ['unpaid', 'partial']);
        } elseif ($group === 'paid') {
            $q->where('payment_status', 'paid');
        } elseif ($group === 'posted') {
            $q->where('status', 'posted');
        } elseif ($group === 'void') {
            $q->where('status', 'void');
        }

        // Filter spesifik (opsional) — asumsi ada local scope Transaksi::filter($status,$payment)
        $q->filter($status, $payment);

        $transaksis = $q
            ->with(['payments:id,transaksi_id,direction,amount'])
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Hitung ringkasan badge
        $byStatus  = Transaksi::selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $byPayment = Transaksi::selectRaw('payment_status, COUNT(*) c')->groupBy('payment_status')->pluck('c', 'payment_status');

        $shiftOpen = \App\Models\KasirShift::openBy(auth()->id())->exists();

        return view('history.index', [
            'transaksis' => $transaksis,
            'status'     => $status,
            'payment'    => $payment,
            'group'      => $group,
            'byStatus'   => $byStatus,
            'byPayment'  => $byPayment,
            'shiftOpen'  => $shiftOpen,
        ]);
    }

    /**
     * Detail transaksi (untuk tampilan biasa).
     */
    public function show(Transaksi $transaksi)
    {
        $transaksi->load([
            'items.barang',
            'items.jasa',
            'items.unit',
            'payments' => fn ($q) => $q->orderBy('paid_at', 'asc'),
            'shift',
        ]);

        return view('history.show', compact('transaksi'));
    }

    /**
     * Halaman PDF (sudah ada di proyek).
     */
    public function pdf(Transaksi $transaksi)
    {
        $transaksi->load([
            'items.barang',
            'items.jasa',
            'items.unit',
            'payments' => fn ($q) => $q->orderBy('paid_at', 'asc'),
            'shift',
        ]);

        return view('history.pdf', compact('transaksi'));
    }

    /**
     * STRUK / RECEIPT (HTML siap print untuk printer thermal).
     * Rute: GET /history/{transaksi}/receipt
     */
    public function receipt(Transaksi $transaksi)
    {
        $transaksi->load([
            'items.barang',
            'items.jasa',
            'items.unit',
            'payments' => fn ($q) => $q->orderBy('paid_at', 'asc'),
            'shift',
        ]);

        return view('history.receipt', compact('transaksi'));
    }

    /**
     * Posting transaksi Draft:
     * mode=soft  -> hanya tandai posted (TIDAK memotong stok; aman untuk data lama)
     * mode=hard  -> hitung ulang total dari item & potong stok untuk item BARANG
     */
    public function post(Request $r, Transaksi $transaksi)
    {
        $data = $r->validate([
            'mode' => ['required', 'in:soft,hard'],
        ]);

        if ($transaksi->status === 'posted') {
            return back()->withErrors('Transaksi sudah posted.');
        }
        if ($transaksi->status === 'void') {
            return back()->withErrors('Transaksi sudah void, tidak bisa diposting.');
        }

        try {
            DB::transaction(function () use ($transaksi, $data) {
                $transaksi->loadMissing('items');

                // Hitung grand total dari item
                $grand = 0;
                foreach ($transaksi->items as $it) {
                    $grand += ((int) $it->jumlah) * ((int) $it->harga_satuan);
                }

                // mode hard -> potong stok untuk item BARANG
                if ($data['mode'] === 'hard') {
                    foreach ($transaksi->items as $it) {
                        if ($it->tipe_item === 'barang' && $it->unit_id && $it->barang_id) {
                            $pivot = BarangUnitPrice::where('barang_id', $it->barang_id)
                                ->where('unit_id',   $it->unit_id)
                                ->lockForUpdate()
                                ->first();

                            if (! $pivot) {
                                throw new \Exception('Data unit/harga barang tidak ditemukan.');
                            }
                            if ($pivot->stok < $it->jumlah) {
                                throw new \Exception('Stok tidak cukup untuk item: '.($it->barang->nama ?? ''));
                            }
                            $pivot->decrement('stok', (int) $it->jumlah);
                        }
                    }
                }

                // Update header
                $dibayar        = (int) $transaksi->dibayar;
                $paymentStatus  = $dibayar >= $grand ? 'paid' : ($dibayar > 0 ? 'partial' : 'unpaid');

                $transaksi->update([
                    'status'         => 'posted',
                    'posted_at'      => now(),
                    'total_harga'    => $grand,
                    'kembalian'      => max(0, $dibayar - $grand),
                    'payment_status' => $paymentStatus,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }

        return back()->with('success', 'Transaksi berhasil diposting.');
    }
}
