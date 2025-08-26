@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    // ================= NORMALIZER =================
    $pick = function($row, $keys, $default = '—') {
        foreach ($keys as $k) { $v = data_get($row, $k); if (!is_null($v) && $v !== '') return $v; }
        return $default;
    };

    $harian_total = is_array($harian ?? null) || $harian instanceof \ArrayAccess
        ? ($harian['total'] ?? $harian['pendapatan'] ?? $harian['grand_total'] ?? null)
        : ($harian ?? null);
    $harian_count = is_array($harian ?? null) || $harian instanceof \ArrayAccess
        ? ($harian['transaksi'] ?? $harian['count'] ?? $harian['jumlah'] ?? null)
        : null;

    $mingguan_total = is_array($mingguan ?? null) || $mingguan instanceof \ArrayAccess
        ? ($mingguan['total'] ?? $mingguan['pendapatan'] ?? $mingguan['grand_total'] ?? null)
        : ($mingguan ?? null);
    $mingguan_count = is_array($mingguan ?? null) || $mingguan instanceof \ArrayAccess
        ? ($mingguan['transaksi'] ?? $mingguan['count'] ?? $mingguan['jumlah'] ?? null)
        : null;

    // Top items → maks. 10 (viewport tabel 5 baris)
    $top = collect($topItems ?? [])->map(function($row) use ($pick) {
        return [
            'label' => $pick($row, ['nama','name','title','kode']),
            'qty'   => (float) $pick($row, ['qty','jumlah','total_qty','count'], 0),
            'rev'   => (float) $pick($row, ['omzet','revenue','pendapatan','total_harga','total'], 0),
        ];
    })->filter(fn($r) => $r['label'] !== '—')->values()->take(10);

    // Stok kritis per unit
    $stokKritisRows = collect($stokKritis ?? [])->flatMap(function($row) use ($pick){
        $nama  = $pick($row, ['nama','name','title','kode']);
        $units = collect(data_get($row, 'units', []));
        return $units->map(function($u) use ($nama){
            return [
                'label' => $nama,
                'stok'  => (int) data_get($u, 'pivot.stok', 0),
                'unit'  => data_get($u, 'kode', data_get($u, 'name', '—')),
            ];
        })->all();
    })->filter(fn($r)=> $r['unit'] !== '—')->values();

    $notifications = collect($notifications ?? []);
    $critTotal = $stokKritisRows->count();
@endphp

<div class="dash-neo">
  <!-- ===== HERO ===== -->
  <section class="neo-hero">
    <div class="neo-hero__bg"></div>
    <div class="neo-hero__content container-fluid">
      <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
          <div class="eyebrow">Ringkasan Toko</div>
          <h1 class="display-6 fw-700 m-0">Dashboard</h1>
          <div class="muted mt-1">Per {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          @if(function_exists('route') && Route::has('pembayaran.create'))
          <a href="{{ route('pembayaran.create') }}" class="btn-neo">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 5v14"/><path d="M5 12h14"/>
            </svg>
            <span class="ms-2">Transaksi Baru</span>
          </a>
          @endif

          @if(function_exists('route') && Route::has('dashboard.pdf'))
          <a href="{{ route('dashboard.pdf') }}" class="btn-ghost">
            {{-- Icon: File-Text --}}
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <path d="M14 2v6h6"/>
              <path d="M16 13H8"/><path d="M16 17H8"/>
            </svg>
            <span class="ms-2">PDF</span>
          </a>
          @endif

          @if(function_exists('route') && Route::has('dashboard.excel'))
          <a href="{{ route('dashboard.excel') }}" class="btn-ghost">
            {{-- Icon: File-Grid --}}
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <path d="M14 2v6h6"/>
              <path d="M8 12h8"/><path d="M8 16h8"/><path d="M12 10v10"/>
            </svg>
            <span class="ms-2">Excel</span>
          </a>
          @endif
        </div>
      </div>
    </div>
  </section>

  <div class="container-fluid neo-main">
    <!-- ===== KPI ===== -->
    <div class="row g-3 kpi-strip">
      <div class="col-12 col-sm-6 col-xl-3 d-flex">
        <div class="neo-card neo-kpi h-100 w-100">
          <div class="neo-kpi__icon text-brand">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
          </div>
          <div class="neo-kpi__body">
            <div class="label">Total Harian</div>
            <div class="value">@if(is_numeric($harian_total)) Rp{{ number_format($harian_total,0,',','.') }} @else — @endif</div>
            <div class="sub">@if(is_numeric($harian_count)) {{ (int)$harian_count }} transaksi @else &nbsp; @endif</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3 d-flex">
        <div class="neo-card neo-kpi h-100 w-100">
          <div class="neo-kpi__icon text-brand">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="neo-kpi__body">
            <div class="label">Total Mingguan</div>
            <div class="value">@if(is_numeric($mingguan_total)) Rp{{ number_format($mingguan_total,0,',','.') }} @else — @endif</div>
            <div class="sub">@if(is_numeric($mingguan_count)) {{ (int)$mingguan_count }} transaksi @else &nbsp; @endif</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3 d-flex">
        <div class="neo-card neo-kpi h-100 w-100">
          <div class="neo-kpi__icon text-brand">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 5 17 10"/><line x1="12" y1="5" x2="12" y2="15"/></svg>
          </div>
          <div class="neo-kpi__body">
            <div class="label">Item Terlaris</div>
            <div class="value">{{ $top->first()['label'] ?? '—' }}</div>
            <div class="sub">Qty: {{ (int)($top->first()['qty'] ?? 0) }}</div>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3 d-flex">
        <div class="neo-card neo-kpi h-100 w-100">
          <div class="neo-kpi__icon text-brand">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div class="neo-kpi__body">
            <div class="label">Stok Kritis</div>
            <div class="value">{{ $stokKritisRows->count() }}</div>
            <div class="sub">Perlu restock</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== ROW 1: CHART vs NOTIF ===== -->
    <div class="row g-4 pair-row mt-2">
      <div class="col-12 col-xl-7">
        <div class="neo-card pair-card">
          <div class="neo-card__head">
            <h2 class="h6 m-0">Top 10 Item Terlaris</h2>
          </div>
          <div class="neo-card__body">
            @if($top->isEmpty())
              <div class="muted">Belum ada data.</div>
            @else
              <canvas id="chartTopItems" height="260"
                data-labels='@json($top->pluck("label"))'
                data-values='@json($top->pluck("qty"))'></canvas>
            @endif
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-5">
        <div class="neo-card pair-card" id="notif-card">
          <div class="neo-card__head d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.73 21a2 2 0 0 1-3.46 0"/><path d="M21 19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2"/><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
              <h2 class="h6 m-0">Notifikasi</h2>
            </div>
            <span class="badge badge-peach">{{ $notifications->count() }}</span>
          </div>

          <div class="neo-card__body">
            @if($notifications->isEmpty())
              <div class="muted">Tidak ada notifikasi.</div>
            @else
              <div class="notif-list nice-scroll">
                @foreach($notifications as $n)
                  @php
                    $d = $n->data ?? [];
                    $type = $d['type'] ?? '';
                    $tone = match($type){
                      'stock_out'   => 'danger',
                      'stock_low'   => 'warn',
                      'cash_diff'   => 'info',
                      'below_cost'  => 'secondary',
                      'void_burst'  => 'dark',
                      'daily_summary'=> 'success',
                      default       => 'secondary'
                    };
                  @endphp
                  <div class="notif-item tone-{{ $tone }}">
                    <div class="icon">
                      @if($type==='stock_out')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                      @elseif($type==='stock_low')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                      @elseif($type==='cash_diff')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="8" cy="6" rx="5" ry="3"/><path d="M3 6v6c0 1.7 2.2 3 5 3s5-1.3 5-3V6"/><path d="M16 12c2.8 0 5-1.3 5-3s-2.2-3-5-3"/></svg>
                      @elseif($type==='below_cost')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 7 9 13 13 9 21 17"/><polyline points="21 10 21 17 14 17"/></svg>
                      @elseif($type==='void_burst')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>
                      @elseif($type==='daily_summary')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><polyline points="9 16 11.5 18.5 15 15"/></svg>
                      @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13.73 21a2 2 0 0 1-3.46 0"/><path d="M21 19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2"/><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
                      @endif
                    </div>
                    <div class="content">
                      <div class="title">
                        {{ $d['title'] ?? ucfirst(str_replace('_',' ', $type ?: ($n->type ?? 'Notifikasi'))) }}
                      </div>
                      <div class="desc">
                        @if($type==='stock_out')
                          {{ $d['barang'] ?? '—' }} ({{ $d['unit'] ?? '—' }}) — <strong>STOK HABIS</strong>
                        @elseif($type==='stock_low')
                          {{ $d['barang'] ?? '—' }} ({{ $d['unit'] ?? '—' }}) — sisa {{ (int)($d['stok'] ?? 0) }}
                        @elseif($type==='cash_diff')
                          Shift #{{ $d['shift_id'] ?? '—' }} — selisih Rp {{ number_format((int)($d['difference'] ?? 0),0,',','.') }}
                        @elseif($type==='below_cost')
                          {{ $d['barang'] ?? '—' }} ({{ $d['unit'] ?? '—' }}) — Harga < HPP (Rp {{ number_format((float)($d['hpp'] ?? 0),0,',','.') }})
                        @elseif($type==='void_burst')
                          {{ (int)($d['count'] ?? 0) }} void/refund hari ini
                        @elseif($type==='daily_summary')
                          Ringkasan penjualan {{ $d['data']['date'] ?? '' }}
                        @else
                          &nbsp;
                        @endif
                      </div>
                      <div class="meta">
                        <span class="time">{{ optional($n->created_at)->diffForHumans() }}</span>
                        @if(function_exists('route') && Route::has('notifications.read') && is_null($n->read_at))
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="d-inline ms-2">
                          @csrf
                          <button class="btn btn-xs btn-ghost">Tandai dibaca</button>
                        </form>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- ===== ROW 2: PENDAPATAN vs STOK KRITIS ===== -->
    <div class="row g-4 pair-row mt-1">
      <div class="col-12 col-xl-7">
        <div class="neo-card pair-card">
          <div class="neo-card__head">
            <h2 class="h6 m-0">Pendapatan (Top 10)</h2>
          </div>
          <div class="neo-card__body">
            @if($top->isEmpty())
              <div class="muted">Belum ada data.</div>
            @else
              <div class="table-shell table-shell--brand mt-2">
                <div class="table-responsive scroll-5rows">
                  <table class="table align-middle mb-0 table-fixed-rows table-sticky">
                    <thead class="table-head-soft">
                      <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Pendapatan</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($top as $i => $row)
                      <tr>
                        <td class="fw-700">{{ $i+1 }}</td>
                        <td class="text-truncate" style="max-width: 260px">{{ $row['label'] }}</td>
                        <td class="text-end">{{ number_format($row['qty'],0,',','.') }}</td>
                        <td class="text-end">@if($row['rev']>0) Rp{{ number_format($row['rev'],0,',','.') }} @else — @endif</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-12 col-xl-5">
        {{-- ====== STOK KRITIS (DESAIN BARU) ====== --}}
        <div class="neo-card pair-card" id="krit-card">
          <div class="neo-card__head d-flex justify-content-between align-items-center">
            <h2 class="h6 m-0">Stok Kritis</h2>
            <span class="badge badge-peach">{{ $critTotal }}</span>
          </div>
          <div class="neo-card__body">
            @if($stokKritisRows->isEmpty())
              <div class="muted">Tidak ada item kritis.</div>
            @else
              <div id="krit-list" class="crit-list nice-scroll">
                @foreach($stokKritisRows as $row)
                  @php $isZero = (int)$row['stok'] <= 0; @endphp
                  <div class="crit-row {{ $isZero ? 'is-zero' : 'is-low' }}">
                    <div class="title text-truncate" title="{{ $row['label'] }}">{{ $row['label'] }}</div>
                    <div class="tags">
                      <span class="tag">{{ $row['unit'] }}</span>
                      <span class="tag {{ $isZero ? 'tag-danger' : 'tag-warn' }}">
                        {{ $isZero ? 'Habis' : 'Sisa '.number_format((int)$row['stok'],0,',','.') }}
                      </span>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================= THEME: Crimson Red + Peach ================= -->
<style>
  :root{
    --bg: var(--bs-body-bg);
    --ink: var(--bs-body-color);
    --muted: var(--bs-secondary-color);

    --brand: #A4193D;   /* Crimson Red */
    --brand-2: #8C1433;
    --peach: #FFDFB9;   /* Peach */

    --thead-soft: #F6EEF2;

    --radius: 16px;
    --radius-md: 12px;
    --shadow: 0 10px 30px rgba(2, 6, 23, .08);
    --ring: 0 0 0 1px rgba(2,6,23,.06) inset;

    --kpi-h: 116px;

    --table-row-h: 56px;
    --crit-row-h: 56px;

    --scroll-thumb: #D9A4B3;
    --scroll-track: transparent;
  }

  .fw-700{font-weight:700}
  .muted{color:var(--muted)}
  .text-brand{ color: var(--brand) !important; }

  .neo-hero{ position: relative; padding: 28px 0 10px; overflow: clip; }
  .neo-hero__bg{
    position:absolute; inset:0; pointer-events:none;
    background:
      radial-gradient(60% 100% at 85% 0%, rgba(164,25,61,.16), transparent 60%),
      radial-gradient(50% 80% at 5% 10%, rgba(255,223,185,.40), transparent 60%),
      linear-gradient(180deg, rgba(164,25,61,.06), transparent 35%);
    filter: saturate(106%); opacity:.9;
  }
  .eyebrow{ text-transform: uppercase; letter-spacing:.12em; font-size:.72rem; color:var(--muted); }

  .neo-main{ margin-top: 6px; padding-bottom: 2.75rem; }

  .neo-card{
    background: var(--bg);
    border-radius: var(--radius);
    border: 1px solid rgba(2,6,23,.06);
    box-shadow: var(--shadow);
    overflow: hidden;
    animation: fadeUp .35s ease both;
    height: auto;
  }
  .neo-card__head{ padding: 1rem 1rem; border-bottom: 1px solid rgba(2,6,23,.06); }
  .neo-card__body{ padding: 1rem; }

  .pair-row .pair-card{ display:flex; flex-direction:column; height:100%; min-height: clamp(360px, 42vh, 520px); }
  @media (max-width: 1199.98px){ .pair-row .pair-card{ min-height: unset; } }

  .kpi-strip > [class*="col-"]{ display:flex; }
  .neo-kpi{ display:flex; gap:.9rem; align-items:center; padding:.9rem 1rem; min-height: var(--kpi-h); }
  .neo-kpi__icon{
    width: 48px; height: 48px; display:grid; place-items:center; border-radius:14px;
    background: linear-gradient(135deg, rgba(164,25,61,.10), rgba(164,25,61,.02));
    box-shadow: var(--ring); flex:0 0 48px;
  }
  .neo-kpi__body .label{ font-size:.8rem; color:var(--muted); margin:0;}
  .neo-kpi__body .value{ font-weight:800; font-size:1.2rem; letter-spacing:.2px;}
  .neo-kpi__body .sub{ font-size:.76rem; color:var(--muted); }

  .btn-neo{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.6rem .9rem; border-radius:.8rem;
    background: linear-gradient(135deg, var(--brand), var(--brand-2)) !important;
    color:#fff !important; border:1px solid rgba(164,25,61,.45) !important;
    box-shadow: 0 6px 18px rgba(164,25,61,.28);
    text-decoration:none; transition: transform .16s ease, box-shadow .16s ease, filter .16s ease, background .16s ease;
  }
  .btn-neo:hover{ filter:brightness(1.05); transform:translateY(-1px); box-shadow:0 10px 26px rgba(164,25,61,.36);}
  .btn-neo:active{ transform:translateY(0); filter:brightness(.98);}
  .btn-neo:focus-visible{ outline:3px solid rgba(164,25,61,.35); outline-offset:2px; }

  .btn-ghost{
    display:inline-flex; align-items:center; gap:.5rem;
    padding:.55rem .85rem; border-radius:.8rem; background:transparent !important;
    color:var(--ink) !important; border:1px solid rgba(2,6,23,.12) !important;
    text-decoration:none; transition:.16s;
  }
  .btn-ghost:hover{ background:rgba(255,223,185,.45) !important; border-color:rgba(164,25,61,.35) !important; color:var(--brand) !important; transform:translateY(-1px); }

  .table-shell{ background:var(--bg); border:1px solid rgba(2,6,23,.07); border-radius:12px; padding:.4rem; box-shadow:0 10px 26px rgba(164,25,61,.10); }
  .table-head-soft th{ background:#F6EEF2; border-bottom:1px solid rgba(164,25,61,.12); font-weight:700; }
  .table td, .table th{ padding:.9rem 1rem; }
  .table-fixed-rows tbody tr{ height: var(--table-row-h); }
  .table tbody tr:nth-of-type(odd){ background-color: rgba(2,6,23,.02); }

  .scroll-5rows{ position:relative; height:auto; overflow:visible; border-radius:10px; }
  .table-sticky{ width:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; }
  .table-sticky thead, .table-sticky tbody{ display:block; width:100%; }
  .table-sticky thead tr, .table-sticky tbody tr{ display:table; width:100%; table-layout:fixed; }
  .table-sticky tbody{ max-height: calc(5 * var(--table-row-h)); overflow:auto; scrollbar-gutter:stable; }
  .table-sticky thead th{ position:sticky; top:0; z-index:3; background:#F6EEF2 !important; border-bottom:1px solid rgba(164,25,61,.12); box-shadow:0 2px 0 rgba(2,6,23,.05); }

  /* NOTIFIKASI */
  .notif-list{ display:flex; flex-direction:column; gap:.75rem; max-height: 420px; overflow:auto; }
  .notif-item{ display:grid; grid-template-columns: 40px 1fr; gap:.75rem; padding:.75rem .9rem; border-radius:12px; background:var(--bg); border:1px solid rgba(2,6,23,.06); box-shadow:0 4px 14px rgba(2,6,23,.04); }
  .notif-item .icon{ width:40px; height:40px; border-radius:12px; display:grid; place-items:center; color:#73122B; background:rgba(164,25,61,.10); }
  .tone-warn .icon{ background: rgba(255,223,185,.70); color:#7a1029; }
  .tone-danger .icon{ background: rgba(220,53,69,.14); color:#b02a37; }
  .tone-info .icon{ background: rgba(13,202,240,.16); color:#087990; }
  .tone-success .icon{ background: rgba(25,135,84,.14); color:#0f5132; }
  .tone-secondary .icon{ background: rgba(108,117,125,.14); color:#495057; }
  .tone-dark .icon{ background: rgba(33,37,41,.14); color:#212529; }
  .notif-item .content .title{ font-weight:700; }
  .notif-item .content .desc{ color:var(--muted); }
  .notif-item .content .meta{ font-size:.78rem; color:var(--muted); margin-top:.2rem; }

  /* STOK KRITIS */
  .crit-list{ display:flex; flex-direction:column; gap:.75rem; height: calc(5 * var(--crit-row-h) + 4 * .75rem); overflow:auto; }
  .crit-row{ position:relative; display:flex; align-items:center; justify-content:space-between; gap:.9rem; padding:.75rem .9rem .75rem 1rem; min-height:var(--crit-row-h); border-radius:12px; background:var(--bg); border:1px solid rgba(2,6,23,.06); box-shadow:0 4px 14px rgba(2,6,23,.04); transition: transform .12s ease, box-shadow .12s ease; }
  .crit-row::before{ content:""; position:absolute; left:0; top:6px; bottom:6px; width:4px; border-radius:8px; background: var(--brand); opacity:.55; }
  .crit-row.is-zero::before{ background:#dc3545; opacity:.85; }
  .crit-row:hover{ transform:translateY(-1px); box-shadow:0 8px 22px rgba(2,6,23,.08); }
  .crit-row .title{ font-weight:600; flex:1; min-width:0; }
  .tags{ display:flex; gap:.4rem; align-items:center; }
  .tag{ display:inline-flex; align-items:center; padding:.25rem .55rem; font-size:.75rem; border-radius:999px; background:rgba(2,6,23,.05); border:1px solid rgba(2,6,23,.06); color:var(--ink); white-space:nowrap; }
  .tag-warn{ background:rgba(255,223,185,.65); border-color:rgba(164,25,61,.15); }
  .tag-danger{ background:rgba(220,53,69,.12); color:#b02a37; border-color:rgba(220,53,69,.28); }

  /* SCROLLBAR PINK */
  .dash-neo .nice-scroll,
  .dash-neo .table-sticky tbody,
  .dash-neo .notif-list{
    scrollbar-color: var(--scroll-thumb) var(--scroll-track);
    scrollbar-width: thin;
  }
  .dash-neo .nice-scroll::-webkit-scrollbar,
  .dash-neo .table-sticky tbody::-webkit-scrollbar,
  .dash-neo .notif-list::-webkit-scrollbar{ width:10px; height:10px; }
  .dash-neo .nice-scroll::-webkit-scrollbar-thumb,
  .dash-neo .table-sticky tbody::-webkit-scrollbar-thumb,
  .dash-neo .notif-list::-webkit-scrollbar-thumb{
    background-color: var(--scroll-thumb);
    border-radius:999px; border:3px solid transparent; background-clip:content-box;
  }
  .dash-neo .nice-scroll::-webkit-scrollbar-track,
  .dash-neo .table-sticky tbody::-webkit-scrollbar-track,
  .dash-neo .notif-list::-webkit-scrollbar-track{ background: var(--scroll-track); }

  @keyframes fadeUp { from { opacity:0; transform: translateY(6px);} to { opacity:1; transform:none; } }

  @media (prefers-color-scheme: dark){
    :root{
      --shadow: 0 12px 28px rgba(0,0,0,.35);
      --ring: 0 0 0 1px rgba(255,255,255,.10) inset;
      --thead-soft: #24161B;
      --scroll-thumb: #C98598;
    }
    .neo-card{ border-color: rgba(255,255,255,.08); }
    .neo-card__head{ border-color: rgba(255,255,255,.08); }
    .table-shell{ border-color: rgba(255,255,255,.10); box-shadow: 0 8px 22px rgba(0,0,0,.35); }
  }

  @media (max-width: 991.98px){ .neo-hero{ padding-top:22px; } .neo-card__body{ padding:.9rem; } }
  @media (max-width: 576px){
    :root{ --table-row-h:52px; --crit-row-h:52px; }
    .btn-neo, .btn-ghost{ padding:.5rem .75rem; }
  }
</style>

<!-- ================= Chart.js + Datalabels ================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('chartTopItems');
    if(!el) return;

    const labelsFull = JSON.parse(el.getAttribute('data-labels') || '[]');
    const values = JSON.parse(el.getAttribute('data-values') || '[]');

    const shorten = (s) => (typeof s === 'string' && s.length > 20) ? s.slice(0, 20) + '…' : s;
    const labelsShort = labelsFull.map(shorten);

    const ctx = el.getContext('2d');

    // Gradient bar crimson
    const grad = ctx.createLinearGradient(0, 0, el.width, 0);
    grad.addColorStop(0, '#A4193D');
    grad.addColorStop(1, '#7F0F29');

    // Plugin: background chartArea peachy
    const themeArea = {
      id: 'themeArea',
      beforeDraw(chart) {
        const {ctx, chartArea} = chart;
        if (!chartArea) return;
        const {left, top, width, height} = chartArea;
        const g = ctx.createLinearGradient(0, top, 0, top + height);
        g.addColorStop(0, 'rgba(255,223,185,.20)');
        g.addColorStop(1, 'rgba(255,223,185,.06)');
        ctx.save();
        ctx.fillStyle = g;
        ctx.fillRect(left, top, width, height);
        ctx.restore();
      }
    };

    // Plugin: soft shadow for bars
    const softShadow = {
      id: 'softShadow',
      beforeDatasetDraw(chart, args) {
        const {ctx} = chart;
        ctx.save();
        ctx.shadowColor = 'rgba(164,25,61,.25)';
        ctx.shadowBlur = 12;
        ctx.shadowOffsetY = 4;
      },
      afterDatasetDraw(chart, args) {
        chart.ctx.restore();
      }
    };

    Chart.register(ChartDataLabels, themeArea, softShadow);

    // Dinamis tinggi
    el.height = Math.max(260, labelsShort.length * 30 + 56);

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labelsShort,
        datasets: [{
          label: 'Qty',
          data: values,
          backgroundColor: grad,
          borderRadius: 10,
          borderSkipped: false,
          maxBarThickness: 24,
          categoryPercentage: 0.78,
          barPercentage: 0.9,
          datalabels: {
            anchor: 'end',
            align: 'end',
            offset: 6,
            clamp: true,
            borderRadius: 8,
            backgroundColor: 'rgba(255,223,185,.85)',
            color: '#7A1029',
            font: { weight: 700 },
            padding: {top: 3, bottom: 3, left: 6, right: 6},
            formatter: (v)=> new Intl.NumberFormat('id-ID').format(v)
          }
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { top: 6, right: 12, bottom: 6, left: 6 } },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(24,24,27,.92)',
            titleColor: '#fff',
            bodyColor: '#fff',
            padding: 10,
            callbacks: {
              title: (c)=> labelsFull[c[0].dataIndex] || '',
              label: (c)=> 'Qty: ' + (c.parsed.x ?? 0),
            }
          },
        },
        scales: {
          y: {
            grid: { display: false },
            ticks: { color: 'rgba(2,6,23,.7)', autoSkip: false, maxRotation: 0, minRotation: 0 }
          },
          x: {
            beginAtZero: true,
            ticks: { precision: 0, color: 'rgba(2,6,23,.7)' },
            grid: { color: 'rgba(164,25,61,.15)', lineWidth: 1, borderDash: [3, 6] }
          },
        }
      }
    });
  });
</script>
@include('partials.dashboard-advanced')
@endsection
