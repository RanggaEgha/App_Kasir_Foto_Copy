@extends('layouts.print')
@section('title', 'Struk '.$transaksi->kode_transaksi)

@php
  $rp = fn($v) => 'Rp '.number_format((int)$v,0,',','.');

  // angka dasar
  $subTotal = (int)($transaksi->total_harga ?? 0);

  // konfig pajak & pembulatan (opsional, bisa diset di config/store.php)
  $taxRate   = config('store.tax_rate', 0);
  $roundStep = (int)config('store.rounding_to', 0);

  $pajak     = (int)round($subTotal * $taxRate);
  $bill      = $subTotal + $pajak;

  if ($roundStep > 0) {
      $rounded     = (int) (floor($bill / $roundStep) * $roundStep);
      $pembulatan  = $rounded - $bill;
      $grandTotal  = $rounded;
  } else {
      $pembulatan  = 0;
      $grandTotal  = $bill;
  }

  // metode bayar: ambil dari payment terakhir, fallback ke transaksi.metode_bayar
  $pay = $transaksi->payments->last();
  $method = strtoupper($pay->method ?? $transaksi->metode_bayar ?? '-');
  $reference = $pay->reference ?? null;

  // info kasir/shift
  $kasir  = $transaksi->shift?->user?->name ?? '-';
  $shiftN = $transaksi->shift?->id ?? '-';

  // identitas toko
  $store = [
    'name'    => config('store.name',    'Nama Toko'),
    'address' => config('store.address', 'Alamat Toko'),
    'city'    => config('store.city',    ''),
    'ig'      => config('store.ig',      ''),
    'phone'   => config('store.phone',   env('STORE_PHONE')),
    'logo'    => config('store.logo_url', env('STORE_LOGO_URL')),
  ];
  $storeAddrMultiline = str_replace('\\n', "\n", (string)($store['address'] ?? ''));

  // hitung diskon item + nota
  $gross = 0; $netItems = 0;
  foreach(($transaksi->items ?? []) as $it){ $gross += (int)$it->jumlah * (int)$it->harga_satuan; $netItems += (int)$it->subtotal; }
  $itemDisc = max(0, $gross - $netItems);
  $invDisc  = (int)($transaksi->discount_amount ?? 0);
@endphp

@section('content')

  <div class="center">
    @if(!empty($store['logo']))
      <div style="margin-bottom:4px"><img src="{{ $store['logo'] }}" alt="logo" style="max-width:120px; max-height:60px"></div>
    @endif
    <h3 style="font-weight:800">{{ $store['name'] }}</h3>
    @if(!empty($store['address']))<div>{!! nl2br(e($storeAddrMultiline)) !!}</div>@endif
    @if(!empty($store['city']))   <div class="muted">{{ $store['city'] }}</div>@endif
    @if(!empty($store['phone']))  <div class="muted">{{ $store['phone'] }}</div>@endif
  </div>

  <div class="hr"></div>

  <div class="row mono">
    <div>
      <div>Waktu : {{ optional($transaksi->tanggal ?? $transaksi->created_at)->translatedFormat('d M Y H:i') }}</div>
      <div>Kasir : {{ $kasir }}</div>
      <div>#{{ $transaksi->kode_transaksi }}</div>
    </div>
    <div class="right" style="text-align:right"></div>
  </div>

  <div class="hr"></div>

  <div class="items">
    @foreach($transaksi->items as $it)
      @php
        $nama  = $it->tipe_item === 'barang' ? ($it->barang->nama ?? '-') : ($it->jasa->nama ?? '-');
        $qty   = (int)$it->jumlah;
        $unit  = $it->tipe_item === 'barang' ? ($it->unit->kode ?? 'pcs') : ($it->jasa->satuan ?? '');
      @endphp
      <div class="line">
        <div class="name">{{ $nama }}</div>
        @if($unit)
          <div class="mono muted" style="margin-top:2px">{{ strtoupper($unit) }}</div>
        @endif
        <div class="row mono" style="margin-top:2px">
          <div>{{ number_format((int)$it->harga_satuan,0,',','.') }} x{{ $qty }}</div>
          <div>{{ number_format((int)$it->subtotal,0,',','.') }}</div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="hr"></div>

  <div class="totals">
    @if(($itemDisc + $invDisc) > 0)
      <div class="row"><div class="label">Bruto</div><div class="amt">{{ $rp($gross) }}</div></div>
      @if($itemDisc>0)
        <div class="row"><div class="label">Diskon Item</div><div class="amt">-{{ $rp($itemDisc) }}</div></div>
      @endif
      @if($invDisc>0)
        <div class="row"><div class="label">Diskon Nota</div><div class="amt">-{{ $rp($invDisc) }}</div></div>
      @endif
    @endif
    @if(($taxRate ?? 0) > 0)
      <div class="row"><div class="label">Sub Total</div><div class="amt">{{ $rp($subTotal) }}</div></div>
    @endif
    @if($taxRate > 0)
      <div class="row"><div class="label">Pajak {{ (int)round($taxRate*100) }}%</div><div class="amt">{{ $rp($pajak) }}</div></div>
    @endif
    @if($pembulatan !== 0)
      <div class="row"><div class="label">Pembulatan</div><div class="amt">{{ ($pembulatan>0?'+':'').$rp($pembulatan) }}</div></div>
    @endif
    <div class="hr-solid"></div>
    <div class="row grand"><div>Total</div><div>{{ $rp($grandTotal) }}</div></div>
  </div>

  <div class="hr"></div>

  <div class="row mono">
    <div style="text-transform:uppercase">{{ $method }}{!! $reference ? ' — '.$reference : '' !!}</div>
    <div style="font-weight:700">{{ $rp($transaksi->dibayar) }}</div>
  </div>
  <div class="row mono" style="margin-top:2px">
    <div>Kembalian</div>
    <div>{{ $rp($transaksi->kembalian) }}</div>
  </div>

  <div class="hr"></div>

  <div class="footer center">
    <div>Barang Yang Sudah Dibeli</div>
    <div>Tidak Dapat Ditukar</div>
    <div>Terimakasih</div>
    <div>Hormat Kami</div>
    @if(!empty($store['ig']))
      <div class="muted">IG {{ $store['ig'] }}</div>
    @endif
  </div>

@endsection
