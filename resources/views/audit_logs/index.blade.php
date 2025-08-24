@extends('layouts.app')
@section('title','Audit Logs')

@section('content')
@php
  /* ================= Helpers (CLOSURES, bukan function global) ================= */
  $nf      = fn($v) => is_numeric($v) ? number_format($v, 0, ',', '.') : (is_bool($v) ? ($v?'true':'false') : ($v===null?'':(string)$v));
  $rupiah  = fn($v) => (is_numeric($v) || (is_string($v) && preg_match('~^\d~',$v))) ? 'Rp '.$nf((float)$v) : '';
  $toFloat = function($v){
    if ($v===null || $v==='') return null;
    if (is_numeric($v)) return (float)$v;
    $s = str_replace([' ', "\u{A0}"], '', (string)$v);
    if (preg_match('~^\d{1,3}(\.\d{3})+(,\d+)?$~', $s)) $s = str_replace('.','',$s);
    $s = str_replace(',','.',$s);
    return is_numeric($s) ? (float)$s : null;
  };
  $payStatus = fn($s) => match($s){ 'paid'=>'Lunas','partial'=>'Sebagian (parsial)','unpaid'=>'Belum dibayar', default=>ucfirst((string)$s) };
  $methodId  = fn($m) => match($m){ 'cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS', default=>ucfirst((string)$m) };
  $eventId   = fn(string $ev) => match ($ev) {
    'transaksi.created' => 'Membuat draft transaksi',
    'transaksi.posted'  => 'Mem-finalkan transaksi',
    'transaksi.voided'  => 'Membatalkan transaksi',
    'payment.added'     => 'Menambah pembayaran',
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
  $extractKode = function ($log) {
    $desc = (string)($log->description ?? '');
    if (preg_match('/TRX[0-9A-Z]+/i', $desc, $m)) return strtoupper($m[0]);
    return null;
  };
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
  $guessTotal = function($log, array $items) use ($toFloat){
    $props = is_array($log->properties) ? $log->properties : [];
    $old   = is_array($log->old_values) ? $log->old_values : [];
    $new   = is_array($log->new_values) ? $log->new_values : [];
    $cands = [
      $props['total_harga'] ?? null, $new['total_harga'] ?? null, $old['total_harga'] ?? null,
      $props['grand_total'] ?? null, $new['grand_total'] ?? null, $old['grand_total'] ?? null,
      $props['total'] ?? null,       $new['total'] ?? null,       $old['total'] ?? null,
    ];
    foreach ($cands as $c) { $f=$toFloat($c); if($f!==null) return $f; }
    if ($items) { $sum=0; foreach($items as $ln) $sum += $toFloat($ln['subtotal'] ?? null) ?? 0; return $sum>0?$sum:null; }
    return null;
  };
  $imageChange = function($log){
    $props = is_array($log->properties) ? $log->properties : [];
    if (isset($props['media_changes']) && is_array($props['media_changes'])) {
      foreach($props['media_changes'] as $m){
        if (($m['attribute'] ?? '') === 'image_path') return ['old'=>$m['old'] ?? null, 'new'=>$m['new'] ?? null];
      }
    }
    // kalau tidak ada di properties, kita tidak paksa tampil dari path raw
    return null;
  };

  // kelompokkan log untuk tab
  $rows = $logs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $logs->getCollection() : collect($logs);
  $payments = $rows->filter(fn($l) => $l->event === 'payment.added');
  $stock    = $rows->filter(fn($l) => in_array($l->event, ['stock.decremented','stock.incremented']));
  $barang   = $rows->filter(fn($l) => class_basename($l->subject_type) === 'Barang' && in_array($l->event, ['created','updated','deleted','restored']));
  $trx      = $rows->filter(fn($l) => str_starts_with($l->event, 'transaksi.'));
  $shift    = $rows->filter(fn($l) => str_starts_with($l->event, 'shift.'));
@endphp

<style>
  /* ====== Header & Card: seragam dgn my_activity ====== */
  #audit-tabs .card{border:0;border-radius:16px}
  #audit-tabs .card-header{
    background:linear-gradient(135deg,#f7f9fc,#eef2f7);
    border-bottom:1px solid #e9edf3;
    position:relative; padding:14px 16px 18px; margin-bottom:6px;
  }

  .audit-table-wrap{overflow-x:auto;}
  .audit-table{min-width:1100px;}
  .audit-table > :not(caption) > * > * { vertical-align: top !important; }
  .audit-table th, .audit-table td { white-space:nowrap; }

  .list-items{ margin:.25rem 0 0 1rem; }
  .list-items li{ margin:.15rem 0; }

  /* Kolom lebar ala my_activity */
  .col-pay-amount{ width:200px; padding-right:24px; }
  .col-pay-ref   { width:220px; padding-left:20px; }
  .col-pay-info  { min-width:460px; white-space:normal; }

  .col-total  { width:190px;  padding-right:18px; }
  .col-status { width:170px;  padding-left:12px; }
  .col-metode { width:130px;  padding-left:12px; }
  .col-items  { min-width:380px; white-space:normal; }

  .money{ display:inline-block; line-height:1.1; margin:0 !important; font-variant-numeric:tabular-nums; font-weight:700; }

  /* Badge */
  .badge-status{border-radius:999px; font-weight:600; padding:.35rem .6rem; display:inline-block; margin:0 !important;}
  .badge-status.lunas{background:#e6f4ea; color:#137333}
  .badge-status.parsial{background:#fff4e5; color:#a15c00}
  .badge-status.belum{background:#f1f3f4; color:#3c4043}

  .badge-method{border-radius:999px; font-weight:600; padding:.35rem .6rem; display:inline-block; margin:0 !important;}
  .badge-method.tunai{background:#e6f3ff; color:#0b57d0}
  .badge-method.transfer{background:#eef2ff; color:#4a57d2}
  .badge-method.qris{background:#f2e8ff; color:#7e22ce}
</style>

<div id="audit-tabs" class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0">Audit Logs</h5>
    <small class="text-muted">Ringkasan aktivitas kasir & admin</small>
  </div>

  <div class="card-body">
    <ul class="nav nav-tabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-coreui-toggle="tab" data-coreui-target="#pane-payments" type="button">Pembayaran <span class="badge text-bg-secondary">{{ $payments->count() }}</span></button></li>
      <li class="nav-item"><button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-stock" type="button">Stok <span class="badge text-bg-secondary">{{ $stock->count() }}</span></button></li>
      <li class="nav-item"><button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-barang" type="button">Perubahan Barang <span class="badge text-bg-secondary">{{ $barang->count() }}</span></button></li>
      <li class="nav-item"><button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-trx" type="button">Transaksi <span class="badge text-bg-secondary">{{ $trx->count() }}</span></button></li>
      <li class="nav-item"><button class="nav-link" data-coreui-toggle="tab" data-coreui-target="#pane-shift" type="button">Shift <span class="badge text-bg-secondary">{{ $shift->count() }}</span></button></li>
    </ul>

    <div class="tab-content pt-3">

      {{-- ================= Pembayaran ================= --}}
      <div class="tab-pane fade show active" id="pane-payments">
        <div class="audit-table-wrap">
          <table class="table table-sm audit-table">
            <thead>
            <tr>
              <th style="width:160px;">Waktu</th>
              <th>Actor</th>
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
              @endphp
              <tr>
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->actor_name }} @if($log->actor_role)<span class="text-muted">({{ $log->actor_role }})</span>@endif</td>
                <td>
                  @if(Route::has('history.show') && class_basename($log->subject_type)==='Transaksi')
                    <a href="{{ route('history.show', $log->subject_id) }}">{{ $kode }}</a>
                  @else
                    {{ $kode }}
                  @endif
                </td>
                <td>@if($mLbl)<span class="badge-method {{ $mCls }}">{{ $mLbl }}</span>@endif</td>
                <td class="text-end col-pay-amount">@if(!is_null($amountPaid))<span class="money">{{ $rupiah($amountPaid) }}</span>@endif</td>
                <td class="col-pay-ref">{{ $p['reference'] ?? '' }}</td>
                <td class="col-pay-info">
                  <div>
                    @if(!is_null($total)) Total transaksi: <strong>{{ $rupiah($total) }}</strong>@endif
                    @if(!is_null($amountPaid)) • Dibayar: <strong>{{ $rupiah($amountPaid) }}</strong>@endif
                    @if($mLbl) • Metode: <strong>{{ $mLbl }}</strong>@endif
                  </div>
                  @if($items)
                    <ul class="list-items">
                      @foreach($items as $it)
                        @php
                          $qtyDisp = rtrim(rtrim(number_format($it['qty'],2,',','.'),'0'),',');
                          $price   = $it['price']; $sub = $it['subtotal'];
                        @endphp
                        <li>
                          {{ $it['name'] }} × <strong>{{ $qtyDisp }} {{ $it['unit'] }}</strong>
                          @if(!is_null($price)) @ {{ $rupiah($price) }} @endif
                          @if(!is_null($sub)) = <strong>{{ $rupiah($sub) }}</strong> @endif
                        </li>
                      @endforeach
                    </ul>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted">Belum ada pembayaran.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Stok ================= --}}
      <div class="tab-pane fade" id="pane-stock">
        <div class="audit-table-wrap">
          <table class="table table-sm audit-table">
            <thead>
            <tr>
              <th style="width:160px;">Waktu</th>
              <th>Actor</th>
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
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->actor_name }} @if($log->actor_role)<span class="text-muted">({{ $log->actor_role }})</span>@endif</td>
                <td>{{ $p['barang_name'] ?? ('Barang#'.($p['barang_id'] ?? '')) }}</td>
                <td>{{ $p['unit_kode'] ?? '' }}</td>
                <td class="text-center"><span class="badge {{ $log->event==='stock.decremented' ? 'text-bg-primary':'text-bg-success' }}">{{ $aksi }}</span></td>
                <td class="text-end">{{ isset($p['qty']) ? $nf($p['qty']) : '' }}</td>
                <td class="text-end">@if(isset($p['old_stok'],$p['new_stok'])){{ $nf($p['old_stok']) }} → {{ $nf($p['new_stok']) }}@endif</td>
              </tr>
            @empty
              <tr><td colspan="7" class="text-center text-muted">Belum ada perubahan stok.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Perubahan Barang ================= --}}
      <div class="tab-pane fade" id="pane-barang">
        <div class="audit-table-wrap">
          <table class="table table-sm audit-table">
            <thead>
            <tr>
              <th style="width:160px;">Waktu</th>
              <th>Actor</th>
              <th>Aksi</th>
              <th>Barang</th>
              <th>Perubahan</th>
              <th>Gambar</th>
            </tr>
            </thead>
            <tbody>
            @forelse($barang as $log)
              @php
                $old = is_array($log->old_values)?$log->old_values:[]; $new = is_array($log->new_values)?$log->new_values:[];
                $props = is_array($log->properties)?$log->properties:[]; $media = $props['media_changes'] ?? [];
                $nama = $new['nama'] ?? $old['nama'] ?? ($props['barang_name'] ?? ('Barang#'.$log->subject_id));
                $changes=[]; foreach($old as $k=>$vOld){ $vNew=$new[$k]??null; $changes[]="<span class='text-secondary'>{$k}</span>: <span class='text-danger'>".$nf($vOld)."</span> → <span class='text-success'>".$nf($vNew)."</span>"; }
                $img = null;
                if (is_array($media)) { foreach($media as $m){ if(($m['attribute']??'')==='image_path'){ $img=['old'=>$m['old']??null,'new'=>$m['new']??null]; break; } } }
              @endphp
              <tr>
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->actor_name }} @if($log->actor_role)<span class="text-muted">({{ $log->actor_role }})</span>@endif</td>
                <td>{{ $eventId($log->event) }}</td>
                <td class="text-break">{{ $nama }}</td>
                <td class="small">{!! count($changes)?implode('<br>',$changes):'' !!}</td>
                <td class="small">
                  @if($img)
                    <div class="d-flex gap-2">
                      <div class="text-center">
                        <div class="small text-muted">Sebelum</div>
                        @if(!empty($img['old']))<img src="{{ $img['old'] }}" class="img-thumbnail" style="max-width:100px">@endif
                      </div>
                      <div class="text-center">
                        <div class="small text-muted">Sesudah</div>
                        @if(!empty($img['new']))<img src="{{ $img['new'] }}" class="img-thumbnail" style="max-width:100px">@endif
                      </div>
                    </div>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">Belum ada perubahan data barang.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Transaksi ================= --}}
      <div class="tab-pane fade" id="pane-trx">
        <div class="audit-table-wrap">
          <table class="table table-sm audit-table">
            <thead>
            <tr>
              <th style="width:160px;">Waktu</th>
              <th>Actor</th>
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
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->actor_name }} @if($log->actor_role)<span class="text-muted">({{ $log->actor_role }})</span>@endif</td>
                <td>{{ $kode }}</td>
                <td class="text-end col-total">@if(!is_null($total))<span class="money">{{ $rupiah($total) }}</span>@endif</td>
                <td class="col-status">
                  @if($statusLbl)
                    <span class="badge-status {{ $statusCls }}">{{ $statusLbl }}</span>
                  @elseif($log->event==='transaksi.voided')
                    <span class="badge-status belum">Dibatalkan</span>
                  @endif
                </td>
                <td class="col-metode">@if($mLbl)<span class="badge-method {{ $mCls }}">{{ $mLbl }}</span>@endif</td>
                <td class="col-items small">
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
              <tr><td colspan="7" class="text-center text-muted">Belum ada aktivitas transaksi.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- ================= Shift ================= --}}
      <div class="tab-pane fade" id="pane-shift">
        <div class="audit-table-wrap">
          <table class="table table-sm audit-table">
            <thead>
            <tr>
              <th style="width:160px;">Waktu</th>
              <th>Actor</th>
              <th>Aksi</th>
              <th>Shift</th>
              <th>Ringkasan</th>
            </tr>
            </thead>
            <tbody>
            @forelse($shift as $log)
              @php $p = is_array($log->properties) ? $log->properties : []; @endphp
              <tr>
                <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->actor_name }} @if($log->actor_role)<span class="text-muted">({{ $log->actor_role }})</span>@endif</td>
                <td>{{ $eventId($log->event) }}</td>
                <td>{{ class_basename($log->subject_type) }}#{{ $log->subject_id }}</td>
                <td>
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
              <tr><td colspan="5" class="text-center text-muted">Belum ada aktivitas shift.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

  <div class="card-footer py-3">
    <div class="d-flex justify-content-center">
      {{ $logs->withQueryString()->onEachSide(1)->links('components.pagination.pill-clean') }}
    </div>
  </div>
</div>
@endsection
