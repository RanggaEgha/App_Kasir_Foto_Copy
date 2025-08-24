@extends('layouts.print')
@section('title', 'Struk '.$transaksi->kode_transaksi)

@php
  $rp = fn($v) => 'Rp'.number_format((int)$v,0,',','.');

  // angka dasar
  $subTotal = (int)($transaksi->total_harga ?? 0);

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
    'ig'      => config('store.ig',      '@toko.ig'),
    'city'    => config('store.city',    'Kota'),
  ];
@endphp

@section('content')

  {{-- Header toko --}}
  <div class="center">
    <h3 style="font-weight:800">{{ $store['name'] }}</h3>
    <div>{{ $store['address'] }}</div>
    <div class="muted">IG {{ $store['ig'] }}</div>
    <div class="muted">{{ $store['city'] }}</div>
  </div>

  <div class="hr"></div>

  {{-- Info transaksi --}}
  <div class="row mono">
    <div>
      <div>{{ optional($transaksi->tanggal ?? $transaksi->created_at)->format('d M Y') }}</div>
      <div>Kasir: {{ $kasir }}</div>
      <div>Shift: {{ $shiftN }}</div>
    </div>
    <div class="right" style="text-align:right">
      <div class="muted">No.</div>
      <div style="font-weight:700">{{ $transaksi->kode_transaksi }}</div>
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
        <div class="row">
          <div class="name">{{ $nama }}</div>
          <div class="amt">{{ $rp($it->subtotal) }}</div>
        </div>
        <div class="meta mono">
          <div>{{ $qty }} x @ {{ $rp($it->harga_satuan) }} {{ $unit ? "($unit)" : "" }}</div>
          <div></div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="hr"></div>

  {{-- Ringkasan --}}
  <div class="totals">
    <div class="row"><div class="label">Sub Total</div><div class="amt">{{ $rp($subTotal) }}</div></div>
    @if($taxRate > 0)
      <div class="row"><div class="label">Pajak {{ (int)round($taxRate*100) }}%</div><div class="amt">{{ $rp($pajak) }}</div></div>
    @endif
    @if($pembulatan !== 0)
      <div class="row"><div class="label">Pembulatan</div><div class="amt">{{ ($pembulatan>0?'+':'').$rp($pembulatan) }}</div></div>
    @endif
    <div class="hr-solid"></div>
    <div class="row grand"><div>Grand Total</div><div>{{ $rp($grandTotal) }}</div></div>
  </div>

  <div class="hr"></div>

  {{-- Pembayaran --}}
  <div class="row mono">
    <div style="text-transform:uppercase">{{ $method }}{!! $reference ? ' — '.$reference : '' !!}</div>
    <div style="font-weight:700">{{ $rp($grandTotal) }}</div>
  </div>

  <div class="hr"></div>

  {{-- Footer --}}
  <div class="footer center">
    <div>Terimakasih</div>
    <div class="muted">Follow IG kami: {{ $store['ig'] }}</div>
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
