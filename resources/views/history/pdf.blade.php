<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>{{ $transaksi->kode_transaksi }}</title>
  <style>
    body{ font-family: DejaVu Sans, sans-serif; font-size: 11px; }
    table{ width:100%; border-collapse: collapse; margin-top:10px}
    th,td{ border:1px solid #000; padding:4px 6px; }
    th{ background:#eee; }
    .text-end{ text-align:right }
    .no-border td{ border:none; padding:2px 4px; }
  </style>
</head>
<body>
  <h3 style="margin:0 0 5px 0">Detail Transaksi</h3>

  @php
    $statusId = fn($s) => match($s){ 'draft'=>'Draf','posted'=>'Diposting','void'=>'Dibatalkan', default=>ucfirst((string)$s) };
    $payId    = fn($s) => match($s){ 'paid'=>'Lunas','partial'=>'Sebagian (parsial)','unpaid'=>'Belum dibayar', default=>ucfirst((string)$s) };
    $methodId = fn($m) => match($m){ 'cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS', default=>ucfirst((string)$m) };
  @endphp

  <table class="no-border" style="width:auto">
    <tr><td>Kode</td><td>:</td><td>{{ $transaksi->kode_transaksi }}</td></tr>
    <tr><td>Tanggal</td><td>:</td><td>{{ optional($transaksi->tanggal)->translatedFormat('d F Y • H:i') ?? optional($transaksi->created_at)->format('d M Y H:i') }} WIB</td></tr>
    @if(!empty($transaksi->metode_bayar))
    <tr><td>Metode</td><td>:</td><td>{{ $methodId($transaksi->metode_bayar) }}</td></tr>
    @endif
    <tr><td>Dibayar</td><td>:</td><td>Rp{{ number_format((int)$transaksi->dibayar,0,',','.') }}</td></tr>
    <tr><td>Kembalian</td><td>:</td><td>Rp{{ number_format((int)$transaksi->kembalian,0,',','.') }}</td></tr>
    @php
      $status = $transaksi->status ?? 'posted';
      $paymentStatus = $transaksi->payment_status ?? 'unpaid';
    @endphp
    <tr><td>Status</td><td>:</td><td>{{ $statusId($status) }}</td></tr>
    <tr><td>Status Bayar</td><td>:</td><td>{{ $payId($paymentStatus) }}</td></tr>
  </table>

  <table>
    <thead>
      <tr>
        <th>#</th><th>Nama</th><th>Qty</th><th>Harga</th><th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @foreach($transaksi->items as $it)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $it->tipe_item=='barang' ? ($it->barang->nama ?? '-') : ($it->jasa->nama ?? '-') }}</td>
          <td class="text-end">
            {{ (int)$it->jumlah }} {{ $it->tipe_item=='barang'
                ? (isset($it->tipe_qty) && $it->tipe_qty=='paket' ? 'paket' : 'pcs')
                : ($it->jasa->satuan ?? '') }}
          </td>
          <td class="text-end">Rp{{ number_format((int)$it->harga_satuan,0,',','.') }}</td>
          <td class="text-end">Rp{{ number_format((int)$it->subtotal,0,',','.') }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="text-end">Total</th>
        <th class="text-end">Rp{{ number_format((int)$transaksi->total_harga,0,',','.') }}</th>
      </tr>
    </tfoot>
  </table>
</body>
</html>
