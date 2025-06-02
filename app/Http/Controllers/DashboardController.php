<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DashboardExport;
use App\Models\{Transaksi, TransaksiItem, Barang};

class DashboardController extends Controller
{
    /* ───────── Dashboard view ───────── */
    public function index()
    {
        [$harian, $mingguan] = $this->directOmzet();
        $topItems   = $this->topItems();
        $stokKritis = $this->stokKritis();

        return view('dashboard', [
            'harian'     => $harian,
            'mingguan'   => $mingguan,
            'topItems'   => $topItems,
            'stokKritis' => $stokKritis,
        ]);
    }

    /* ───────── Export PDF ───────── */
    public function pdf()
    {
        [$harian, $mingguan] = $this->directOmzet();
        $topItems   = $this->topItems();
        $stokKritis = $this->stokKritis();

        $pdf = Pdf::loadView('dashboard_pdf', [
                    'harian'     => $harian,
                    'mingguan'   => $mingguan,
                    'topItems'   => $topItems,
                    'stokKritis' => $stokKritis,
                ])
                ->setPaper('A4', 'portrait');

        return $pdf->download('dashboard-'.now()->format('Ymd-His').'.pdf');
    }

    /* ───────── Export Excel ───────── */
    public function excel()
    {
        [$harian, $mingguan] = $this->directOmzet();
        $topItems   = $this->topItems();
        $stokKritis = $this->stokKritis();

        return Excel::download(
            new DashboardExport($harian, $mingguan, $topItems, $stokKritis),
            'dashboard-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    /* ===== hitung omzet ===== */
    private function directOmzet(): array
    {
        $today = Carbon::today();
        $week  = Carbon::today()->subDays(6);

        return [
            Transaksi::whereDate('tanggal', $today)->sum('total_harga'),
            Transaksi::whereDate('tanggal', '>=', $week)->sum('total_harga'),
        ];
    }

    /* ===== top-10 item ===== */
    private function topItems()
    {
        return TransaksiItem::selectRaw("
                  COALESCE(barangs.nama, jasas.nama) AS nama,
                  SUM(jumlah)  AS qty,
                  SUM(subtotal) AS omzet")
                ->leftJoin('barangs','barangs.id','=','transaksi_items.barang_id')
                ->leftJoin('jasas','jasas.id','=','transaksi_items.jasa_id')
                ->groupBy('nama')
                ->orderByDesc('omzet')
                ->limit(10)
                ->get();
    }

    /* ===== stok kritis ===== */
    private function stokKritis()
    {
        return Barang::where('stok_satuan','<=',50)
                     ->orWhere('stok_paket','<=',2)
                     ->orderBy('stok_satuan')
                     ->get();
    }
}
