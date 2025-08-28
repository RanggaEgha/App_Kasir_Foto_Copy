@extends('layouts.app')
@section('title','Detail Transaksi')

@section('content')
<div class="container-fluid px-4">
  <h1 class="mt-4">Detail Transaksi</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">

      @php
        $status = $transaksi->status ?? 'posted';
        $paymentStatus = $transaksi->payment_status ?? 'unpaid';
        $payClass = ['paid'=>'success','partial'=>'warning','unpaid'=>'secondary'][$paymentStatus] ?? 'secondary';
      @endphp
      <div class="mb-3">
        @php
          $statusId = fn($s) => match($s){ 'draft'=>'Draf','posted'=>'Diposting','void'=>'Dibatalkan', default=>ucfirst((string)$s) };
          $payId    = fn($s) => match($s){ 'paid'=>'Lunas','partial'=>'Sebagian (parsial)','unpaid'=>'Belum dibayar', default=>ucfirst((string)$s) };
          $methodId = fn($m) => match($m){ 'cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS', default=>ucfirst((string)$m) };
        @endphp
        <span class="badge bg-{{ $status === 'void' ? 'danger' : ($status==='draft'?'secondary':'success') }}">
          {{ $statusId($status) }}
        </span>
        <span class="badge bg-{{ $payClass }}">
          {{ $payId($paymentStatus) }}
        </span>
      </div>

      {{-- ===== HEADER INFO ===== --}}
      <table class="table table-borderless w-auto mb-4">
        <tbody>
          <tr><th>Kode Transaksi</th><td>{{ $transaksi->kode_transaksi }}</td></tr>
          <tr><th>Tanggal</th>
              <td>{{ optional($transaksi->tanggal)->translatedFormat('d F Y • H:i') ?? optional($transaksi->created_at)->format('d M Y H:i') }} WIB</td></tr>
          @if(!empty($transaksi->metode_bayar))
          <tr><th>Metode Bayar</th><td>{{ $methodId($transaksi->metode_bayar) }}</td></tr>
          @endif
          <tr><th>Dibayar</th>
              <td>Rp{{ number_format((int)$transaksi->dibayar,0,',','.') }}</td></tr>
          <tr><th>Kembalian</th>
              <td>Rp{{ number_format((int)$transaksi->kembalian,0,',','.') }}</td></tr>
        </tbody>
      </table>

      {{-- ===== TABLE ITEM ===== --}}
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nama Barang / Jasa</th>
              <th>Jumlah</th>
              <th>Harga Satuan</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transaksi->items as $it)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $it->tipe_item == 'barang' ? ($it->barang->nama ?? '-') : ($it->jasa->nama ?? '-') }}</td>
                <td>
                  {{ (int)$it->jumlah }}
                  {{ $it->tipe_item == 'barang'
                       ? (isset($it->tipe_qty) && $it->tipe_qty == 'paket' ? 'paket' : 'pcs')
                       : ($it->jasa->satuan ?? '') }}
                  @if($it->tipe_item == 'barang' && (isset($it->tipe_qty) && $it->tipe_qty == 'paket') && isset($it->barang->isi_per_paket))
                    (isi {{ $it->barang->isi_per_paket }})
                  @endif
                </td>
                <td>Rp{{ number_format((int)$it->harga_satuan,0,',','.') }}</td>
                <td>Rp{{ number_format((int)$it->subtotal,0,',','.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total</th>
              <th>Rp{{ number_format((int)$transaksi->total_harga,0,',','.') }}</th>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- ===== ACTION BUTTONS ===== --}}
      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('history.pdf', $transaksi->id) }}"
           class="btn btn-outline-primary" target="_blank">Cetak&nbsp;PDF</a>
        <a href="{{ route('history.index') }}" class="btn btn-secondary">← Kembali</a>
      </div>

    </div>
  </div>
</div>
@endsection
