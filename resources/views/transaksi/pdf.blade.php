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
  </style>
</head>
<body>
  <h3 style="margin:0 0 5px 0">Detail Transaksi</h3>
  <table style="border:none">
    <tr><td>Kode</td><td>{{ $transaksi->kode_transaksi }}</td></tr>
    <tr><td>Tanggal</td><td>{{ $transaksi->tanggal->translatedFormat('d F Y • H:i') }} WIB</td></tr>
    <tr><td>Metode</td><td>{{ ucfirst($transaksi->metode_bayar) }}</td></tr>
    <tr><td>Dibayar</td><td>Rp{{ number_format($transaksi->dibayar,0,',','.') }}</td></tr>
    <tr><td>Kembalian</td><td>Rp{{ number_format($transaksi->kembalian,0,',','.') }}</td></tr>
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
          <td>{{ $it->tipe_item=='barang' ? $it->barang->nama : $it->jasa->nama }}</td>
          <td class="text-end">
            {{ $it->jumlah }} {{ $it->tipe_item=='barang'
                ? ($it->tipe_qty=='paket' ? 'paket' : 'pcs')
                : $it->jasa->satuan }}
          </td>
          <td class="text-end">Rp{{ number_format($it->harga_satuan,0,',','.') }}</td>
          <td class="text-end">Rp{{ number_format($it->subtotal,0,',','.') }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="text-end">Total</th>
        <th class="text-end">Rp{{ number_format($transaksi->total_harga,0,',','.') }}</th>
      </tr>
    </tfoot>
  </table>
</body>
</html>
