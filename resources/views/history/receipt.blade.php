@extends('layouts.print')
@section('title', 'Struk '.$transaksi->kode_transaksi)

@php
  $rp = fn($v) => 'Rp. '.number_format((int)$v,0,',','.');

  // angka dasar
  $subTotal = (int)($transaksi->total_harga ?? 0);
  // hitung diskon dari item & nota (invoice)
  $gross = 0; $netItems = 0;
  foreach(($transaksi->items ?? []) as $it){
    $gross    += (int)$it->jumlah * (int)$it->harga_satuan;
    $netItems += (int)$it->subtotal;
  }
  $itemDisc = max(0, $gross - $netItems);
  $invDisc  = (int)($transaksi->discount_amount ?? 0);

  // konfig pajak & pembulatan (opsional, bisa diset di config/store.php)
  $taxRate   = config('store.tax_rate', 0);            // contoh: 0.10
  $roundStep = (int)config('store.rounding_to', 0);    // contoh: 500 (bulat ke bawah per 500)

  $pajak     = (int)round($subTotal * $taxRate);
  $bill      = $subTotal + $pajak;

  if ($roundStep > 0) {
      $rounded     = (int) (floor($bill / $roundStep) * $roundStep);
      $pembulatan  = $rounded - $bill;  // biasanya negatif atau 0
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
@endphp

@section('content')

  {{-- Header toko --}}
  <div class="center">
    @if(!empty($store['logo']))
      <div style="margin-bottom:4px"><img src="{{ $store['logo'] }}" alt="logo" style="max-width:120px; max-height:60px"></div>
    @endif
    @if(request()->boolean('reprint'))
      <div class="muted" style="margin-bottom:4px">(Reprint)</div>
    @endif
    <h3 style="font-weight:800">{{ $store['name'] }}</h3>
    @if(!empty($store['address']))<div>{!! nl2br(e($storeAddrMultiline)) !!}</div>@endif
    @if(!empty($store['city']))   <div class="muted">{{ $store['city'] }}</div>@endif
    @if(!empty($store['phone']))  <div class="muted">{{ $store['phone'] }}</div>@endif
  </div>

  <div class="hr"></div>

  {{-- Info transaksi --}}
  <div class="row mono">
    <div>
      <div>Waktu : {{ optional($transaksi->tanggal ?? $transaksi->created_at)->translatedFormat('d M Y H:i') }}</div>
      <div>Kasir : {{ $kasir }}</div>
      <div>#{{ $transaksi->kode_transaksi }}</div>
    </div>
    <div class="right" style="text-align:right">
      {{-- kanan dibiarkan kosong agar pola mirip contoh --}}
    </div>
  </div>

  <div class="hr"></div>

  {{-- Daftar item --}}
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

  {{-- Ringkasan --}}
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

  {{-- Pembayaran --}}
  <div class="row mono">
    <div style="text-transform:uppercase">{{ $method }}{!! $reference ? ' — '.$reference : '' !!}</div>
    <div style="font-weight:700">{{ $rp($transaksi->dibayar) }}</div>
  </div>
  <div class="row mono" style="margin-top:2px">
    <div>Kembalian</div>
    <div>{{ $rp($transaksi->kembalian) }}</div>
  </div>

  <div class="hr"></div>

  {{-- Footer --}}
  <div class="footer center">
    <div>Barang Yang Sudah Dibeli</div>
    <div>Tidak Dapat Ditukar</div>
    <div>Terimakasih</div>
    <div>Hormat Kami</div>
    @if(!empty($store['ig']))
      <div class="muted">IG {{ $store['ig'] }}</div>
    @endif
  </div>

  {{-- Tombol aksi (tidak ikut tercetak) --}}
  <div class="noprint" style="margin-top:12px; display:flex; gap:8px; justify-content:center">
    <a href="{{ route('pembayaran.create') }}"
       id="btnDone"
       accesskey="s"
       style="border:1px solid #444; padding:6px 10px; border-radius:6px; text-decoration:none; font-weight:700; background:#fff;">
      Selesai (Alt+S)
    </a>

    <button type="button"
            onclick="window.print()"
            style="border:1px solid #444; padding:6px 10px; border-radius:6px; background:#fff; cursor:pointer;">
      Cetak
    </button>
  </div>
@endsection

@section('scripts')
  @if(request()->boolean('print'))
    <script>
      // Buka dialog print saat halaman selesai render
      window.addEventListener('load', () => setTimeout(() => window.print(), 200));

      // Setelah dialog print ditutup, fokuskan ke tombol "Selesai"
      window.addEventListener('afterprint', () => {
        const btn = document.getElementById('btnDone');
        if (btn) btn.focus();
      });
    </script>
  @endif
@endsection
