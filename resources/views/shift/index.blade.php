@extends('layouts.app')

@section('title', 'Shift Kasir')

@section('content')
{{-- Fonts & Icons --}}
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root{
    --bg:#f7f8fb; --card:#ffffff; --ink:#0f172a; --muted:#64748b;
    --brand:#1d4ed8; --brand-ink:#0b3aa7;
    --ok:#16a34a; --danger:#dc2626;
    --border:#e5e7eb; --hover:#f8fafc; --radius:14px;
    --ring:0 0 0 3px rgba(29,78,216,.15);
    --ease-out:cubic-bezier(.22,1,.36,1);
    --ease-in:cubic-bezier(.4,0,1,1);
  }
  @media (prefers-color-scheme: dark){
    :root{
      --bg:#0b1020; --card:#0f1528; --ink:#e6ebff; --muted:#9aa4c1;
      --border:#1d2440; --hover:#0f1834;
    }
    .table-light th{ background:#0e1630 !important; color:#cbd5ff !important; }
  }

  body{ background:var(--bg); color:var(--ink); font-family:"Inter",system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif; }

  /* Card */
  .card{ border:1px solid rgba(2,6,23,.06); border-radius:var(--radius); background:var(--card); box-shadow:0 10px 24px rgba(2,6,23,.06); }
  .card-header{ border-bottom:1px solid rgba(2,6,23,.06); background:var(--card); }
  .card-header h5,.card-header h6{ font-weight:700; letter-spacing:.2px }
  .card-body{ padding:1rem 1rem; }
  .card-footer{ padding:1rem 1rem; background:var(--card); border-top:1px solid rgba(2,6,23,.06); }

  /* Buttons */
  .btn{ border-radius:10px; transition:transform .05s ease, box-shadow .2s ease, filter .2s ease; }
  .btn:focus{ box-shadow: var(--ring); outline:none; }
  .btn:active{ transform: translateY(1px); }
  .btn-brand{ background:var(--brand); border-color:var(--brand); color:#fff; }
  .btn-brand:hover{ background:var(--brand-ink); border-color:var(--brand-ink); }
  .btn-danger{ background:var(--danger); border-color:var(--danger); color:#fff; }
  .btn-outline-danger{ border-color:var(--danger); color:var(--danger); }
  .btn-outline-danger:hover{ background:var(--danger); color:#fff; }

  /* Chip status */
  .chip{ display:inline-flex; align-items:center; gap:.45rem; padding:.28rem .6rem; border-radius:999px; border:1px solid var(--border); font-weight:600; font-size:.8rem; }
  .chip i{ font-size:.95rem }
  .chip.open{ background:rgba(22,163,74,.08); border-color:rgba(22,163,74,.22); color:#166534; }
  .chip.closed{ background:#f1f5f9; border-color:#e2e8f0; color:#475569; }
  @media (prefers-color-scheme: dark){
    .chip.closed{ background:#0f1834; border-color:#1b2a5a; color:#cbd5ff; }
  }

  /* Pane */
  .pane{ border:1px solid var(--border); border-radius:12px; padding:12px; background:var(--card); }
  .pane h6{ font-weight:600; font-size:.95rem; margin-bottom:.5rem }

  /* Money inputs */
  .money-prefix{position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); font-weight:600}
  .money-wrap{position:relative}
  .money-wrap input{padding-left:46px}

  /* ===== Clean table ===== */
  .table-clean{
    --header-bg:#f6f8ff; --row-hover:var(--hover);
    font-size:.95rem;
  }
  .table-clean thead th{
    background:var(--header-bg) !important;
    font-weight:600; color:#1f2937;
    position:sticky; top:0; z-index:1;
    border-bottom:1px solid var(--border) !important;
    padding-top:12px; padding-bottom:12px;
  }
  .table-clean thead th:first-child{ border-top-left-radius:8px; }
  .table-clean thead th:last-child{ border-top-right-radius:8px; }
  @media (prefers-color-scheme: dark){
    .table-clean thead th{ background:#0e1630 !important; color:#e2e8f0; }
  }
  .table-clean td,.table-clean th{ padding:10px 12px; vertical-align:middle; border-bottom:1px solid var(--border); }
  .table-clean tbody tr:hover{ background:var(--row-hover); }
  .table-clean .num{ text-align:right; white-space:nowrap; }
  .table-clean .nowrap{ white-space:nowrap; }
  #shift-index{ margin-top:14px; }
  .num{ text-align:right; font-variant-numeric: tabular-nums; font-feature-settings:"tnum" 1, "lnum" 1; white-space:nowrap; }
  .nowrap{ white-space:nowrap; }

  /* Ops (Kas Ops) */
  .ops-inline{ display:inline-flex; align-items:center; gap:.4rem; white-space:nowrap; }
  .ops-inline .in{ color:var(--ok); font-weight:600; }
  .ops-inline .out{ color:var(--danger); font-weight:600; }
  .ops-inline .sep{ color:var(--muted); }
  .ops-inline .label{ color:var(--muted); font-weight:600; font-size:.82rem; }

  /* ===== Toast (container hanya mengatur visibility; animasi di .toast-card via WAAPI) ===== */
  .fly-toast{
    position: fixed; right:16px; bottom:16px; z-index:1080;
    width:min(360px,calc(100% - 24px));
    opacity:0; visibility:hidden; pointer-events:none;
  }
  .fly-toast.show{
    opacity:1; visibility:visible; pointer-events:auto;
  }
  .fly-toast .toast-card{
    background:var(--card); border:1px solid rgba(2,6,23,.08); border-radius:12px;
    box-shadow:0 16px 40px rgba(2,6,23,.14); overflow:hidden;
    will-change: transform, opacity, filter;
  }
  .fly-toast header{ display:flex; align-items:center; justify-content:space-between; gap:.5rem; padding:10px 12px; border-bottom:1px solid rgba(2,6,23,.06); background:var(--card); }
  .fly-toast header .title{ display:flex; align-items:center; gap:.5rem; font-weight:700; font-size:.95rem; }
  .fly-toast .toast-body{ padding:12px; color:var(--muted); font-size:.92rem; }
  .fly-toast .toast-footer{ padding:8px 12px; display:flex; justify-content:flex-end; gap:.5rem; background:var(--card); border-top:1px solid rgba(2,6,23,.06); }
  .fly-toast .progressbar{ height:3px; width:100%; background:#eef2ff; position:relative; overflow:hidden; }
  .fly-toast .progressbar > i{ position:absolute; left:0; top:0; bottom:0; width:100%; transform-origin:left; transform: scaleX(1); background:#1d4ed8; }

  /* Floating help */
  .fab-help{
    position:fixed; right:20px; bottom:80px; z-index:1081;
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem .8rem; border-radius:999px; border:1px solid var(--border);
    background:var(--card); box-shadow:0 8px 20px rgba(2,6,23,.12);
    cursor:pointer; user-select:none; transition:opacity .2s ease, transform .2s ease, visibility 0s linear;
    opacity:.55; /* less obtrusive by default */
  }
  .fab-help:hover{ transform: translateY(-1px); opacity:1; }
  .fab-help:focus,.fab-help:focus-visible{ opacity:1; }
  .fab-help.is-hidden{ opacity:0; transform: translateY(8px) scale(.98); visibility:hidden; pointer-events:none; }
  .fab-help span{ font-weight:600; font-size:.85rem; }

  @media (max-width: 576px){
    .card-header .flex-sm{flex-direction:column; align-items:flex-start !important; gap:.5rem}
    .fab-help span{ display:none; }
  }
</style>

<div id="shift-index" class="container-fluid">

  {{-- Kartu utama --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-sm">
          <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0">Shift {{ auth()->user()->name ?? 'Saya' }}</h5>
            @if ($myOpen)
              <span class="chip open"><i class="bi bi-unlock-fill"></i> Terbuka</span>
            @else
              <span class="chip closed"><i class="bi bi-lock-fill"></i> Tertutup</span>
            @endif
          </div>
          <div class="d-flex align-items-center gap-3">
            @if(session('success')) <span class="text-success fw-semibold"><i class="bi bi-check-circle"></i> {{ session('success') }}</span> @endif
            @if($errors->any()) <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle"></i> {{ $errors->first() }}</span> @endif
          </div>
        </div>

        <div class="card-body">
          @if ($myOpen)
            @php
              $opsOpen = \App\Models\CashMovement::where('shift_id',$myOpen->id)
                ->selectRaw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) as masuk, SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) as keluar")
                ->first();
              $inOpen  = (int)($opsOpen->masuk ?? 0);
              $outOpen = (int)($opsOpen->keluar ?? 0);
            @endphp

            <div class="pane mb-3">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap gap-4">
                  <div><div class="text-muted small">Dibuka</div><div class="fw-semibold">{{ $myOpen->opened_at?->format('d/m/Y H:i') }}</div></div>
                  <div><div class="text-muted small">Kas Awal</div><div class="fw-semibold">@rupiah($myOpen->opening_cash)</div></div>
                  <div>
                    <div class="text-muted small">Kas Ops</div>
                    <div class="ops-inline">
                      <span class="in">+@rupiah($inOpen)</span>
                      <span class="sep">/</span>
                      <span class="out">-@rupiah($outOpen)</span>
                    </div>
                  </div>
                </div>
                @if($myOpen->notes)
                  <div class="small text-muted"><i class="bi bi-journal-text"></i> {{ $myOpen->notes }}</div>
                @endif
              </div>
              <div class="mt-2 small text-muted">
                <em>Kas Ekspektasi</em> sementara mengikuti sistem (kas awal; akan + penjualan/refund tunai setelah POS aktif).
              </div>
            </div>

            {{-- Kas Masuk/Keluar --}}
            <div class="row g-3 mb-3">
              <div class="col-lg-6">
                <form id="cashInForm" action="{{ route('shift.cash_in') }}" method="post" class="pane">
                  @csrf
                  <h6 class="d-flex align-items-center gap-2"><i class="bi bi-plus-circle"></i> Kas Masuk</h6>
                  <div class="mb-2 money-wrap">
                    <span class="money-prefix">Rp</span>
                    <input type="text" id="cashInAmountView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Nominal (Rp)" required>
                    <input type="hidden" name="amount" id="cashInAmount" value="0">
                  </div>
                  <div class="mb-2"><input type="text" class="form-control" name="reference" placeholder="Referensi (opsional)"></div>
                  <div class="mb-3"><input type="text" class="form-control" name="note" placeholder="Catatan (opsional)"></div>
                  <div class="d-flex justify-content-end">
                    <button class="btn btn-brand btn-sm"><i class="bi bi-save"></i> Simpan</button>
                  </div>
                </form>
              </div>
              <div class="col-lg-6">
                <form id="cashOutForm" action="{{ route('shift.cash_out') }}" method="post" class="pane">
                  @csrf
                  <h6 class="d-flex align-items-center gap-2"><i class="bi bi-dash-circle"></i> Kas Keluar</h6>
                  <div class="mb-2 money-wrap">
                    <span class="money-prefix">Rp</span>
                    <input type="text" id="cashOutAmountView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Nominal (Rp)" required>
                    <input type="hidden" name="amount" id="cashOutAmount" value="0">
                  </div>
                  <div class="mb-2"><input type="text" class="form-control" name="reference" placeholder="Referensi (opsional)"></div>
                  <div class="mb-3"><input type="text" class="form-control" name="note" placeholder="Catatan (opsional)"></div>
                  <div class="d-flex justify-content-end">
                    <button class="btn btn-danger btn-sm"><i class="bi bi-save"></i> Simpan</button>
                  </div>
                </form>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="button" id="saveBothBtn" class="btn btn-primary btn-sm">
                  <i class="bi bi-save2"></i> Simpan Keduanya
                </button>
              </div>
            </div>

            {{-- Tutup Shift --}}
            <form action="{{ route('shift.close', $myOpen) }}" method="POST" class="row g-3">
              @csrf
              <div class="col-md-6">
                <label class="form-label fw-semibold">Kas Akhir (Closing Cash)</label>
                <div class="money-wrap">
                  <span class="money-prefix">Rp</span>
                  <input type="text" id="closingCashView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Isi langsung atau pakai hitung lembar di bawah">
                  <input type="hidden" name="closing_cash" id="closingCash" value="">
                </div>
                <div class="form-text">Jika mengisi “Hitung Lembar Uang”, nilai ini dihitung otomatis.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Catatan (opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="mis: selisih karena uang receh">
              </div>

              {{-- Hitung lembar uang --}}
              <div class="col-12">
                <div class="pane">
                  <h6 class="d-flex align-items-center gap-2"><i class="bi bi-calculator"></i> Hitung Lembar Uang</h6>
                  <div class="row g-2">
                    @php $noms=[100000,50000,20000,10000,5000,2000,1000,500,200,100]; @endphp
                    @foreach($noms as $n)
                      <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small">Rp{{ number_format($n,0,',','.') }}</label>
                        <input type="number" name="denom[{{ $n }}]" class="form-control" min="0" value="0">
                      </div>
                    @endforeach
                  </div>
                  <div class="small text-muted mt-2">Isi jumlah lembar/koin. Sistem mengakumulasikan ke Kas Akhir.</div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-danger">
                  <i class="bi bi-lock-fill"></i> Tutup Shift
                </button>
              </div>
            </form>
          @else
            {{-- Buka Shift --}}
            <form action="{{ route('shift.open') }}" method="POST" class="row g-3">
              @csrf
              <div class="col-md-6">
                <label class="form-label fw-semibold">Kas Awal (Opening Cash)</label>
                <div class="money-wrap">
                  <span class="money-prefix">Rp</span>
                  <input type="text" id="openingCashView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="0" required>
                  <input type="hidden" name="opening_cash" id="openingCash" value="0">
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Catatan (opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="mis: uang awal dari brankas">
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-brand"><i class="bi bi-unlock-fill"></i> Buka Shift</button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Riwayat --}}
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Riwayat Shift</h5>
    </div>
    <div class="card-body">
      @if($myOpen)
        @php
          $movs = \App\Models\CashMovement::where('shift_id',$myOpen->id)->orderByDesc('id')->limit(10)->get();
        @endphp
        @if(count($movs))
          <div class="mb-3">
            <div class="fw-semibold mb-2">Kas Masuk/Keluar Terakhir</div>
            <div class="table-responsive">
              <table class="table table-sm table-clean">
                <thead class="table-light"><tr><th>Waktu</th><th>Arah</th><th class="num">Nominal</th><th>Referensi</th><th>Catatan</th></tr></thead>
                <tbody>
                  @foreach($movs as $m)
                    <tr>
                      <td class="nowrap">{{ $m->occurred_at?->format('d/m/Y H:i') ?? $m->created_at?->format('d/m/Y H:i') }}</td>
                      <td class="nowrap">
                        @if($m->direction==='in')
                          <span class="text-success fw-semibold">MASUK</span>
                        @else
                          <span class="text-danger fw-semibold">KELUAR</span>
                        @endif
                      </td>
                      <td class="num">@rupiah($m->amount)</td>
                      <td class="nowrap">{{ $m->reference }}</td>
                      <td>{{ $m->note }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif
      @endif

      @php
        $displayRecent = collect($recent instanceof \Illuminate\Pagination\LengthAwarePaginator ? $recent->items() : $recent)->take(5);
        $baseIndex = method_exists($recent,'firstItem') ? ($recent->firstItem() ?? 1) : 1;
      @endphp

      <div id="recWrap" class="table-responsive" style="max-height: 420px;">
        <table class="table table-striped mb-0 table-clean">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Kasir</th>
              <th>Dibuka</th>
              <th class="num">Kas Awal</th>
              <th>Ditutup</th>
              <th class="num">Kas Akhir</th>
              <th class="num">Ekspektasi</th>
              <th>Kas Ops</th>
              <th class="num">Selisih</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($displayRecent as $idx => $s)
              @php
                $ops = \App\Models\CashMovement::where('shift_id',$s->id)
                  ->selectRaw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) as masuk, SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) as keluar")
                  ->first();
                $in  = (int)($ops->masuk ?? 0);
                $out = (int)($ops->keluar ?? 0);
                $diff = (int) $s->difference;
                $absDiff = abs($diff);
                $sign = $diff>0 ? '+' : ($diff<0 ? '-' : '');
                $cls  = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-secondary');
              @endphp
              <tr>
                <td class="nowrap">{{ $baseIndex + $idx }}</td>
                <td class="nowrap">{{ $s->user?->name ?? '—' }}</td>
                <td class="nowrap">{{ $s->opened_at?->format('d/m/Y H:i') }}</td>
                <td class="num">@rupiah($s->opening_cash)</td>
                <td class="nowrap">{{ $s->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="num">@if(!is_null($s->closing_cash)) @rupiah($s->closing_cash) @else — @endif</td>
                <td class="num">@rupiah($s->expected_cash)</td>
                <td class="nowrap">
                  <span class="ops-inline">
                    <span class="in">+@rupiah($in)</span>
                    @if($out>0)<span class="sep">/</span><span class="out">-@rupiah($out)</span>@endif
                  </span>
                </td>
                <td class="num {{ $cls }}">{{ $sign }}@rupiah($absDiff)</td>
                <td class="nowrap">
                  @if($s->status==='open')
                    <span class="chip open"><i class="bi bi-unlock-fill"></i> Terbuka</span>
                  @else
                    <span class="chip closed"><i class="bi bi-lock-fill"></i> Tertutup</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="10" class="text-center text-muted">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    {{-- footer pagination disembunyikan --}}
  </div>

</div>

{{-- Toast Tips --}}
<div id="tipsToast" class="fly-toast" role="alert" aria-live="polite" aria-atomic="true">
  <div class="toast-card">
    <header>
      <div class="title">
        <i class="bi bi-lightbulb"></i>
        <span>Tips</span>
      </div>
      <button type="button" class="btn-close" aria-label="Tutup" data-close-toast></button>
    </header>
    <div class="toast-body">
      <ul class="mb-0" style="display:none">
        <li>Hanya boleh ada satu shift <em>open</em> per kasir.</li>
        <li><strong>Kas Ekspektasi</strong> sementara = Kas Awal. Setelah modul POS aktif, ekspektasi = kas awal + pemasukan tunai − refund tunai.</li>
        <li>Tutup shift setiap selesai jaga agar rekonsiliasi rapi.</li>
      </ul>
      <ul class="mb-0">
        <li>Hanya boleh ada satu shift <em>open</em> per kasir.</li>
        <li>Kas operasional: gunakan Kas Masuk/Keluar untuk non-transaksi (setor modal, biaya kecil). Penjualan/refund tunai dari POS tercatat otomatis.</li>
        <li>Kas Ekspektasi = Kas Awal + Penjualan Tunai - Refund Tunai + Kas Masuk (ops) - Kas Keluar (ops).</li>
        <li>Mengisi keduanya sekaligus? Klik "Simpan Keduanya" isian tetap tersimpan meski simpan salah satunya duluan.</li>
        <li>Sebelum tutup shift, gunakan "Hitung Lembar Uang" agar Kas Akhir otomatis; selisih dihitung sistem.</li>
      </ul>
    </div>
    <div class="toast-footer">
      <button class="btn btn-sm btn-brand" data-close-toast>Sip, paham</button>
    </div>
    <div class="progressbar" aria-hidden="true"><i id="tipsProgress"></i></div>
  </div>
</div>

{{-- Floating help --}}
<button type="button" class="fab-help" id="helpToastBtn" title="Lihat Tips">
  <i class="bi bi-question-circle"></i>
  <span>Butuh Tips?</span>
</button>
@endsection

@push('scripts')
<script>
  // ===== Formatter uang ID =====
  const clean = s => +(String(s||'').replace(/[^0-9]/g,''))||0;
  const idFormat = n => (Number(n)||0).toLocaleString('id-ID');
  function bindMoneyPair(viewId, hidId){
    const v=document.getElementById(viewId), h=document.getElementById(hidId); if(!v||!h) return;
    const sync=()=>{
      if(v.value.trim()===''){ h.value=''; return; }
      const raw=clean(v.value);
      v.value = raw? idFormat(raw):'';
      h.value = raw;
    };
    v.addEventListener('input', sync);
    v.addEventListener('blur', sync);
    if(h.value){ v.value = idFormat(h.value); }
  }
  bindMoneyPair('openingCashView','openingCash');
  bindMoneyPair('closingCashView','closingCash');
  bindMoneyPair('cashInAmountView','cashInAmount');
  bindMoneyPair('cashOutAmountView','cashOutAmount');

  // ===== Persist Kas Masuk/Keluar form inputs (localStorage) =====
  (function(){
    const $ = (s, ctx=document) => ctx.querySelector(s);
    const LS_KEYS = {
      in:   { amount:'shift.cashIn.amount',  ref:'shift.cashIn.reference',  note:'shift.cashIn.note' },
      out:  { amount:'shift.cashOut.amount', ref:'shift.cashOut.reference', note:'shift.cashOut.note' }
    };

    const inForm = $('#cashInForm');
    const outForm= $('#cashOutForm');

    const inView = $('#cashInAmountView');
    const inHid  = $('#cashInAmount');
    const inRef  = inForm?.querySelector('input[name="reference"]');
    const inNote = inForm?.querySelector('input[name="note"]');

    const outView= $('#cashOutAmountView');
    const outHid = $('#cashOutAmount');
    const outRef = outForm?.querySelector('input[name="reference"]');
    const outNote= outForm?.querySelector('input[name="note"]');

    // Restore
    function restore(){
      try{
        const ia = +(localStorage.getItem(LS_KEYS.in.amount) || 0);
        if(inView && inHid && ia>0){ inHid.value = ia; inView.value = idFormat(ia); }
        const ir = localStorage.getItem(LS_KEYS.in.ref);
        const in_ = localStorage.getItem(LS_KEYS.in.note);
        if(inRef && ir !== null)  inRef.value  = ir;
        if(inNote && in_ !== null) inNote.value = in_;

        const oa = +(localStorage.getItem(LS_KEYS.out.amount) || 0);
        if(outView && outHid && oa>0){ outHid.value = oa; outView.value = idFormat(oa); }
        const orf = localStorage.getItem(LS_KEYS.out.ref);
        const on  = localStorage.getItem(LS_KEYS.out.note);
        if(outRef && orf !== null) outRef.value = orf;
        if(outNote && on !== null)  outNote.value = on;
      }catch(e){}
    }

    // Save per field
    function wire(){
      if(inView) inView.addEventListener('input', ()=> localStorage.setItem(LS_KEYS.in.amount, String(clean(inView.value))));
      if(inRef)  inRef.addEventListener('input',  ()=> localStorage.setItem(LS_KEYS.in.ref, inRef.value));
      if(inNote) inNote.addEventListener('input', ()=> localStorage.setItem(LS_KEYS.in.note, inNote.value));
      if(outView) outView.addEventListener('input', ()=> localStorage.setItem(LS_KEYS.out.amount, String(clean(outView.value))));
      if(outRef)  outRef.addEventListener('input',  ()=> localStorage.setItem(LS_KEYS.out.ref, outRef.value));
      if(outNote) outNote.addEventListener('input', ()=> localStorage.setItem(LS_KEYS.out.note, outNote.value));

      // On submit clear only the submitted side so the other stays
      inForm?.addEventListener('submit', ()=>{
        localStorage.removeItem(LS_KEYS.in.amount);
        localStorage.removeItem(LS_KEYS.in.ref);
        localStorage.removeItem(LS_KEYS.in.note);
      });
      outForm?.addEventListener('submit', ()=>{
        localStorage.removeItem(LS_KEYS.out.amount);
        localStorage.removeItem(LS_KEYS.out.ref);
        localStorage.removeItem(LS_KEYS.out.note);
      });
    }

    restore();
    wire();
  })();

  // ===== Simpan Keduanya (AJAX ke 2 endpoint) =====
  (function(){
    const $ = (s, ctx=document) => ctx.querySelector(s);
    const inForm = $('#cashInForm');
    const outForm= $('#cashOutForm');
    const btn = $('#saveBothBtn');
    if(!btn || !inForm || !outForm) return; // only when both forms exist

    const inView = $('#cashInAmountView');
    const inHid  = $('#cashInAmount');
    const inRef  = inForm.querySelector('input[name="reference"]');
    const inNote = inForm.querySelector('input[name="note"]');

    const outView= $('#cashOutAmountView');
    const outHid = $('#cashOutAmount');
    const outRef = outForm.querySelector('input[name="reference"]');
    const outNote= outForm.querySelector('input[name="note"]');

    async function post(url, data){
      const tokenInput = document.querySelector('input[name="_token"]');
      const token = tokenInput ? tokenInput.value : '';
      const body = new URLSearchParams({ ...data, _token: token });
      return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      });
    }

    btn.addEventListener('click', async ()=>{
      const orig = btn.innerHTML;
      const setLoading = (on)=>{
        btn.disabled = on;
        btn.innerHTML = on ? '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...' : orig;
      };

      const inAmt  = Number(inHid?.value || clean(inView?.value || 0)) || 0;
      const outAmt = Number(outHid?.value || clean(outView?.value || 0)) || 0;

      if(inAmt<=0 && outAmt<=0){
        alert('Isi nominal Kas Masuk atau Kas Keluar terlebih dahulu.');
        return;
      }

      setLoading(true);
      try{
        if(inAmt>0){
          const r = await post(inForm.action, { amount: inAmt, reference: inRef?.value || '', note: inNote?.value || '' });
          if(!r.ok) throw new Error('Gagal menyimpan kas masuk');
        }
        if(outAmt>0){
          const r = await post(outForm.action, { amount: outAmt, reference: outRef?.value || '', note: outNote?.value || '' });
          if(!r.ok) throw new Error('Gagal menyimpan kas keluar');
        }
        // Clear both caches then reload
        ['shift.cashIn.amount','shift.cashIn.reference','shift.cashIn.note','shift.cashOut.amount','shift.cashOut.reference','shift.cashOut.note']
          .forEach(k=> localStorage.removeItem(k));
        location.reload();
      }catch(e){
        console.error(e);
        alert((e && e.message) ? e.message : 'Gagal menyimpan. Coba simpan terpisah atau periksa koneksi.');
        setLoading(false);
      }
    });
  })();

  // ===== Toast Tips + FAB logic (with WAAPI smooth animations) =====
  (function(){
    const toast = document.getElementById('tipsToast');
    const helpBtn = document.getElementById('helpToastBtn');
    if(!toast) return;
    const progress = document.getElementById('tipsProgress');
    const card = toast.querySelector('.toast-card');
    const closeBtns = toast.querySelectorAll('[data-close-toast]');
    let timer=null, start=null, remaining=7000; // ms
    let running=false;
    const LS_TIPS_SEEN = 'shift.tips.seen';

    const inKeyframes = [
      { opacity: 0, transform: 'translateY(12px) scale(.98)', filter:'blur(2px)' },
      { opacity: 1, transform: 'translateY(0) scale(1)',     filter:'blur(0)' }
    ];
    const outKeyframes = [
      { opacity: 1, transform: 'translateY(0) scale(1)',     filter:'blur(0)' },
      { opacity: 0, transform: 'translateY(10px) scale(.98)', filter:'blur(2px)' }
    ];
    const inOpts  = { duration: 360, easing: getComputedStyle(document.documentElement).getPropertyValue('--ease-out').trim() || 'cubic-bezier(.22,1,.36,1)' };
    const outOpts = { duration: 300, easing: getComputedStyle(document.documentElement).getPropertyValue('--ease-in').trim()  || 'cubic-bezier(.4,0,1,1)' };

    function animate(ts){
      if(!start) start = ts;
      const elapsed = ts - start;
      const ratio = Math.max(0, 1 - (elapsed/remaining));
      if(progress) progress.style.transform = `scaleX(${ratio})`;
      if(elapsed >= remaining){ hide(); return; }
      timer = requestAnimationFrame(animate);
    }

    function show(){
      toast.classList.add('show');
      helpBtn?.classList.add('is-hidden');
      card?.animate(inKeyframes, inOpts);
      running=true; start=null;
      timer = requestAnimationFrame(animate);
    }
    function hide(){
      // Animasi keluar baru kemudian benar-benar disembunyikan
      const finisher = card?.animate(outKeyframes, outOpts);
      if (finisher && finisher.finished) {
        finisher.finished.then(() => finalizeHide());
      } else {
        finalizeHide();
      }
    }
    function finalizeHide(){
      toast.classList.remove('show');
      if(timer) cancelAnimationFrame(timer);
      running=false;
      if(progress) progress.style.transform = `scaleX(1)`;
      remaining = 7000; start=null;
      helpBtn?.classList.remove('is-hidden');
      try{ localStorage.setItem(LS_TIPS_SEEN,'1'); }catch(e){}
    }

    function pause(){
      if(!running) return;
      if(timer) cancelAnimationFrame(timer);
      try{
        const style = getComputedStyle(progress);
        const Matrix = window.DOMMatrix || window.WebKitCSSMatrix;
        const matrix = new Matrix(style.transform);
        const scaleX = matrix.a || 1;
        remaining = Math.max(300, remaining * scaleX);
      }catch(e){}
      running=false; start=null;
    }
    function resume(){
      if(running) return;
      running=true; start=null;
      timer = requestAnimationFrame(animate);
    }

    window.__tipsToastShow = show;
    closeBtns.forEach(b=>b.addEventListener('click', hide));
    toast.addEventListener('mouseenter', pause);
    toast.addEventListener('mouseleave', resume);

    window.addEventListener('DOMContentLoaded', ()=> {
      let seen=false; try{ seen = localStorage.getItem(LS_TIPS_SEEN)==='1'; }catch(e){}
      if(!seen){
        setTimeout(()=>{ show(); try{ localStorage.setItem(LS_TIPS_SEEN,'1'); }catch(e){} }, 400);
      }
    });
    helpBtn?.addEventListener('click', ()=> show());
  })();

  // ===== Sticky header shadow on scroll =====
  (function(){
    const wrap = document.getElementById('recWrap');
    if(!wrap) return;
    const thead = wrap.querySelector('thead');
    function toggleShadow(){
      if(!thead) return;
      thead.style.boxShadow = wrap.scrollTop>0 ? '0 8px 12px -8px rgba(2,6,23,.12)' : 'none';
    }
    wrap.addEventListener('scroll', toggleShadow);
    toggleShadow();
  })();
</script>
@endpush
