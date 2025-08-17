<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;        // ← yang benar
use App\Exports\DashboardExport;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\{Transaksi, TransaksiItem, Barang};

class DashboardController extends Controller
{
    /* ---------- DASHBOARD ---------- */
    public function index()
    {
        [$harian, $mingguan] = $this->ringkasOmzet();
        return view('dashboard', [
            'harian'     => $harian,
            'mingguan'   => $mingguan,
            'topItems'   => $this->topItems(),
            'stokKritis' => $this->stokKritis(),
        ]);
    }

    /* ---------- RINGKAS OMZET ---------- */
    private function ringkasOmzet(): array
    {
        $today = Carbon::today();
        $week  = Carbon::today()->subDays(6);   // 7 hari ke belakang

        return [
            Transaksi::whereDate('tanggal', $today)->sum('total_harga'),
            Transaksi::whereDate('tanggal', '>=', $week)->sum('total_harga'),
        ];
    }

    /* ---------- TOP-10 ITEM ---------- */
    private function topItems()
    {
        return TransaksiItem::selectRaw("
                COALESCE(barangs.nama, jasas.nama) AS nama,
                SUM(jumlah)  AS qty,
                SUM(subtotal) AS omzet
            ")
            ->leftJoin('barangs','barangs.id','=','transaksi_items.barang_id')
            ->leftJoin('jasas',  'jasas.id',  '=','transaksi_items.jasa_id')
            ->groupBy('nama')
            ->orderByDesc('omzet')
            ->limit(10)
            ->get();
    }

    /* ---------- STOK KRITIS (pivot) ---------- */
    private function stokKritis()
    {
        $pcsLimit  = 50;   // ≤ 50 pcs
        $packLimit = 2;    // ≤ 2 pack

        return Barang::with('units')        // eager-load
           ->whereHas('units', function ($u) use ($pcsLimit, $packLimit) {
    $u->where(function ($q) use ($pcsLimit) {
            $q->where('units.kode', 'pcs')
              ->where('barang_unit_prices.stok', '<=', $pcsLimit);
        })
      ->orWhere(function ($q) use ($packLimit) {
            $q->where('units.kode', 'paket') // <— ganti 'pack' menjadi 'paket'
              ->where('barang_unit_prices.stok', '<=', $packLimit);
        });
})

            ->get()
            ->sortBy(fn ($b) => $b->stokPcs());   // method di model Barang
    }
    public function pdf()
{
    // pakai helper existing agar konsisten dgn halaman dashboard
    [$harian, $mingguan] = $this->ringkasOmzet();
    $topItems   = $this->topItems();
    $stokKritis = $this->stokKritis(); // sudah include with('units')

    return Pdf::loadView('dashboard_pdf', compact('harian','mingguan','topItems','stokKritis'))
              ->setPaper('A4','portrait')
              ->stream('dashboard.pdf');
}
 public function excel()
{
    // Ambil data persis seperti di dashboard
    [$harian, $mingguan] = $this->ringkasOmzet();
    $topItems   = $this->topItems();
    $stokKritis = $this->stokKritis(); // sudah with('units')

    // Cocokkan signature DashboardExport(harian, mingguan, topItems, stokKritis)
    return Excel::download(
        new DashboardExport($harian, $mingguan, $topItems, $stokKritis),
        'dashboard.xlsx'
    );
}


}
