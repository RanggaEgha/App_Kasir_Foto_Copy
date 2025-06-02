<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{Transaksi, TransaksiItem, Barang, Jasa};

class TransaksiController extends Controller
{
    /* ───────────────── INDEX ───────────────── */
    public function index()
    {
        $transaksis = Transaksi::latest()->paginate(15);
        return view('transaksi.index', compact('transaksis'));
    }

    /* ───────────────── CREATE FORM ─────────── */
    public function create()
    {
        return view('transaksi.create', [
            'barangs' => Barang::orderBy('nama')->get(),
            'jasas'   => Jasa::orderBy('nama')->get(),
        ]);
    }

    /* ───────────────── STORE (SIMPAN) ───────── */
    public function store(Request $r)
    {
        $r->validate([
            'tipe_item'      => 'required|array|min:1',
            'jumlah'         => 'required|array',
            'jumlah.*'       => 'numeric|min:1',
            'dibayar'        => 'required|numeric|min:0',
            'metode_bayar'   => 'required|in:cash,debit,dana',
        ]);

        DB::beginTransaction();
        try {
            /* 1️⃣  HEADER dgn nilai default */
            $trx = Transaksi::create([
                'kode_transaksi' => 'TRX' . time(),
                'tanggal'        => now(),
                'total_harga'    => 0,
                'metode_bayar'   => 'cash',
                'dibayar'        => 0,
                'kembalian'      => 0,
            ]);

            /* 2️⃣  PROSES ITEM */
            $total = 0;
            foreach ($r->tipe_item as $i => $tipe) {

                /* --- BARANG --- */
                if ($tipe === 'barang') {
                    $barang   = Barang::findOrFail($r->barang_id[$i]);
                    $tipeQty  = $r->tipe_qty[$i] ?? 'satuan';
                    $qty      = (int) $r->jumlah[$i];

                    if ($tipeQty === 'satuan') {
                        abort_if($barang->stok_satuan < $qty,
                            422, "Stok pcs {$barang->nama} kurang");
                        $barang->decrement('stok_satuan', $qty);
                        $harga = $barang->harga_satuan;
                    } else {
                        abort_if($barang->stok_paket < $qty,
                            422, "Stok paket {$barang->nama} kurang");
                        $barang->decrement('stok_paket', $qty);
                        $barang->decrement('stok_satuan',
                                           $qty * $barang->isi_per_paket);
                        $harga = $barang->harga_paket;
                    }

                    $sub = $harga * $qty;

                    TransaksiItem::create([
                        'transaksi_id' => $trx->id,
                        'barang_id'    => $barang->id,
                        'tipe_item'    => 'barang',
                        'tipe_qty'     => $tipeQty,
                        'jumlah'       => $qty,
                        'harga_satuan' => $harga,
                        'subtotal'     => $sub,
                    ]);

                    $total += $sub;
                    continue;
                }

                /* --- JASA --- */
                $jasa = Jasa::findOrFail($r->jasa_id[$i]);
                $qty  = (int) $r->jumlah[$i];
                $sub  = $jasa->harga_per_satuan * $qty;

                TransaksiItem::create([
                    'transaksi_id' => $trx->id,
                    'jasa_id'      => $jasa->id,
                    'tipe_item'    => 'jasa',
                    'tipe_qty'     => 'satuan',
                    'jumlah'       => $qty,
                    'harga_satuan' => $jasa->harga_per_satuan,
                    'subtotal'     => $sub,
                ]);

                $total += $sub;
            }

            /* 3️⃣  HITUNG BAYAR & UPDATE HEADER */
            $dibayar   = (int) $r->dibayar;
            $metode    = $r->metode_bayar;
            $kembalian = max(0, $dibayar - $total);

            if ($metode === 'cash' && $dibayar < $total) {
                throw new \Exception('Uang dibayar kurang dari total.');
            }

            $trx->update([
                'total_harga'  => $total,
                'metode_bayar' => $metode,
                'dibayar'      => $dibayar,
                'kembalian'    => $kembalian,
            ]);

            DB::commit();
            return redirect()->route('transaksi.show', $trx->id)
                             ->with('success', 'Transaksi berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    /* ───────────────── SHOW DETAIL ─────────── */
    public function show($id)
    {
        $transaksi = Transaksi::with(['items.barang','items.jasa'])
                     ->findOrFail($id);

        return view('transaksi.show', compact('transaksi'));
    }

    /* ───────────────── CETAK PDF ───────────── */
    public function pdf($id)
    {
        $transaksi = Transaksi::with(['items.barang','items.jasa'])
                     ->findOrFail($id);

        $pdf = Pdf::loadView('transaksi.pdf', compact('transaksi'))
                  ->setPaper('A5', 'portrait');

        return $pdf->stream($transaksi->kode_transaksi . '.pdf');
    }

    /* ───────────────── DELETE ──────────────── */
    public function destroy($id)
    {
        $trx = Transaksi::findOrFail($id);
        $trx->items()->delete();
        $trx->delete();

        return back()->with('success','Transaksi dihapus');
    }
}
