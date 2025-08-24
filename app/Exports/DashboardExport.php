<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

/**
 * Export Dashboard: 3 sheet (Ringkasan, Top 10, Stok Kritis).
 * Versi ringan – tanpa styling spesial supaya aman di semua environment.
 */
class DashboardExport implements WithMultipleSheets
{
    public function __construct(
        protected int $harian,
        protected int $mingguan,
        protected Collection $topItems,
        protected Collection $stokKritis
    ) {}

    public function sheets(): array
    {
        return [
            new RingkasanSheet ($this->harian, $this->mingguan),
            new TopItemSheet   ($this->topItems),
            new StokKritisSheet($this->stokKritis),
        ];
    }
}

/* ================== Sheet: Ringkasan ================== */
class RingkasanSheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(private int $harian, private int $mingguan) {}

    public function array(): array
    {
        return [
            ['Ringkasan'],
            ['Harian', $this->harian],
            ['7 Hari', $this->mingguan],
        ];
    }

    public function title(): string { return 'Ringkasan'; }
}

/* ================== Sheet: Top 10 ================== */
class TopItemSheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $rows) {}

    public function array(): array
    {
        $arr   = [['#', 'Nama', 'Qty', 'Omzet']];
        $index = 1;

        foreach ($this->rows as $r) {
            $arr[] = [
                $index++,
                (string) (data_get($r, 'nama') ?? data_get($r, 'name') ?? ''),
                (int)    (data_get($r, 'qty')  ?? data_get($r, 'jumlah') ?? 0),
                (int)    (data_get($r, 'omzet')?? data_get($r, 'total')  ?? 0),
            ];
        }
        return $arr;
    }

    public function title(): string { return 'Top 10'; }
}

/* ================== Sheet: Stok Kritis ================== */
class StokKritisSheet implements FromArray, WithTitle, ShouldAutoSize
{
    public function __construct(private Collection $rows) {}

    public function array(): array
    {
        // Tampilkan per barang, kolom unit umum: Pcs, Paket, Lusin, Box (kalau tidak ada → kosong)
        $arr = [['Nama', 'Pcs', 'Paket', 'Lusin', 'Box']];

        foreach ($this->rows as $b) {
            $nama  = (string) ($b->nama ?? ('Barang #'.$b->id));
            $units = collect(optional($b)->units ?? []);

            $pcs   = optional($units->firstWhere('kode', 'pcs'))?->pivot?->stok   ?? ($b->stok_satuan ?? null);
            $paket = optional($units->firstWhere('kode', 'paket'))?->pivot?->stok ?? ($b->stok_paket ?? null);
            $lusin = optional($units->firstWhere('kode', 'lusin'))?->pivot?->stok ?? null;
            $box   = optional($units->firstWhere('kode', 'box'))?->pivot?->stok   ?? null;

            $arr[] = [
                $nama,
                is_null($pcs)   ? '' : (int) $pcs,
                is_null($paket) ? '' : (int) $paket,
                is_null($lusin) ? '' : (int) $lusin,
                is_null($box)   ? '' : (int) $box,
            ];
        }
        return $arr;
    }

    public function title(): string { return 'Stok Kritis'; }
}
