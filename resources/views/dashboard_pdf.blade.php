<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><title>Dashboard</title>
<style>
 body{font-family:DejaVu Sans,sans-serif;font-size:11px;}
 table{width:100%;border-collapse:collapse;margin-bottom:8px}
 th,td{border:1px solid #000;padding:4px 6px;} th{background:#eee;}
 .text-end{text-align:right}
</style>
</head><body>
<h3 style="margin:0 0 8px 0">Ringkasan Dashboard</h3>

<table style="border:none">
  <tr><td>Omzet Hari Ini</td><td>Rp{{ number_format($harian,0,',','.') }}</td></tr>
  <tr><td>Omzet 7 Hari</td><td>Rp{{ number_format($mingguan,0,',','.') }}</td></tr>
</table>

<h4>Top 10 Barang/Jasa</h4>
<table>
 <thead><tr><th>#</th><th>Nama</th><th>Qty</th><th>Omzet</th></tr></thead>
 <tbody>
  @foreach($topItems as $i=>$t)
   <tr><td>{{ $i+1 }}</td><td>{{ $t->nama }}</td>
       <td class="text-end">{{ $t->qty }}</td>
       <td class="text-end">Rp{{ number_format($t->omzet,0,',','.') }}</td></tr>
  @endforeach
 </tbody>
</table>

<h4>Stok Kritis</h4>
<table>
 <thead><tr><th>#</th><th>Nama</th><th>Pcs</th><th>Paket</th></tr></thead>
 <tbody>
  @forelse($stokKritis as $i=>$b)
    <tr><td>{{ $i+1 }}</td><td>{{ $b->nama }}</td>
        <td class="text-end">{{ $b->stok_satuan }}</td>
        <td class="text-end">{{ $b->stok_paket }}</td></tr>
  @empty
    <tr><td colspan="4" style="text-align:center">Semua stok aman</td></tr>
  @endforelse
 </tbody>
</table>
</body></html>
