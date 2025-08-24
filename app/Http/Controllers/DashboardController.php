<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Notifications\DatabaseNotification as Notification;
use App\Exports\DashboardExport;
use App\Models\{Transaksi, TransaksiItem, Barang};

class DashboardController extends Controller
{
    public function index()
    {
        [$harian, $mingguan] = $this->ringkasOmzet();

        // Tampilkan 10 notifikasi terbaru secara global (semua user)
        $notifications = Notification::orderByDesc('created_at')->limit(10)->get();

        return view('dashboard', [
            'harian'        => $harian,
            'mingguan'      => $mingguan,
            'topItems'      => $this->topItems(),
            'stokKritis'    => $this->stokKritis(),
            'notifications' => $notifications,
        ]);
    }

    private function ringkasOmzet(): array
    {
        $today = Carbon::today();
        $week  = Carbon::today()->subDays(6);

        return [
            (int) Transaksi::where('status','posted')->whereDate('tanggal', $today)->sum('total_harga'),
            (int) Transaksi::where('status','posted')->whereDate('tanggal', '>=', $week)->sum('total_harga'),
        ];
    }

    private function topItems()
    {
        return TransaksiItem::selectRaw("
                COALESCE(barangs.nama, jasas.nama) AS nama,
                SUM(transaksi_items.jumlah)  AS qty,
                SUM(transaksi_items.subtotal) AS omzet
            ")
            ->join('transaksis', 'transaksis.id', '=', 'transaksi_items.transaksi_id')
            ->leftJoin('barangs', 'barangs.id', '=', 'transaksi_items.barang_id')
            ->leftJoin('jasas',   'jasas.id',   '=', 'transaksi_items.jasa_id')
            ->where('transaksis.status', 'posted')
            ->groupBy('nama')
            ->orderByDesc('omzet')
            ->limit(10)
            ->get();
    }

    private function stokKritis()
    {
        $map       = (array) config('alerts.stock_low_thresholds', []); // mis. ['pcs'=>5,'paket'=>2,'lusin'=>1]
        $fallback  = (int) config('alerts.stock_low_threshold', 5);
        $mapCodes  = array_keys($map);

        // Eager-load hanya units yang low:
        // - unit yang ada di map: stok <= limit per unit
        // - unit yang TIDAK ada di map: stok <= fallback
        return Barang::query()
            ->with(['units' => function ($u) use ($map, $mapCodes, $fallback) {
                $u->where(function ($q) use ($map, $mapCodes, $fallback) {
                    // 1) Unit yang di-map
                    foreach ($map as $kode => $lim) {
                        $q->orWhere(function ($x) use ($kode, $lim) {
                            $x->where('units.kode', $kode)
                              ->where('barang_unit_prices.stok', '<=', (int) $lim);
                        });
                    }
                    // 2) Unit lain (tidak ada di map) pakai fallback threshold
                    $q->orWhere(function ($x) use ($mapCodes, $fallback) {
                        $x->whereNotIn('units.kode', $mapCodes)
                          ->where('barang_unit_prices.stok', '<=', $fallback);
                    });
                });
            }])
            ->whereHas('units', function ($u) use ($map, $mapCodes, $fallback) {
                $u->where(function ($q) use ($map, $mapCodes, $fallback) {
                    foreach ($map as $kode => $lim) {
                        $q->orWhere(function ($x) use ($kode, $lim) {
                            $x->where('units.kode', $kode)
                              ->where('barang_unit_prices.stok', '<=', (int) $lim);
                        });
                    }
                    $q->orWhere(function ($x) use ($mapCodes, $fallback) {
                        $x->whereNotIn('units.kode', $mapCodes)
                          ->where('barang_unit_prices.stok', '<=', $fallback);
                    });
                });
            })
            ->get()
            // Urutkan berdasarkan stok TERKECIL di antara unit-unit low
            ->sortBy(function ($b) {
                return collect($b->units ?? [])
                    ->map(fn ($u) => (int) data_get($u, 'pivot.stok', 0))
                    ->min() ?? PHP_INT_MAX;
            })
            ->values();
    }

    public function pdf()
    {
        [$harian, $mingguan] = $this->ringkasOmzet();
        $topItems   = $this->topItems();
        $stokKritis = $this->stokKritis();

        return Pdf::loadView('dashboard_pdf', compact('harian', 'mingguan', 'topItems', 'stokKritis'))
                  ->setPaper('A4', 'portrait')
                  ->stream('dashboard.pdf');
    }

    public function excel()
    {
        [$harian, $mingguan] = $this->ringkasOmzet();
        $topItems   = $this->topItems();
        $stokKritis = $this->stokKritis();

        return Excel::download(
            new DashboardExport($harian, $mingguan, $topItems, $stokKritis),
            'dashboard.xlsx'
        );
    }
}
