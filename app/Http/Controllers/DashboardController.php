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
        // Kompat lama untuk KPI di header dashboard
        [$harian, $mingguan] = $this->ringkasOmzet();
        $notifications = Notification::orderByDesc('created_at')->limit(10)->get();

        // KPI lanjutan + perbandingan (hari/minggu/bulan/tahun)
        $adv = $this->ringkasOmzetAdvanced();

        // Top 10 (hari/bulan/tahun + barang/jasa)
        $topTodayAll     = $this->topItemsByPeriod('day');
        $topMonthAll     = $this->topItemsByPeriod('month');
        $topYearAll      = $this->topItemsByPeriod('year');
        $topTodayBarang  = $this->topItemsByPeriod('day', 'barang');
        $topTodayJasa    = $this->topItemsByPeriod('day', 'jasa');
        $topMonthBarang  = $this->topItemsByPeriod('month', 'barang');
        $topMonthJasa    = $this->topItemsByPeriod('month', 'jasa');
        $topYearBarang   = $this->topItemsByPeriod('year', 'barang');
        $topYearJasa     = $this->topItemsByPeriod('year', 'jasa');

        // Streak Top #1
        $streaks = [
            'day'           => $this->streakTop1('day'),
            'week'          => $this->streakTop1('week'),
            'month'         => $this->streakTop1('month'),
            'year'          => $this->streakTop1('year'),
            'day_barang'    => $this->streakTop1('day', 'barang'),
            'day_jasa'      => $this->streakTop1('day', 'jasa'),
            'month_barang'  => $this->streakTop1('month', 'barang'),
            'month_jasa'    => $this->streakTop1('month', 'jasa'),
        ];

        // Seri rinci (otomatis ikut waktu berjalan)
        $seriesWeekday = $this->seriesWeekdayOmzet();                  // minggu ini (Senin–Minggu)
        $seriesMonth   = $this->seriesMonthOmzet();                    // tahun ini (Jan–Des)
        $yearDirection = config('dashboard.series.year.direction', 'future');
        $yearSpan      = (int) config('dashboard.series.year.span', 5);
        $seriesYear    = $this->seriesYearOmzet($yearSpan, $yearDirection); // rentang tahun otomatis

        return view('dashboard', [
            'harian'         => $harian,
            'mingguan'       => $mingguan,
            'topItems'       => $this->topItems(),
            'stokKritis'     => $this->stokKritis(),
            'notifications'  => $notifications,

            // advanced
            'adv'            => $adv,
            'topTodayAll'    => $topTodayAll,
            'topMonthAll'    => $topMonthAll,
            'topYearAll'     => $topYearAll,
            'topTodayBarang' => $topTodayBarang,
            'topTodayJasa'   => $topTodayJasa,
            'topMonthBarang' => $topMonthBarang,
            'topMonthJasa'   => $topMonthJasa,
            'topYearBarang'  => $topYearBarang,
            'topYearJasa'    => $topYearJasa,
            'streaks'        => $streaks,

            // series untuk chart
            'seriesWeekday'  => $seriesWeekday,
            'seriesMonth'    => $seriesMonth,
            'seriesYear'     => $seriesYear,
        ]);
    }

    // ====== Kompat + util lama ======
    private function ringkasOmzet(): array
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd   = Carbon::today()->endOfDay();
        $weekStart  = Carbon::now()->startOfWeek();
        $weekEnd    = Carbon::now()->endOfWeek();

        $qDay = Transaksi::where('status','posted')->whereBetween('tanggal', [$todayStart, $todayEnd]);
        $qWek = Transaksi::where('status','posted')->whereBetween('tanggal', [$weekStart, $weekEnd]);

        $harian = ['total' => (int) $qDay->sum('total_harga'), 'transaksi' => (int) $qDay->count()];
        $mingguan = ['total' => (int) $qWek->sum('total_harga'), 'transaksi' => (int) $qWek->count()];
        return [$harian, $mingguan];
    }

    private function topItems($limit = 10)
    {
        $start = Carbon::today()->startOfDay();
        $end   = Carbon::today()->endOfDay();

        return TransaksiItem::selectRaw("
                COALESCE(barangs.nama, jasas.nama) AS nama,
                SUM(transaksi_items.jumlah)  AS qty,
                SUM(transaksi_items.subtotal) AS omzet
            ")
            ->join('transaksis', 'transaksis.id', '=', 'transaksi_items.transaksi_id')
            ->leftJoin('barangs', 'barangs.id', '=', 'transaksi_items.barang_id')
            ->leftJoin('jasas',   'jasas.id',   '=', 'transaksi_items.jasa_id')
            ->where('transaksis.status', 'posted')
            ->whereBetween('transaksis.tanggal', [$start, $end])
            ->groupBy('nama')
            ->orderByDesc('omzet')
            ->limit($limit)
            ->get();
    }

    private function stokKritis()
    {
        $map       = (array) config('alerts.stock_low_thresholds', []);
        $fallback  = (int) config('alerts.stock_low_threshold', 5);
        $mapCodes  = array_keys($map);

        return Barang::query()
            ->with(['units' => function ($u) use ($map, $mapCodes, $fallback) {
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
            }])
            ->whereHas('units', function($q) use ($map, $mapCodes, $fallback){
                $q->where(function ($qq) use ($map, $mapCodes, $fallback) {
                    foreach ($map as $kode => $lim) {
                        $qq->orWhere(function ($x) use ($kode, $lim) {
                            $x->where('units.kode', $kode)
                              ->where('barang_unit_prices.stok', '<=', (int) $lim);
                        });
                    }
                    $qq->orWhere(function ($x) use ($mapCodes, $fallback) {
                        $x->whereNotIn('units.kode', $mapCodes)
                          ->where('barang_unit_prices.stok', '<=', $fallback);
                    });
                });
            })
            ->get();
    }

    // ====== KPI lanjutan + tops ======
    private function ringkasOmzetAdvanced(): array
    {
        $periods = [
            'day'   => [Carbon::today()->startOfDay(),  Carbon::today()->endOfDay(),  Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'week'  => [Carbon::now()->startOfWeek(),   Carbon::now()->endOfWeek(),   Carbon::now()->subWeek()->startOfWeek(),  Carbon::now()->subWeek()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(),  Carbon::now()->endOfMonth(),  Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'year'  => [Carbon::now()->startOfYear(),   Carbon::now()->endOfYear(),   Carbon::now()->subYear()->startOfYear(),  Carbon::now()->subYear()->endOfYear()],
        ];

        $out = [];
        foreach ($periods as $key => [$s, $e, $ps, $pe]) {
            $q = Transaksi::where('status','posted')->whereBetween('tanggal', [$s, $e]);
            $total = (int) $q->sum('total_harga');
            $count = (int) $q->count();

            $qp   = Transaksi::where('status','posted')->whereBetween('tanggal', [$ps, $pe]);
            $prev = (int) $qp->sum('total_harga');

            $delta_pct = $prev > 0 ? round(($total - $prev) / $prev * 100, 2) : ($total > 0 ? 100.00 : 0.00);

            $out[$key] = [
                'total'      => $total,
                'count'      => $count,
                'prev'       => $prev,
                'delta_pct'  => $delta_pct,
                'start'      => $s,
                'end'        => $e,
                'prev_start' => $ps,
                'prev_end'   => $pe,
            ];
        }
        return $out;
    }

    private function topItemsByPeriod(string $period, string $type = 'all', int $limit = 10)
    {
        [$start, $end] = match ($period) {
            'day'   => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'week'  => [Carbon::now()->startOfWeek(),  Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year'  => [Carbon::now()->startOfYear(),  Carbon::now()->endOfYear()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };

        $q = TransaksiItem::selectRaw("
                COALESCE(barangs.nama, jasas.nama) AS nama,
                SUM(transaksi_items.jumlah)  AS qty,
                SUM(transaksi_items.subtotal) AS omzet
            ")
            ->join('transaksis', 'transaksis.id', '=', 'transaksi_items.transaksi_id')
            ->leftJoin('barangs', 'barangs.id', '=', 'transaksi_items.barang_id')
            ->leftJoin('jasas',   'jasas.id',   '=', 'transaksi_items.jasa_id')
            ->where('transaksis.status', 'posted')
            ->whereBetween('transaksis.tanggal', [$start, $end]);

        if ($type === 'barang') $q->whereNotNull('transaksi_items.barang_id');
        if ($type === 'jasa')   $q->whereNotNull('transaksi_items.jasa_id');

        return $q->groupBy('nama')->orderByDesc('omzet')->limit($limit)->get();
    }

    private function streakTop1(string $granularity = 'month', string $type = 'all'): array
    {
        $maxBuckets = match ($granularity) {
            'day'   => 30, 'week' => 16, 'year' => 5, default => 12,
        };

        $buckets = [];
        $cursorStart = match ($granularity) {
            'day'   => Carbon::today()->startOfDay(),
            'week'  => Carbon::now()->startOfWeek(),
            'year'  => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };
        for ($i = 0; $i < $maxBuckets; $i++) {
            $s = match ($granularity) {
                'day'   => $cursorStart->copy()->subDays($i)->startOfDay(),
                'week'  => $cursorStart->copy()->subWeeks($i)->startOfWeek(),
                'year'  => $cursorStart->copy()->subYears($i)->startOfYear(),
                default => $cursorStart->copy()->subMonths($i)->startOfMonth(),
            };
            $e = match ($granularity) {
                'day'   => $s->copy()->endOfDay(),
                'week'  => $s->copy()->endOfWeek(),
                'year'  => $s->copy()->endOfYear(),
                default => $s->copy()->endOfMonth(),
            };
            $buckets[] = [$s, $e];
        }

        $top1s = [];
        foreach ($buckets as [$s, $e]) {
            $q = TransaksiItem::selectRaw("
                    COALESCE(barangs.nama, jasas.nama) AS nama,
                    SUM(transaksi_items.subtotal) AS omzet
                ")
                ->join('transaksis', 'transaksis.id', '=', 'transaksi_items.transaksi_id')
                ->leftJoin('barangs', 'barangs.id', '=', 'transaksi_items.barang_id')
                ->leftJoin('jasas',   'jasas.id',   '=', 'transaksi_items.jasa_id')
                ->where('transaksis.status', 'posted')
                ->whereBetween('transaksis.tanggal', [$s, $e]);
            if ($type === 'barang') $q->whereNotNull('transaksi_items.barang_id');
            if ($type === 'jasa')   $q->whereNotNull('transaksi_items.jasa_id');

            $row = $q->groupBy('nama')->orderByDesc('omzet')->limit(1)->first();
            $top1s[] = [
                'period_start' => $s->copy(),
                'period_end'   => $e->copy(),
                'nama'         => data_get($row, 'nama'),
                'omzet'        => (int) data_get($row, 'omzet', 0),
            ];
        }

        $best    = ['nama'=>null,'streak'=>0,'since'=>null,'until'=>null,'unit'=>$granularity];
        $current = ['nama'=>null,'streak'=>0,'since'=>null,'until'=>null];

        foreach ($top1s as $row) {
            $nama = $row['nama'] ?: null;
            if (!$nama) {
                if ($current['streak'] > $best['streak']) {
                    $best = ['nama'=>$current['nama'],'streak'=>$current['streak'],'since'=>$current['since'],'until'=>$current['until'],'unit'=>$granularity];
                }
                $current = ['nama'=>null,'streak'=>0,'since'=>null,'until'=>null];
                continue;
            }
            if ($current['nama'] === null || $current['nama'] === $nama) {
                $current['nama']   = $nama;
                $current['streak'] = ($current['streak'] ?? 0) + 1;
                $current['until']  = $current['until']  ?? $row['period_end'];
                $current['since']  = $row['period_start'];
            } else {
                if ($current['streak'] > $best['streak']) {
                    $best = ['nama'=>$current['nama'],'streak'=>$current['streak'],'since'=>$current['since'],'until'=>$current['until'],'unit'=>$granularity];
                }
                $current = ['nama'=>$nama, 'streak'=>1, 'since'=>$row['period_start'], 'until'=>$row['period_end']];
            }
        }
        if ($current['streak'] > $best['streak']) {
            $best = ['nama'=>$current['nama'],'streak'=>$current['streak'],'since'=>$current['since'],'until'=>$current['until'],'unit'=>$granularity];
        }
        return $best;
    }

    // ====== Seri harian, bulanan, tahunan ======
    private function seriesWeekdayOmzet(): array
    {
        $start = Carbon::now()->startOfWeek(); // Senin
        $end   = Carbon::now()->endOfWeek();

        // MySQL: 1=Min ... 7=Sab
        $rows = Transaksi::where('status','posted')
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DAYOFWEEK(tanggal) as dow, SUM(total_harga) as total')
            ->groupBy('dow')
            ->pluck('total','dow')
            ->toArray();

        $map = [2=>'Senin',3=>'Selasa',4=>'Rabu',5=>'Kamis',6=>'Jumat',7=>'Sabtu',1=>'Minggu'];
        $labels = array_values($map);
        $values = [];
        foreach (array_keys($map) as $k) $values[] = (int) ($rows[$k] ?? 0);

        return ['labels'=>$labels,'values'=>$values];
    }

    private function seriesMonthOmzet(?int $year = null): array
    {
        $year = $year ?: Carbon::now()->year;
        $rows = Transaksi::where('status','posted')
            ->whereYear('tanggal', $year)
            ->selectRaw('MONTH(tanggal) as m, SUM(total_harga) as total')
            ->groupBy('m')
            ->pluck('total','m')
            ->toArray();

        $labels = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $values = [];
        for ($m=1; $m<=12; $m++) $values[] = (int) ($rows[$m] ?? 0);

        return ['labels'=>$labels, 'values'=>$values, 'year'=>$year];
    }

    /**
     * Seri tahunan otomatis.
     * $years: jumlah tahun; $direction: 'future' (tahun ini -> depan) | 'past' (ke belakang)
     */
    private function seriesYearOmzet(int $years = 5, string $direction = 'future'): array
    {
        $nowY = Carbon::now()->year;

        if ($direction === 'future') {
            $startY = $nowY;
            $endY   = $nowY + $years - 1;
        } else {
            $endY   = $nowY;
            $startY = $nowY - $years + 1;
        }

        $start = Carbon::create($startY, 1, 1)->startOfDay();
        $end   = Carbon::create($endY, 12, 31)->endOfDay();

        $rows = Transaksi::where('status','posted')
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('YEAR(tanggal) as y, SUM(total_harga) as total')
            ->groupBy('y')
            ->pluck('total','y')
            ->toArray();

        $labels = [];
        $values = [];
        for ($y = $startY; $y <= $endY; $y++) {
            $labels[] = (string) $y;
            $values[] = (int) ($rows[$y] ?? 0);
        }
        return ['labels'=>$labels,'values'=>$values,'start'=>$startY,'end'=>$endY, 'direction'=>$direction];
    }

    // ====== Export (kompat) ======
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
