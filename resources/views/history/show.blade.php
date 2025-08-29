@extends('layouts.app')
@section('title','Detail Transaksi')

@section('content')
@include('partials.neo-theme')

{{-- Fonts --}}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
  :root{
    --font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans", "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji";
    --font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;

    --ink:#0f172a; --muted:#6b7280;
    --card:#ffffff; --bg:#f6f8fb; --border:#e5e7eb;
    --thead:#f8fafc;

    /* Palet */
    --indigo-600:#1d4ed8;   /* tombol Kembali/Cetak PDF */
    --indigo-800:#173ea6;
    --amber-500:#f59e0b;
    --amber-700:#b45309;

    --ring-indigo: rgba(29,78,216,.28);
    --ring-amber:  rgba(245,158,11,.28);
  }

  /* ===== Base ===== */
  body{ background:var(--bg); color:var(--ink); font-family:var(--font-sans) }
  h5{ font-weight:700; letter-spacing:.2px }
  .small, .form-text{ color:var(--muted) }
  .code{ font-family:var(--font-mono); font-size:.92rem }

  /* ===== Card ===== */
  .history-card{
    margin:16px 0 36px;
    border:1px solid rgba(2,6,23,.06);
    border-radius:16px;
    background:var(--card);
    box-shadow:0 12px 28px rgba(29,78,216,.12);
  }
  .history-card .card-header{
    border-bottom:1px solid var(--border);
    background:linear-gradient(135deg,#f7f9ff,#eef2ff);
    border-radius:16px 16px 0 0;
    padding:18px 18px 20px;
    margin-bottom:8px;
  }
  .history-card .card-body{ padding:16px }

  /* ===== Buttons (solid; teks SELALU putih) ===== */
  .btn-soft{
    display:inline-flex; align-items:center; gap:8px;
    border-radius:999px; padding:.52rem 1rem;
    font-weight:700; font-size:.92rem;
    border:1px solid transparent; line-height:1.1;
    transition:background-color .18s ease, box-shadow .18s ease, border-color .18s ease;
    color:#fff !important; -webkit-text-fill-color:#fff !important; text-decoration:none !important;
  }
  a.btn-soft, a.btn-soft:link, a.btn-soft:visited,
  a.btn-soft:hover, a.btn-soft:focus, a.btn-soft:active,
  button.btn-soft, button.btn-soft:hover, button.btn-soft:focus, button.btn-soft:active{
    color:#fff !important; -webkit-text-fill-color:#fff !important; text-decoration:none !important;
  }
  .btn-soft *, .btn-soft:hover *, .btn-soft:focus *, .btn-soft:active *{
    color:#fff !important; fill:#fff !important; stroke:#fff !important;
  }
  .btn-soft--indigo{ background:var(--indigo-600); border-color:var(--indigo-600); }
  .btn-soft--indigo:hover{ background:var(--indigo-800); border-color:var(--indigo-800); box-shadow:0 6px 18px var(--ring-indigo); }
  .btn-soft--indigo:focus-visible{ box-shadow:0 0 0 3px var(--ring-indigo) }
  .btn-soft--amber{ background:var(--amber-500); border-color:var(--amber-500); }
  .btn-soft--amber:hover{ background:var(--amber-700); border-color:var(--amber-700); box-shadow:0 6px 18px var(--ring-amber); }
  .btn-soft--amber:focus-visible{ box-shadow:0 0 0 3px var(--ring-amber) }

  /* ===== Badges STATUS (atas) sesuai request sebelumnya ===== */
  /* Diposting */
  .badge.bg-success{
    background-color:#dcfce7 !important;
    color:#065f46 !important;
  }
  /* Sebagian (parsial) */
  .badge.bg-warning{
    background-color:#fef3c7 !important;
    color:#92400e !important;
  }
  /* Refund sebagian (status warning transparan dari CoreUI) */
  .badge.text-bg-warning{
    background-color: rgba(var(--cui-warning-rgb), var(--cui-bg-opacity, 1)) !important;
    color:#080a0c !important;
  }

  /* ===== Badges di TABEL PEMBAYARAN (baris 'Pembayaran' & 'Refund') ===== */
  .badge.text-bg-success{                /* Pembayaran */
    background-color:#dcfce7 !important; /* latar */
    color:#065f46 !important;            /* teks  */
  }
  .badge.text-bg-warning{                /* Refund */
    background-color: rgba(var(--cui-warning-rgb), var(--cui-bg-opacity, 1)) !important;
    color:#080a0c !important;
  }

  /* ===== Table ===== */
  .table{ margin-bottom:0 }
  .table thead th{ background:var(--thead); border-bottom:1px solid var(--border); font-weight:700; }
  .table > :not(caption) > * > *{ padding:.65rem .8rem; vertical-align:middle; }
  .table tfoot th, .table tfoot td{ border-top:1px solid var(--border); background:#fff; }

  /* ===== Info table ===== */
  .info-table{ width:auto }
  .info-table th{ color:var(--muted); font-weight:600; padding-right:18px; white-space:nowrap; }
  .info-table td{ font-weight:600 }

  /* ===== Modal ===== */
  .modal-content{ border-radius:16px }
  .modal-header, .modal-footer{ border-color:var(--border) }

  /* ===== Chips ===== */
  .pill{
    display:inline-block; padding:.15rem .5rem; border-radius:999px;
    font-weight:600; background:#f1f5f9; color:#0f172a; font-size:.78rem;
  }
</style>

<div class="container-fluid px-3 px-sm-4">

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm history-card">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center gap-2">
        <div>
          <h5 class="mb-0">Detail Transaksi</h5>
          <div class="small">Ringkasan transaksi &amp; pembayaran</div>
        </div>
        <div class="d-none d-sm-block">
          {{-- Kembali (desktop) — #1d4ed8 --}}
          <a href="{{ route('history.index') }}" class="btn-soft btn-soft--indigo">Kembali</a>
        </div>
      </div>
    </div>

    <div class="card-body">
      @php
        $status         = $transaksi->status ?? 'posted';
        $paymentStatus  = $transaksi->payment_status ?? 'unpaid';
        $payClass       = ['paid'=>'success','partial'=>'warning','unpaid'=>'secondary'][$paymentStatus] ?? 'secondary';

        $statusId = fn($s) => match($s){ 'draft'=>'Draf','posted'=>'Diposting','void'=>'Dibatalkan', default=>ucfirst((string)$s) };
        $payId    = fn($s) => match($s){ 'paid'=>'Lunas','partial'=>'Sebagian (parsial)','unpaid'=>'Belum dibayar', default=>ucfirst((string)$s) };
        $methodId = fn($m) => match($m){ 'cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS', default=>ucfirst((string)$m) };

        $refundSum = (int) ($transaksi->payments?->where('direction','out')->sum('amount') ?? 0);
        $paidInSum = (int) ($transaksi->payments?->where('direction','in')->sum('amount') ?? 0);
        $netPaid   = max(0, $paidInSum - $refundSum);
      @endphp

      {{-- ===== Status badges (atas) ===== --}}
      <div class="mb-3">
        <span class="badge bg-{{ $status === 'void' ? 'danger' : ($status==='draft'?'secondary':'success') }}">
          {{ $statusId($status) }}
        </span>
        <span class="badge bg-{{ $payClass }}">
          {{ $payId($paymentStatus) }}
        </span>
        @if($refundSum > 0)
          @if($netPaid <= 0)
            <span class="badge text-bg-danger">Refund penuh</span>
          @else
            <span class="badge text-bg-warning">Refund sebagian</span>
          @endif
        @endif
      </div>

      {{-- ===== HEADER INFO ===== --}}
      <table class="table table-borderless info-table mb-4">
        <tbody>
          <tr>
            <th>Kode Transaksi</th>
            <td class="code">{{ $transaksi->kode_transaksi }}</td>
          </tr>
          <tr>
            <th>Tanggal</th>
            <td>{{ optional($transaksi->tanggal)->translatedFormat('d F Y • H:i') ?? optional($transaksi->created_at)->format('d M Y H:i') }} WIB</td>
          </tr>
          @if(!empty($transaksi->metode_bayar))
          <tr>
            <th>Metode Bayar</th>
            <td>{{ $methodId($transaksi->metode_bayar) }}</td>
          </tr>
          @endif

          @php
            $gross=0; $netItems=0;
            foreach($transaksi->items as $it){
              $gross    += (int)$it->jumlah * (int)$it->harga_satuan;
              $netItems += (int)$it->subtotal;
            }
            $itemDisc = max(0, $gross - $netItems);

            $totalIn  = (int) ($transaksi->payments?->where('direction','in')->sum('amount') ?? 0);
            $totalOut = (int) ($transaksi->payments?->where('direction','out')->sum('amount') ?? 0);
            $netPaid  = max(0, $totalIn - $totalOut);
          @endphp

          @if($itemDisc > 0)
          <tr><th>Diskon Item</th><td>@rupiah($itemDisc)</td></tr>
          @endif
          @if((int)$transaksi->discount_amount > 0)
            <tr><th>Diskon Nota</th><td>@rupiah((int)$transaksi->discount_amount)</td></tr>
            @if(!empty($transaksi->discount_reason))
              <tr><th>Alasan Diskon</th><td>{{ $transaksi->discount_reason }}</td></tr>
            @endif
            @if(!empty($transaksi->coupon_code))
              <tr><th>Kupon</th><td>{{ $transaksi->coupon_code }}</td></tr>
            @endif
          @endif
          @if($totalIn > 0)
            <tr><th>Total Pembayaran</th><td>@rupiah($totalIn)</td></tr>
          @endif
          <tr><th>Dibayar</th><td>@rupiah((int)$transaksi->dibayar)</td></tr>
          @if($totalOut > 0)
            <tr><th>Total Refund</th><td class="text-danger">- @rupiah($totalOut)</td></tr>
          @endif
          <tr><th>Kembalian</th><td>@rupiah((int)$transaksi->kembalian)</td></tr>
        </tbody>
      </table>

      {{-- ===== TABLE ITEM ===== --}}
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th style="width:56px">#</th>
              <th>Nama Barang / Jasa</th>
              <th style="width:220px">Jumlah</th>
              <th style="width:180px">Harga Satuan</th>
              <th style="width:180px">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transaksi->items as $it)
              @php
                $refunded = (int)($it->refunded_qty ?? 0);
                $sold     = (int)$it->jumlah;
                $remain   = max(0, $sold - $refunded);
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="text-break">
                  {{ $it->tipe_item == 'barang' ? ($it->barang->nama ?? '-') : ($it->jasa->nama ?? '-') }}
                  <div class="small">
                    <span class="pill">{{ $it->tipe_item==='barang' ? 'Barang' : 'Jasa' }}</span>
                  </div>
                </td>
                <td>
                  {{ (int)$it->jumlah }}
                  {{ $it->tipe_item == 'barang'
                       ? (isset($it->tipe_qty) && $it->tipe_qty == 'paket' ? 'paket' : 'pcs')
                       : ($it->jasa->satuan ?? '') }}
                  @if($it->tipe_item == 'barang' && (isset($it->tipe_qty) && $it->tipe_qty == 'paket') && isset($it->barang->isi_per_paket))
                    <span class="small text-muted">(isi {{ $it->barang->isi_per_paket }})</span>
                  @endif
                  @if($refunded>0 || $remain < $sold)
                    <div class="small text-muted mt-1">Terrefund: {{ $refunded }} • Sisa: {{ $remain }}</div>
                  @endif
                </td>
                <td>@rupiah((int)$it->harga_satuan)</td>
                <td>@rupiah((int)$it->subtotal)</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total</th>
              <th>@rupiah((int)$transaksi->total_harga)</th>
            </tr>
          </tfoot>
        </table>
      </div>

      {{-- ===== DAFTAR PEMBAYARAN & REFUND ===== --}}
      @if(($transaksi->payments?->count() ?? 0) > 0)
      <div class="table-responsive mt-3">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 180px">Waktu</th>
              <th style="width: 140px">Jenis</th>
              <th style="width: 120px">Metode</th>
              <th>Referensi</th>
              <th class="text-end" style="width: 180px">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transaksi->payments as $p)
              <tr>
                <td>{{ optional($p->paid_at)->format('d M Y H:i') }}</td>
                <td>
                  <span class="badge {{ $p->direction==='out'?'text-bg-warning':'text-bg-success' }}">
                    {{ $p->direction === 'out' ? 'Refund' : 'Pembayaran' }}
                  </span>
                </td>
                <td>{{ strtoupper($p->method) }}</td>
                <td class="text-break">{{ $p->reference ?? ($p->note ?? '-') }}</td>
                <td class="text-end {{ $p->direction==='out'?'text-danger':'' }}">
                  {{ $p->direction==='out' ? '-' : '' }}@rupiah((int)$p->amount)
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total Pembayaran</th>
              <th class="text-end">@rupiah($totalIn)</th>
            </tr>
            @if($totalOut>0)
            <tr>
              <th colspan="4" class="text-end">Total Refund</th>
              <th class="text-end text-danger">- @rupiah($totalOut)</th>
            </tr>
            @endif
            <tr>
              <th colspan="4" class="text-end">Dibayar Bersih</th>
              <th class="text-end">@rupiah($netPaid)</th>
            </tr>
          </tfoot>
        </table>
      </div>
      @endif

      {{-- ===== ACTION BUTTONS ===== --}}
      <div class="d-flex justify-content-end gap-2 mt-3 flex-wrap">
        <a href="{{ route('history.pdf', $transaksi->id) }}" class="btn-soft btn-soft--indigo" target="_blank">Cetak PDF</a>

        @if(($status ?? 'posted') !== 'void' && (int)($transaksi->dibayar ?? 0) > 0)
          @php $maxRefund = (int) ($transaksi->dibayar ?? 0); @endphp

          <button
            class="btn-soft btn-soft--amber"
            data-coreui-toggle="modal" data-coreui-target="#refundModal"
            data-id="{{ $transaksi->id }}"
            data-kode="{{ $transaksi->kode_transaksi }}"
            data-max="{{ $maxRefund }}">
            Refund
          </button>

          <button
            class="btn-soft btn-soft--amber"
            data-coreui-toggle="modal" data-coreui-target="#refundItemsModal">
            Refund Per Item
          </button>
        @endif

        {{-- Kembali (mobile) — #1d4ed8 --}}
        <a href="{{ route('history.index') }}" class="btn-soft btn-soft--indigo d-sm-none">Kembali</a>
      </div>
    </div>
  </div>
</div>

{{-- ============ MODAL: REFUND ============ --}}
@php $shiftOpen = \App\Models\KasirShift::openBy(auth()->id())->exists(); @endphp
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="refundForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="refundTitle">Refund (Pengembalian Dana)</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small">TRX: <span id="refundKode">-</span></div>
          <div class="mb-3">
            <label class="form-label">Nominal (Rp)</label>
            <input type="text" inputmode="numeric" class="form-control money-input" name="amount" id="refundAmount" placeholder="0">
            <div class="form-text">Maksimal refund: <span id="refundMax">Rp0</span></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Metode</label>
            <select class="form-select" name="method">
              <option value="cash" {{ $shiftOpen ? '' : 'disabled' }}>Cash</option>
              <option value="transfer">Transfer</option>
              <option value="qris">QRIS</option>
            </select>
            @unless($shiftOpen)
              <div class="form-text text-warning">Shift belum dibuka. Metode Cash dinonaktifkan.</div>
            @endunless
          </div>
          <div class="mb-3">
            <label class="form-label">Alasan Refund</label>
            <input type="text" class="form-control" name="reason" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Referensi (opsional)</label>
            <input type="text" class="form-control" name="reference" placeholder="No. transfer / catatan">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-soft btn-soft--amber">Simpan Refund</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- ============ MODAL: REFUND PER ITEM (RETUR STOK) ============ --}}
<div class="modal fade" id="refundItemsModal" tabindex="-1" aria-labelledby="refundItemsTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="POST" id="refundItemsForm" action="{{ route('pembayaran.refund_items', $transaksi->id) }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="refundItemsTitle">Refund Per Item (Retur Stok)</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info" style="border-radius:12px">
            Pilih item dan jumlah yang akan direfund. Untuk <strong>barang</strong>, stok akan <strong>dikembalikan</strong> sesuai jumlah direfund. Nominal refund dihitung otomatis proporsional terhadap diskon nota.
          </div>

          <div class="table-responsive mb-3">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Item</th>
                  <th class="text-center" style="width:120px">Terjual</th>
                  <th class="text-center" style="width:140px">Refund</th>
                  <th class="text-end" style="width:160px">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @foreach($transaksi->items as $it)
                  @php
                    $nama = $it->tipe_item==='barang' ? ($it->barang->nama ?? '-') : ($it->jasa->nama ?? '-');
                    $unit = $it->tipe_item==='barang' ? ($it->unit->kode ?? 'pcs') : ($it->jasa->satuan ?? '');
                    $refunded = (int)($it->refunded_qty ?? 0);
                    $sold     = (int)$it->jumlah;
                    $remain   = max(0, $sold - $refunded);
                  @endphp
                  <tr>
                    <td class="text-break">
                      <div class="fw-semibold">{{ $nama }}</div>
                      <div class="small">
                        {{ $it->tipe_item==='barang' ? 'Barang' : 'Jasa' }}
                        @if($unit) • {{ strtoupper($unit) }} @endif
                        @if($it->tipe_item==='barang') • <span class="text-success">Stok akan dikembalikan</span>@endif
                      </div>
                    </td>
                    <td class="text-center">
                      {{ $sold }}
                      @if($refunded>0)
                        <div class="small text-muted">Terrefund: {{ $refunded }} • Sisa: {{ $remain }}</div>
                      @endif
                    </td>
                    <td class="text-center">
                      <input type="number" min="0" max="{{ $remain }}" value="0"
                             name="items[{{ $it->id }}]"
                             class="form-control form-control-sm"
                             style="max-width:90px; display:inline-block"
                             {{ $remain<=0 ? 'disabled' : '' }}>
                    </td>
                    <td class="text-end">@rupiah((int)$it->subtotal)</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Metode</label>
              <select class="form-select" name="method">
                <option value="cash" {{ $shiftOpen ? '' : 'disabled' }}>Cash</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
              </select>
              @unless($shiftOpen)
                <div class="form-text text-warning">Shift belum dibuka. Metode Cash dinonaktifkan.</div>
              @endunless
            </div>
            <div class="col-md-8">
              <label class="form-label">Alasan Refund</label>
              <input type="text" class="form-control" name="reason" required>
              <div class="form-text">Contoh: barang rusak, retur sebagian, salah input qty, dll.</div>
            </div>
            <div class="col-12">
              <label class="form-label">Referensi (opsional)</label>
              <input type="text" class="form-control" name="reference" placeholder="No. transfer / catatan">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-soft btn-soft--amber">Simpan Refund Per Item</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- JS --}}
<script>
(function(){
  const formatRibuan = (val) => {
    const n = Math.max(0, parseInt((val||'').toString().replace(/\D+/g,'')) || 0);
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n);
  };
  const currencyID = (v) => 'Rp' + formatRibuan(v);
  const normalizeDigits = (val) => ((val||'').toString().replace(/\D+/g,'') || '');

  document.getElementById('refundModal')?.addEventListener('show.coreui.modal', e=>{
    const btn = e.relatedTarget;
    const id  = btn?.dataset.id;
    const kode= btn?.dataset.kode;
    const max = Number(btn?.dataset.max || 0);

    document.getElementById('refundKode').textContent = kode || '-';
    document.getElementById('refundMax').textContent  = currencyID(max);

    const inp = document.getElementById('refundAmount');
    if (inp) { inp.value = formatRibuan(max); inp.focus(); }

    const form = document.getElementById('refundForm');
    if (form) form.action = "{{ url('/pembayaran/refund') }}/"+id;
  });

  const moneyInp = document.getElementById('refundAmount');
  if (moneyInp) {
    moneyInp.addEventListener('input', ()=>{ moneyInp.value = formatRibuan(moneyInp.value); });
    moneyInp.form && moneyInp.form.addEventListener('submit', ()=>{ moneyInp.value = normalizeDigits(moneyInp.value); });
  }
})();
</script>
@endsection
