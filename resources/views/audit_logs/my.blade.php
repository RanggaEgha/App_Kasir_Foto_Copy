@extends('layouts.app')
@section('title','Aktivitas Saya')

@section('content')
@php
  // formatter angka & rupiah
  $nf     = fn($v) => is_numeric($v) ? number_format($v, 0, ',', '.') : (is_bool($v) ? ($v?'true':'false') : ($v===null?'':(string)$v));
  $rupiah = fn($v) => is_numeric($v) ? 'Rp'.$nf($v) : '';

  // parse angka longgar (menerima "1.234,56" / "1234.56")
  $toFloat = function($v){
    if ($v===null || $v==='') return null;
    if (is_numeric($v)) return (float)$v;
    $s = str_replace([' ', "\u{A0}"], '', (string)$v);
    if (preg_match('~^\d{1,3}(\.\d{3})+(,\d+)?$~', $s)) $s = str_replace('.','',$s);
    $s = str_replace(',','.',$s);
    return is_numeric($s) ? (float)$s : null;
  };

  // Status bayar → Indonesia
  $payStatus = fn($s) => match($s) {
    'paid'    => 'Lunas',
    'partial' => 'Sebagian (parsial)',
    'unpaid'  => 'Belum dibayar',
    default   => ucfirst((string)$s),
  };

  // koleksi log pada halaman ini
  $rows = $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->getCollection() : collect($logs);

  // kelompok
  $payments = $rows->filter(fn($l) => in_array($l->event, ['payment.added','payment.refund']));
  $stock    = $rows->filter(fn($l) => in_array($l->event, ['stock.decremented','stock.incremented']));
  $barang   = $rows->filter(fn($l) => class_basename($l->subject_type) === 'Barang' && in_array($l->event, ['created','updated','deleted','restored']));
  $trx      = $rows->filter(fn($l) => str_starts_with($l->event, 'transaksi.'));
  $shift    = $rows->filter(fn($l) => str_starts_with($l->event, 'shift.'));

  // label event → Indonesia
  $eventId = fn(string $ev) => match ($ev) {
    'transaksi.created' => 'Membuat draft transaksi',
    'transaksi.posted'  => 'Mem-finalkan transaksi',
    'transaksi.voided'  => 'Membatalkan transaksi',
    'payment.added'     => 'Menambah pembayaran',
    'payment.refund'    => 'Refund (pengembalian dana)',
    'stock.decremented' => 'Stok berkurang (terjual)',
    'stock.incremented' => 'Stok bertambah (void/retur)',
    'shift.opened'      => 'Buka shift',
    'shift.closed'      => 'Tutup shift',
    'created'           => 'Membuat data',
    'updated'           => 'Mengubah data',
    'deleted'           => 'Menghapus data',
    'restored'          => 'Memulihkan data',
    default             => $ev,
  };

  // metode bayar → Indonesia
  $methodId = fn($m) => match($m) {
    'cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS', default=>ucfirst((string)$m)
  };

  // ambil kode TRX dari deskripsi jika ada
  $extractKode = function ($log) {
    $desc = (string)($log->description ?? '');
    if (preg_match('/TRX[0-9A-Z]+/i', $desc, $m)) return strtoupper($m[0]);
    return null;
  };

  // Ambil daftar item dari properties/new_values (nama, qty, unit, harga, subtotal)
  $extractItems = function($log) use ($toFloat){
    $items = [];
    $props = is_array($log->properties) ? $log->properties : [];
    $new   = is_array($log->new_values) ? $log->new_values : [];
    $lists = [];
    if (isset($props['items']) && is_array($props['items'])) $lists[] = $props['items'];
    if (isset($new['items'])   && is_array($new['items']))   $lists[] = $new['items'];

    foreach ($lists as $arr) {
      foreach ($arr as $it) {
        if (!is_array($it)) continue;
        $name = $it['nama'] ?? $it['product_name'] ?? $it['item_name'] ?? $it['deskripsi'] ?? 'Item';
        $qty  = $toFloat($it['qty'] ?? $it['jumlah'] ?? $it['quantity'] ?? 1) ?? 1;
        $unit = $it['unit'] ?? $it['satuan'] ?? 'pcs';
        $price= $toFloat($it['harga'] ?? $it['harga_satuan'] ?? $it['unit_price'] ?? null);
        $sub  = $toFloat($it['subtotal'] ?? $it['line_total'] ?? null);
        if ($price===null && $sub!==null && $qty>0) $price = $sub / $qty;
        $items[] = ['name'=>$name,'qty'=>$qty,'unit'=>$unit,'price'=>$price,'subtotal'=>$sub ?? ($price!==null?$price*$qty:null)];
      }
      if ($items) break;
    }
    return $items;
  };

  // Tebak total
  $guessTotal = function($log, array $items) use ($toFloat){
    $props = is_array($log->properties) ? $log->properties : [];
    $old   = is_array($log->old_values) ? $log->old_values : [];
    $new   = is_array($log->new_values) ? $log->new_values : [];
    $cands = [
      $props['total_harga'] ?? null,
      $new['total_harga']   ?? null,
      $old['total_harga']   ?? null,
      $props['grand_total'] ?? null,
      $new['grand_total']   ?? null,
      $old['grand_total']   ?? null,
      $props['total']       ?? null,
      $new['total']         ?? null,
      $old['total']         ?? null,
    ];
    foreach ($cands as $c) {
      $f = $toFloat($c);
      if ($f !== null) return $f;
    }
    if ($items) {
      $sum = 0; foreach($items as $ln) $sum += $toFloat($ln['subtotal'] ?? null) ?? 0;
      return $sum > 0 ? $sum : null;
    }
    return null;
  };
@endphp

{{-- Fonts & Icons --}}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root{
    --bg:#f7f8fb; --card:#ffffff; --ink:#0f172a; --muted:#64748b;
    --brand:#1d4ed8; --brand-ink:#0b3aa7;
    --ok:#16a34a; --warn:#f59e0b; --danger:#dc2626;
    --chip:#e8eefc; --chip-ink:#1d4ed8; --chip-border:#bed1fa;
    --border:#e7e9ef; --hover:#f8fafc; --radius:16px;
    --ring:0 0 0 3px rgba(29,78,216,.15);
    --thead-offset: 8px;            /* jarak header sticky */
    --cell-py:.55rem; --cell-px:.75rem; /* padding seragam */
    --stripe:#f9fbff;               /* zebra row warna halus */
  }
  @media (prefers-color-scheme: dark){
    :root{
      --bg:#0b1220; --card:#0f172a; --ink:#e5e7eb; --muted:#94a3b8;
      --brand:#60a5fa; --brand-ink:#93c5fd;
      --border:#1f2937; --hover:#0b1529; --stripe:#0b1529;
      --chip:#0b2248; --chip-ink:#93c5fd; --chip-border:#102b5c;
    }
  }
  body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif; background:var(--bg); color:var(--ink)}

  /* ===== Card ===== */
  .neo-card{
    background:var(--card);
    border:1px solid rgba(2,6,23,.07);
    border-radius:var(--radius);
    box-shadow:0 12px 30px rgba(2,6,23,.08);
  }
  .neo-head{
    padding:16px 18px;
    border-bottom:1px solid var(--border);
    background:linear-gradient(180deg, rgba(255,255,255,.6), rgba(255,255,255,.3));
    backdrop-filter:saturate(160%) blur(2px);
  }
  .neo-foot{
    border-top:1px solid var(--border);
    background:linear-gradient(180deg, rgba(255,255,255,.25), rgba(255,255,255,0));
  }

  /* ===== Tabs ===== */
  .tab-modern .nav-link{
    border:none; background:transparent; color:var(--muted);
    font-weight:600; border-radius:999px; padding:.5rem .9rem;
  }
  .tab-modern .nav-link.active{
    color:var(--brand-ink); background:var(--chip); border:1px solid var(--chip-border);
  }
  .tab-modern .badge{vertical-align:middle; margin-left:.35rem}

  /* ===== Table — RAPIH ===== */
  .table-wrap{overflow:auto; position:relative}
  .modern-table{min-width:1100px; font-size:.925rem}
  .modern-table > :not(caption) > * > * {
    vertical-align: middle !important;
    padding: var(--cell-py) var(--cell-px) !important;
  }
  .modern-table thead th{
    position:sticky; top:var(--thead-offset); z-index:2;
    background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.92));
    backdrop-filter:saturate(160%) blur(2px);
    border-bottom:1px solid var(--border);
    box-shadow:0 1px 0 rgba(2,6,23,.04);
    font-weight:600; color:#334155;
  }
  .modern-table tbody tr{
    background:linear-gradient(180deg, #fff, #fff);
    transition:background-color .12s ease, box-shadow .12s ease;
  }
  .modern-table tbody tr:nth-child(even){
    background:linear-gradient(180deg, var(--stripe), var(--stripe));
  }
  .modern-table tbody tr:hover{ background:var(--hover) }
  .modern-table tbody td{ border-top:1px solid rgba(2,6,23,.06); }
  .modern-table tbody tr:first-child td{ border-top:0; }

  .modern-table th, .modern-table td{ white-space:nowrap; }
  .col-items, .col-pay-info{ white-space:normal; } /* hanya kolom deskriptif yang boleh bungkus */

  /* Kolom lebar khusus */
  .col-total{ width:190px; padding-right:18px; }
  .col-status{ width:170px; padding-left:12px; }
  .col-metode{ width:130px; padding-left:12px; }
  .col-items{ min-width:380px; }
  .col-pay-amount{ width:200px; padding-right:24px; }
  .col-pay-ref{ width:220px; padding-left:20px; }
  .col-pay-info{ min-width:460px; }

  .money{
    display:inline-block; line-height:1.15; margin:0 !important;
    font-variant-numeric: tabular-nums; font-weight:700;
  }

  /* Badges */
  .badge-status{border-radius:999px; font-weight:700; padding:.35rem .6rem; display:inline-block; margin:0 !important; letter-spacing:.2px}
  .badge-status.lunas{background:#dcfce7; color:#065f46}
  .badge-status.parsial{background:#fef3c7; color:#92400e}
  .badge-status.belum{background:#f1f5f9; color:#334155}

  .badge-method{border-radius:999px; font-weight:700; padding:.35rem .6rem; display:inline-block; margin:0 !important;}
  .badge-method.tunai{background:#e6f3ff; color:#1d4ed8}
  .badge-method.transfer{background:#eef2ff; color:#4a57d2}
  .badge-method.qris{background:#f2e8ff; color:#7e22ce}

  .list-items{ margin:.25rem 0 0 1rem; }
  .list-items li{ margin:.1rem 0; }

  /* ===== Responsive: table → cards ===== */
  @media (max-width: 992px){
    .modern-table{min-width:100%}
  }
  @media (max-width: 768px){
    .modern-table thead{ display:none; }
    .modern-table tbody tr{
      display:block; border:1px solid var(--border); border-radius:14px; padding:.6rem .75rem; margin:.75rem 0;
      background:linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.9));
      box-shadow:0 6px 18px rgba(2,6,23,.06);
    }
    .modern-table tbody td{
      display:flex; justify-content:space-between; align-items:flex-start; gap:10px;
      white-space:normal; padding:.35rem 0 !important; border:none !important;
    }
    .modern-table tbody td::before{
      content:attr(data-label);
      font-weight:600; color:var(--muted);
      margin-right:12px; flex:0 0 44%;
    }
    .col-items, .col-pay-info{ min-width:auto; }
    .col-pay-ref{ width:auto; padding-left:0; }
  }

  /* Fokus ring */
  a:focus, button:focus, .nav-link:focus, .form-control:focus{ outline:none; box-shadow:var(--ring) }

  /* Jarak dari header halaman + ruang di bawah head card */
  #aktivitas-index{ margin-top:18px; }
  @media (max-width: 991.98px){ #aktivitas-index{ margin-top:14px; } }
  #aktivitas-index .card-body{ padding-top:12px; }
</style>

<div id="aktivitas-index" class="neo-card">
  <div class="neo-head d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
           style="width:38px;height:38px;background:var(--chip);border:1px solid var(--chip-border)">
        <i class="bi bi-activity" style="font-size:18px;color:var(--brand-ink)"></i>
      </div>
      <div>
        <h5 class="mb-0">Aktivitas Saya</h5>
        <small class="text-muted">Ringkasan tindakan akun ini (dikelompokkan)</small>
      </div>
    </div>
  </div>

  <div class="card-body">
    <ul class="nav nav-tabs tab-modern" role="tablist" id="aktivitas-tab">
      <li class="nav-item">
        <button class="nav-link active" data-coreui-toggle="tab" data-coreui-target="#pane-payments" type="button">
          Pembayaran <span class="badge text-bg-secondary">{{ $payments->count() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-stock" type="button">
          Stok <span class="badge text-bg-secondary">{{ $stock->count() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-barang" type="button">
          Perubahan Barang <span class="badge text-bg-secondary">{{ $barang->count() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-trx" type="button">
          Transaksi <span class="badge text-bg-secondary">{{ $trx->count() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-shift" type="button">
          Shift <span class="badge text-bg-secondary">{{ $shift->count() }}</span>
        </button>
      </li>
    </ul>

    <div class="tab-content pt-3">

      {{-- ================= Pembayaran ================= --}}
      <div class="tab-pane fade show active" id="pane-payments">
        <div class="table-wrap">
          <table class="table table-sm modern-table">
            <thead>
              <tr>
                <th style="width:160px;">Waktu</th>
                <th>Transaksi</th>
                <th>Metode</th>
                <th class="text-end col-pay-amount">Jumlah</th>
                <th class="col-pay-ref">Referensi</th>
                <th class="col-pay-info">Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($payments as $log)
                @php
                  $p    = is_array($log->properties) ? $log->properties : [];
                  $kode = $p['transaksi_kode'] ?? $extractKode($log) ?? ('Transaksi#'.$log->subject_id);
                  $mLbl = isset($p['method']) ? $methodId($p['method']) : null;
                  $mCls = match($mLbl){ 'Tunai'=>'tunai','Transfer'=>'transfer','QRIS'=>'qris', default=>'tunai'};

                  $items = $extractItems($log);
                  $total = $guessTotal($log, $items);
                  $amountPaid = $toFloat($p['amount'] ?? null);
                  $isRefund = $log->event === 'payment.refund';
                @endphp
                <tr>
                  <td data-label="Waktu">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                  <td data-label="Transaksi">
                    @if(Route::has('history.show') && class_basename($log->subject_type)==='Transaksi')
                      <a href="{{ route('history.show', $log->subject_id) }}">{{ $kode }}</a>
                    @else
                      {{ $kode }}
                    @endif
                  </td>
                  <td data-label="Metode">@if($mLbl)<span class="badge-method {{ $mCls }}">{{ $mLbl }}</span>@endif</td>
                  <td data-label="Jumlah" class="text-end col-pay-amount">@if(!is_null($amountPaid))<span class="money">{{ $isRefund ? '-' : '' }}{{ $rupiah($amountPaid) }}</span>@endif</td>
                  <td data-label="Referensi" class="col-pay-ref">{{ $p['reference'] ?? '' }}</td>
                  <td data-label="Keterangan" class="col-pay-info">
                    <div class="small">
                      @if(!is_null($total)) Total transaksi: <strong>{{ $rupiah($total) }}</strong>@endif
                      @if(!is_null($amountPaid)) • Dibayar: <strong>{{ $rupiah($amountPaid) }}</strong>@endif
                      @if($mLbl) • Metode: <strong>{{ $mLbl }}</strong>@endif
                    </div>
                    @if($items)
                      <ul class="list-items">
                        @foreach($items as $it)
                          @php
                            $qtyDisp = rtrim(rtrim(number_format($it['qty'],2,',','.'),'0'),',');
                            $price   = $it['price'];
                            $sub     = $it['subtotal'];
                          @endphp
                          <li>
                            {{ $it['name'] }}
                            × <strong>{{ $qtyDisp }} {{ $it['unit'] }}</strong>
                            @if(!is_null($price)) @ {{ $rupiah($price) }} @endif
                            @if(!is_null($sub)) = <strong>{{ $rupiah($sub) }}</strong> @endif
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pembayaran.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Stok ================= --}}
      <div class="tab-pane fade" id="pane-stock">
        <div class="table-wrap">
          <table class="table table-sm modern-table">
            <thead>
              <tr>
                <th style="width:160px;">Waktu</th>
                <th>Barang</th>
                <th>Unit</th>
                <th class="text-center">Aksi</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Stok (Sebelum → Sesudah)</th>
              </tr>
            </thead>
            <tbody>
              @forelse($stock as $log)
                @php $p = is_array($log->properties) ? $log->properties : []; $aksi = $log->event==='stock.decremented'?'Keluar':'Masuk'; @endphp
                <tr>
                  <td data-label="Waktu">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                  <td data-label="Barang">{{ $p['barang_name'] ?? ('Barang#'.($p['barang_id'] ?? '')) }}</td>
                  <td data-label="Unit">{{ $p['unit_kode'] ?? '' }}</td>
                  <td data-label="Aksi" class="text-center">
                    <span class="badge {{ $log->event==='stock.decremented' ? 'text-bg-primary':'text-bg-success' }}">{{ $aksi }}</span>
                  </td>
                  <td data-label="Qty" class="text-end">{{ isset($p['qty']) ? $nf($p['qty']) : '' }}</td>
                  <td data-label="Stok" class="text-end">
                    @if(isset($p['old_stok'],$p['new_stok'])){{ $nf($p['old_stok']) }} → {{ $nf($p['new_stok']) }}@endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada perubahan stok.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Perubahan Barang ================= --}}
      <div class="tab-pane fade" id="pane-barang">
        <div class="table-wrap">
          <table class="table table-sm modern-table">
            <thead>
              <tr>
                <th style="width:160px;">Waktu</th>
                <th>Aksi</th>
                <th>Barang</th>
                <th>Perubahan</th>
                <th>Gambar</th>
              </tr>
            </thead>
            <tbody>
              @forelse($barang as $log)
                @php
                  $old = is_array($log->old_values)?$log->old_values:[];
                  $new = is_array($log->new_values)?$log->new_values:[];
                  $props = is_array($log->properties)?$log->properties:[];
                  $media = isset($props['media_changes']) && is_array($props['media_changes']) ? $props['media_changes'] : [];
                  $nama = $new['nama'] ?? $old['nama'] ?? ($props['barang_name'] ?? ('Barang#'.$log->subject_id));
                  $changes=[]; foreach($old as $k=>$vOld){ $vNew=$new[$k]??null; $changes[]="<span class='text-secondary'>{$k}</span>: <span class='text-danger'>".$nf($vOld)."</span> → <span class='text-success'>".$nf($vNew)."</span>"; }
                @endphp
                <tr>
                  <td data-label="Waktu">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                  <td data-label="Aksi">{{ $eventId($log->event) }}</td>
                  <td data-label="Barang" class="text-break">{{ $nama }}</td>
                  <td data-label="Perubahan" class="small">{!! count($changes)?implode('<br>',$changes):'' !!}</td>
                  <td data-label="Gambar" class="small">
                    @if(count($media))
                      @foreach($media as $m)
                        @if(($m['attribute']??'')==='image_path')
                          <div class="d-flex gap-2">
                            <div class="text-center">
                              <div class="small text-muted">Sebelum</div>
                              @if(!empty($m['old']))<img src="{{ $m['old'] }}" class="img-thumbnail" style="max-width:100px">@endif
                            </div>
                            <div class="text-center">
                              <div class="small text-muted">Sesudah</div>
                              @if(!empty($m['new']))<img src="{{ $m['new'] }}" class="img-thumbnail" style="max-width:100px">@endif
                            </div>
                          </div>
                          @break
                        @endif
                      @endforeach
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada perubahan data barang.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Transaksi ================= --}}
      <div class="tab-pane fade" id="pane-trx">
        <div class="table-wrap">
          <table class="table table-sm modern-table">
            <thead>
              <tr>
                <th style="width:160px;">Waktu</th>
                <th>Transaksi</th>
                <th class="text-end col-total">Total</th>
                <th class="col-status">Status Bayar</th>
                <th class="col-metode">Metode</th>
                <th class="col-items">Item Terjual</th>
              </tr>
            </thead>
            <tbody>
              @forelse($trx as $log)
                @php
                  $p = is_array($log->properties)?$log->properties:[];
                  $kode   = $p['transaksi_kode'] ?? $extractKode($log) ?? ('Transaksi#'.$log->subject_id);
                  $total  = $p['total_harga'] ?? null;
                  $status = $p['status_bayar'] ?? null;
                  $metode = $p['metode'] ?? null;
                  $items  = (isset($p['items']) && is_array($p['items'])) ? $p['items'] : [];

                  $statusLbl = $status ? $payStatus($status) : null;
                  $statusCls = match($status){ 'paid'=>'lunas','partial'=>'parsial','unpaid'=>'belum', default=>'belum'};
                  $mLbl      = $metode ? $methodId($metode) : null;
                  $mCls      = match($mLbl){ 'Tunai'=>'tunai','Transfer'=>'transfer','QRIS'=>'qris', default=>'tunai'};
                @endphp
                <tr>
                  <td data-label="Waktu">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                  <td data-label="Transaksi">{{ $kode }}</td>
                  <td data-label="Total" class="text-end col-total">
                    @if(!is_null($total))
                      <span class="money">{{ $rupiah($total) }}</span>
                    @endif
                  </td>
                  <td data-label="Status" class="col-status">
                    @if($statusLbl)
                      <span class="badge-status {{ $statusCls }}">{{ $statusLbl }}</span>
                    @elseif($log->event==='transaksi.voided')
                      <span class="badge-status belum">Dibatalkan</span>
                    @endif
                  </td>
                  <td data-label="Metode" class="col-metode">@if($mLbl)<span class="badge-method {{ $mCls }}">{{ $mLbl }}</span>@endif</td>
                  <td data-label="Item Terjual" class="col-items small">
                    @if(count($items) && $log->event==='transaksi.posted')
                      <ul class="list-items mb-0">
                        @foreach($items as $it)
                          <li>
                            {{ ($it['type'] ?? 'barang') === 'jasa' ? 'Jasa' : 'Barang' }}:
                            {{ $it['nama'] ?? '-' }}
                            @if(!empty($it['unit'])) <span class="text-muted">({{ $it['unit'] }})</span>@endif
                            × <strong>{{ $nf($it['qty'] ?? 0) }}</strong>
                          </li>
                        @endforeach
                      </ul>
                    @elseif($log->event==='transaksi.created')
                      <span class="text-muted">Draft transaksi dibuat.</span>
                    @elseif($log->event==='transaksi.voided')
                      <span class="text-danger">Dibatalkan.</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada aktivitas transaksi.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Shift ================= --}}
      <div class="tab-pane fade" id="pane-shift">
        <div class="table-wrap">
          <table class="table table-sm modern-table">
            <thead>
              <tr>
                <th style="width:160px;">Waktu</th>
                <th>Aksi</th>
                <th>Shift</th>
                <th>Ringkasan</th>
              </tr>
            </thead>
            <tbody>
              @forelse($shift as $log)
                @php $p = is_array($log->properties) ? $log->properties : []; @endphp
                <tr>
                  <td data-label="Waktu">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                  <td data-label="Aksi">{{ $eventId($log->event) }}</td>
                  <td data-label="Shift">{{ class_basename($log->subject_type) }}#{{ $log->subject_id }}</td>
                  <td data-label="Ringkasan">
                    @if($log->event==='shift.opened')
                      Kas awal: {{ isset($p['opening_cash']) ? $rupiah($p['opening_cash']) : '' }}
                    @elseif($log->event==='shift.closed')
                      Ekspektasi: {{ isset($p['expected']) ? $rupiah($p['expected']) : '' }}
                      • Kas akhir: {{ isset($p['closing_cash']) ? $rupiah($p['closing_cash']) : '' }}
                      • Selisih: {{ isset($p['difference']) ? $rupiah($p['difference']) : '' }}
                    @else
                      {{ $log->description }}
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas shift.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <div class="neo-foot card-footer py-3">
    <div class="d-flex justify-content-center">
      {{ $logs->withQueryString()->onEachSide(1)->links('components.pagination.pill-clean') }}
    </div>
  </div>
</div>

{{-- Tab persistence (ingat tab terakhir) --}}
<script>
  (function(){
    const nav = document.getElementById('aktivitas-tab');
    if(!nav) return;
    const KEY = 'aktivitas-tab-active';
    // Restore
    const sel = localStorage.getItem(KEY);
    if(sel){
      const btn = nav.querySelector(`[data-coreui-target="${sel}"]`);
      if(btn){ btn.click?.(); }
    }
    // Save when changed
    nav.addEventListener('shown.coreui.tab', function(ev){
      const tgt = ev?.target?.getAttribute?.('data-coreui-target');
      if(tgt){ localStorage.setItem(KEY, tgt); }
    }, true);
  })();
</script>
@endsection
