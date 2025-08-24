@php
    $fmt = fn($n) => 'Rp'.number_format((float)$n, 0, ',', '.');

    // Top 10 aman untuk object/array
    $top = collect($topItems ?? [])->map(function($r){
        return [
            'nama'  => (string) (data_get($r,'nama') ?? data_get($r,'name') ?? ''),
            'qty'   => (int)    (data_get($r,'qty')  ?? data_get($r,'jumlah') ?? 0),
            'omzet' => (int)    (data_get($r,'omzet')?? data_get($r,'total')  ?? 0),
        ];
    })->take(10);

    // Stok kritis per barang (Pcs & Paket—dua unit paling umum)
    $krit = collect($stokKritis ?? [])->map(function($b){
        $units = collect(optional($b)->units ?? []);
        return [
            'nama'  => (string) ($b->nama ?? ('Barang #'.$b->id)),
            'pcs'   => optional($units->firstWhere('kode','pcs'))?->pivot?->stok   ?? ($b->stok_satuan ?? null),
            'paket' => optional($units->firstWhere('kode','paket'))?->pivot?->stok ?? ($b->stok_paket ?? null),
        ];
    });
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Dashboard</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
  .title { font-size: 16px; font-weight: 700; margin: 0 0 10px; }
  .card { border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 12px; }
  .hd { background: #f7f8fa; padding: 8px 10px; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
  .bd { padding: 10px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #e5e7eb; padding: 6px 8px; }
  th { background: #eef1f5; text-align: left; }
  td.num { text-align: right; }
  .muted { color: #777; }
  .center { text-align: center; }
</style>
</head>
<body>
  <div class="title">Ringkasan Dashboard</div>

  <div class="card">
    <div class="hd">Omzet</div>
    <div class="bd">
      <table>
        <tr><td style="width:40%;font-weight:600;">Omzet Hari Ini</td><td>{{ $fmt($harian ?? 0) }}</td></tr>
        <tr><td style="font-weight:600;">Omzet 7 Hari</td><td>{{ $fmt($mingguan ?? 0) }}</td></tr>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="hd">Top 10 Barang/Jasa</div>
    <div class="bd">
      <table>
        <thead>
          <tr>
            <th class="center" style="width:28px;">#</th>
            <th>Nama</th>
            <th class="num" style="width:70px;">Qty</th>
            <th class="num" style="width:120px;">Omzet</th>
          </tr>
        </thead>
        <tbody>
          @forelse($top as $i => $r)
            <tr>
              <td class="center">{{ $i+1 }}</td>
              <td>{{ $r['nama'] }}</td>
              <td class="num">{{ number_format($r['qty'],0,',','.') }}</td>
              <td class="num">{{ $fmt($r['omzet']) }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="muted">Belum ada data.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="hd">Stok Kritis</div>
    <div class="bd">
      <table>
        <thead>
          <tr>
            <th style="width:28px;" class="center">#</th>
            <th>Nama</th>
            <th class="num" style="width:70px;">Pcs</th>
            <th class="num" style="width:70px;">Paket</th>
          </tr>
        </thead>
        <tbody>
          @forelse($krit as $i => $r)
            <tr>
              <td class="center">{{ $i+1 }}</td>
              <td>{{ $r['nama'] }}</td>
              <td class="num">{{ is_null($r['pcs']) ? '' : number_format($r['pcs'],0,',','.') }}</td>
              <td class="num">{{ is_null($r['paket']) ? '' : number_format($r['paket'],0,',','.') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="muted">Semua stok aman.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
