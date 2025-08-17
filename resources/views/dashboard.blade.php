@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    // Helper pemilih nilai fleksibel: ambil kunci pertama yang tersedia
    $pick = function($row, $keys, $default = '—') {
        foreach ($keys as $k) {
            $v = data_get($row, $k);
            if (!is_null($v) && $v !== '') {
                return $v;
            }
        }
        return $default;
    };

    // Normalisasi KPI harian & mingguan (nilai uang/angka jika ada)
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

    // Normalisasi Top Items untuk chart & tabel
    $top = collect($topItems ?? [])->map(function($row) use ($pick) {
        return [
            'label' => $pick($row, ['nama','name','title','kode']),
            'qty'   => (float) $pick($row, ['qty','jumlah','total_qty','count'], 0),
            'rev'   => (float) $pick($row, ['revenue','pendapatan','total_harga','total'], 0),
        ];
    })->filter(fn($r) => $r['label'] !== '—')->values()->take(10);

    // Normalisasi stok kritis
    $stokKritisRows = collect($stokKritis ?? [])->map(function($row) use ($pick){
        return [
            'label' => $pick($row, ['nama','name','title','kode']),
            'stok'  => $pick($row, ['stok','stock','sisa','remaining','qty'] , '0'),
            'unit'  => $pick($row, ['satuan','unit','units.0.pivot.unit','units.0.kode','units.0.name']),
        ];
    })->filter(fn($r) => $r['label'] !== '—')->values();
@endphp

<div class="container-fluid py-3">
  <!-- Header: Judul + Aksi -->
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h1 class="h4 fw-semibold mb-1">Dashboard</h1>
      <div class="text-secondary small">Ringkasan penjualan & persediaan</div>
    </div>
    <div class="d-flex gap-2">
      @if(function_exists('route') && Route::has('dashboard.pdf'))
      <a href="{{ route('dashboard.pdf') }}" class="btn btn-outline-secondary btn-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        <span class="ms-1">PDF</span>
      </a>
      @endif
      @if(function_exists('route') && Route::has('dashboard.excel'))
      <a href="{{ route('dashboard.excel') }}" class="btn btn-outline-secondary btn-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/></svg>
        <span class="ms-1">Excel</span>
      </a>
      @endif
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card">
        <div class="kpi-icon bg-primary-subtle text-primary">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Total Harian</div>
          <div class="kpi-value">@if(is_numeric($harian_total)) Rp{{ number_format($harian_total,0,',','.') }} @else — @endif</div>
          <div class="kpi-sub">@if(is_numeric($harian_count)) {{ (int)$harian_count }} transaksi @else &nbsp; @endif</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card">
        <div class="kpi-icon bg-success-subtle text-success">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Total Mingguan</div>
          <div class="kpi-value">@if(is_numeric($mingguan_total)) Rp{{ number_format($mingguan_total,0,',','.') }} @else — @endif</div>
          <div class="kpi-sub">@if(is_numeric($mingguan_count)) {{ (int)$mingguan_count }} transaksi @else &nbsp; @endif</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card">
        <div class="kpi-icon bg-info-subtle text-info">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 5 17 10"/><line x1="12" y1="5" x2="12" y2="15"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Item Terlaris</div>
          <div class="kpi-value">{{ $top->first()['label'] ?? '—' }}</div>
          <div class="kpi-sub">Qty: {{ isset($top[0]) ? (int)($top[0]['qty'] ?? 0) : 0 }}</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
      <div class="kpi-card">
        <div class="kpi-icon bg-warning-subtle text-warning">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Stok Kritis</div>
          <div class="kpi-value">{{ $stokKritisRows->count() }}</div>
          <div class="kpi-sub">Perlu restock</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Chart: Top 10 Terlaris -->
    <div class="col-12 col-xl-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
          <h2 class="h6 mb-0">Top 10 Item Terlaris</h2>
        </div>
        <div class="card-body">
          @if($top->isEmpty())
            <div class="text-secondary small">Belum ada data.</div>
          @else
            <canvas id="chartTopItems" height="220"
              data-labels='@json($top->pluck("label"))'
              data-values='@json($top->pluck("qty"))'></canvas>
          @endif
        </div>
        @if(!$top->isEmpty())
        <div class="card-footer bg-transparent">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
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
                  <td>{{ $i+1 }}</td>
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

    <!-- Tabel: Stok Kritis -->
    <div class="col-12 col-xl-6">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
          <h2 class="h6 mb-0">Stok Kritis</h2>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Nama</th>
                  <th class="text-end">Stok</th>
                  <th class="text-end">Satuan</th>
                </tr>
              </thead>
              <tbody>
                @forelse($stokKritisRows as $row)
                  <tr class="table-warning-subtle">
                    <td class="text-truncate" style="max-width: 260px">{{ $row['label'] }}</td>
                    <td class="text-end">{{ is_numeric($row['stok']) ? number_format($row['stok'],0,',','.') : $row['stok'] }}</td>
                    <td class="text-end">{{ $row['unit'] }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="text-secondary small">Tidak ada item kritis.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Styles: subtle, modern, dark-mode aware -->
<style>
  :root{
    --card-radius: 1rem;
  }
  .kpi-card{
    position: relative;
    display: flex; gap: .75rem; align-items: center;
    border: 1px solid rgba(0,0,0,.06);
    border-radius: var(--card-radius);
    padding: .9rem 1rem;
    background: var(--bs-body-bg);
    box-shadow: 0 .25rem .6rem rgba(0,0,0,.04);
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .kpi-card:hover{ transform: translateY(-2px); box-shadow: 0 .5rem 1.2rem rgba(0,0,0,.06); }
  .kpi-icon{ width: 44px; height: 44px; display:grid; place-items:center; border-radius: .9rem; }
  .kpi-body .kpi-label{ font-size: .8rem; color: var(--bs-secondary-color); margin-bottom: .15rem; }
  .kpi-body .kpi-value{ font-weight: 700; font-size: 1.15rem; letter-spacing: .2px; }
  .kpi-body .kpi-sub{ font-size: .75rem; color: var(--bs-secondary-color); }

  /* table subtle row highlight */
  .table-warning-subtle{ background: rgba(255,193,7,.08); }

  /* card */
  .card{ border-radius: var(--card-radius); }
  .card-header{ border-bottom: 1px solid rgba(0,0,0,.06); }

  @media (prefers-color-scheme: dark){
    .kpi-card{ border-color: rgba(255,255,255,.06); box-shadow: 0 .25rem .6rem rgba(0,0,0,.25); }
    .card-header{ border-bottom-color: rgba(255,255,255,.08); }
  }
</style>

<!-- Chart.js via CDN (defer) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const el = document.getElementById('chartTopItems');
    if(!el) return;

    // Ambil data asli
    const labelsFull = JSON.parse(el.getAttribute('data-labels') || '[]');
    const values = JSON.parse(el.getAttribute('data-values') || '[]');

    // Potong label panjang biar nggak numpuk, tampilkan full di tooltip
    const shorten = (s) => (typeof s === 'string' && s.length > 18) ? s.slice(0, 18) + '…' : s;
    const labelsShort = labelsFull.map(shorten);

    // Tinggi chart menyesuaikan jumlah bar (biar rapi untuk 8–10 item)
    el.height = Math.max(220, labelsShort.length * 28 + 30);

    const ctx = el.getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labelsShort,
        datasets: [{
          label: 'Qty',
          data: values,
          borderWidth: 1,
          barThickness: 16,
          categoryPercentage: 0.8,
          barPercentage: 0.9,
        }]
      },
      options: {
        indexAxis: 'y', // horizontal bar agar label mudah dibaca
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { top: 4, right: 8, bottom: 4, left: 4 } },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              // Tampilkan label lengkap di judul tooltip
              title: (ctx) => labelsFull[ctx[0].dataIndex] || '',
              label: (ctx) => 'Qty: ' + (ctx.formattedValue || '0'),
            }
          },
        },
        scales: {
          y: { ticks: { autoSkip: false, maxRotation: 0, minRotation: 0 } },
          x: { beginAtZero: true, ticks: { precision: 0 } },
        }
      }
    });
  });
</script>
@endsection
