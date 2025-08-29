@php
  $fmtRp = fn($n) => is_numeric($n) ? ('Rp. '.number_format((float)$n,0,',','.')) : '—';

  // Perbandingan periode (untuk card "Sekarang vs Periode Lalu")
  $adv    = $adv ?? [];
  $cmpLbl = ['Harian','Mingguan','Bulanan','Tahunan'];
  $cmpNow = [
    (int) data_get($adv,'day.total',0),
    (int) data_get($adv,'week.total',0),
    (int) data_get($adv,'month.total',0),
    (int) data_get($adv,'year.total',0),
  ];
  $cmpPrev = [
    (int) data_get($adv,'day.prev',0),
    (int) data_get($adv,'week.prev',0),
    (int) data_get($adv,'month.prev',0),
    (int) data_get($adv,'year.prev',0),
  ];

  // Top 10 (bulan ini + tahun ini semua)
  $mBarangNames = collect($topMonthBarang ?? [])->pluck('nama')->values();
  $mBarangQty   = collect($topMonthBarang ?? [])->pluck('qty')->map(fn($v)=>(int)$v)->values();
  $mJasaNames   = collect($topMonthJasa ?? [])->pluck('nama')->values();
  $mJasaQty     = collect($topMonthJasa ?? [])->pluck('qty')->map(fn($v)=>(int)$v)->values();
  $yAllNames    = collect($topYearAll ?? [])->pluck('nama')->values();
  $yAllQty      = collect($topYearAll ?? [])->pluck('qty')->map(fn($v)=>(int)$v)->values();

  // Streak
  $sDay   = $streaks['day']   ?? null;
  $sWeek  = $streaks['week']  ?? null;
  $sMonth = $streaks['month'] ?? null;
  $sYear  = $streaks['year']  ?? null;

  // Seri mingguan/bulanan/tahunan
  $seriesWeekday = $seriesWeekday ?? ['labels'=>[],'values'=>[]];
  $seriesMonth   = $seriesMonth   ?? ['labels'=>[],'values'=>[], 'year'=>now()->year];
  $seriesYear    = $seriesYear    ?? ['labels'=>[],'values'=>[], 'start'=>now()->year, 'end'=>now()->year+4, 'direction'=>'future'];
@endphp

<div class="container-fluid neo-main mt-2">

  {{-- ===== ROW 1: MINGGUAN vs PERBANDINGAN ===== --}}
  <div class="row g-4 pair-row">
    <div class="col-12 col-xxl-7">
      <div class="neo-card pair-card">
        <div class="neo-card__head">
          <h2 class="h6 m-0">Omset Minggu Ini (Senin–Minggu)</h2>
        </div>
        <div class="neo-card__body">
          @if(empty($seriesWeekday['labels']))
            <div class="muted">Belum ada data.</div>
          @else
            <canvas id="chartWeekday"
              height="260"
              data-labels='@json($seriesWeekday["labels"])'
              data-values='@json($seriesWeekday["values"])'></canvas>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-xxl-5">
      <div class="neo-card pair-card">
        <div class="neo-card__head">
          <h2 class="h6 m-0">Perbandingan Omset – Sekarang vs Periode Lalu</h2>
          <div class="muted small mt-1">Hari ↔ Kemarin • Minggu ↔ Minggu Lalu • Bulan ↔ Bulan Lalu • Tahun ↔ Tahun Lalu</div>
        </div>
        <div class="neo-card__body">
          @php $hasCmp = array_sum($cmpNow) + array_sum($cmpPrev) > 0; @endphp
          @if(!$hasCmp)
            <div class="muted">Belum ada data untuk ditampilkan.</div>
          @else
            <canvas id="chartCompareOmset"
              height="280"
              data-labels='@json($cmpLbl)'
              data-now='@json($cmpNow)'
              data-prev='@json($cmpPrev)'></canvas>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ===== ROW 2: BULANAN vs TAHUNAN ===== --}}
  <div class="row g-4 pair-row mt-1">
    <div class="col-12 col-xl-6">
      <div class="neo-card pair-card">
        <div class="neo-card__head">
          <h2 class="h6 m-0">Omset {{ $seriesMonth['year'] ?? now()->year }} (Jan–Des)</h2>
        </div>
        <div class="neo-card__body">
          @if(empty($seriesMonth['labels']))
            <div class="muted">Belum ada data.</div>
          @else
            <canvas id="chartMonth"
              height="280"
              data-labels='@json($seriesMonth["labels"])'
              data-values='@json($seriesMonth["values"])'></canvas>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="neo-card pair-card">
        <div class="neo-card__head">
          <h2 class="h6 m-0">
            Omset {{ $seriesYear['start'] ?? now()->year }}–{{ $seriesYear['end'] ?? now()->year+4 }}
          </h2>
          @if(($seriesYear['direction'] ?? 'future') === 'future')
            <div class="muted small mt-1">Otomatis mengikuti tahun berjalan ke depan.</div>
          @else
            <div class="muted small mt-1">Otomatis mengikuti tahun berjalan ke belakang.</div>
          @endif
        </div>
        <div class="neo-card__body">
          @if(empty($seriesYear['labels']))
            <div class="muted">Belum ada data.</div>
          @else
            <canvas id="chartYear"
              height="280"
              data-labels='@json($seriesYear["labels"])'
              data-values='@json($seriesYear["values"])'></canvas>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ===== ROW 3: TOP 10 BULAN INI (BARANG & JASA) ===== --}}
  <div class="row g-4 pair-row mt-1">
    <div class="col-12 col-xl-6">
      <div class="neo-card pair-card">
        <div class="neo-card__head"><h2 class="h6 m-0">Top 10 Bulan Ini – Barang</h2></div>
        <div class="neo-card__body">
          @if($mBarangNames->isEmpty())
            <div class="muted">Belum ada data.</div>
          @else
            <canvas id="chartTopMonthBarang"
              height="280"
              data-labels='@json($mBarangNames)'
              data-values='@json($mBarangQty)'></canvas>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="neo-card pair-card">
        <div class="neo-card__head"><h2 class="h6 m-0">Top 10 Bulan Ini – Jasa</h2></div>
        <div class="neo-card__body">
          @if($mJasaNames->isEmpty())
            <div class="muted">Belum ada data.</div>
          @else
            <canvas id="chartTopMonthJasa"
              height="280"
              data-labels='@json($mJasaNames)'
              data-values='@json($mJasaQty)'></canvas>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ===== ROW 4: STREAK TOP #1 (PENUH) ===== --}}
  <div class="row g-4 pair-row mt-1">
    <div class="col-12">
      <div class="neo-card">
        <div class="neo-card__head">
          <h2 class="h6 m-0">Streak Top #1 (Berdasarkan Omset)</h2>
        </div>
        <div class="neo-card__body">
          <div class="row g-3 kpi-strip">
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="neo-kpi h-100 w-100" style="min-height:92px">
                <div class="neo-kpi__icon text-brand">🔥</div>
                <div class="neo-kpi__body">
                  <div class="label">Harian</div>
                  <div class="value">{{ data_get($sDay,'nama','—') }}</div>
                  <div class="sub">{{ (int) data_get($sDay,'streak',0) }} hari berturut-turut</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="neo-kpi h-100 w-100" style="min-height:92px">
                <div class="neo-kpi__icon text-brand">🔥</div>
                <div class="neo-kpi__body">
                  <div class="label">Mingguan</div>
                  <div class="value">{{ data_get($sWeek,'nama','—') }}</div>
                  <div class="sub">{{ (int) data_get($sWeek,'streak',0) }} minggu berturut-turut</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="neo-kpi h-100 w-100" style="min-height:92px">
                <div class="neo-kpi__icon text-brand">🔥</div>
                <div class="neo-kpi__body">
                  <div class="label">Bulanan</div>
                  <div class="value">{{ data_get($sMonth,'nama','—') }}</div>
                  <div class="sub">{{ (int) data_get($sMonth,'streak',0) }} bulan berturut-turut</div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="neo-kpi h-100 w-100" style="min-height:92px">
                <div class="neo-kpi__icon text-brand">🔥</div>
                <div class="neo-kpi__body">
                  <div class="label">Tahunan</div>
                  <div class="value">{{ data_get($sYear,'nama','—') }}</div>
                  <div class="sub">{{ (int) data_get($sYear,'streak',0) }} tahun berturut-turut</div>
                </div>
              </div>
            </div>
          </div>
          <div class="muted small mt-2">*Streak dihitung mundur dari periode terbaru (urut omset).</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ================= SCRIPTS (Chart.js sudah di-include di halaman utama) ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (typeof Chart === 'undefined') return;

  // Register plugin tema sekali
  if (!window.__neoPluginsRegistered) {
    const themeArea = {
      id: 'themeArea',
      beforeDraw(chart) {
        const {ctx, chartArea} = chart; if (!chartArea) return;
        const {left, top, width, height} = chartArea;
        const g = ctx.createLinearGradient(0, top, 0, top + height);
        g.addColorStop(0, 'rgba(255,223,185,.20)');
        g.addColorStop(1, 'rgba(255,223,185,.06)');
        ctx.save(); ctx.fillStyle = g; ctx.fillRect(left, top, width, height); ctx.restore();
      }
    };
    const softShadow = {
      id: 'softShadow',
      beforeDatasetDraw(chart) {
        const {ctx} = chart; ctx.save();
        ctx.shadowColor = 'rgba(164,25,61,.25)';
        ctx.shadowBlur = 12; ctx.shadowOffsetY = 4;
      },
      afterDatasetDraw(chart) { chart.ctx.restore(); }
    };
    if (typeof ChartDataLabels !== 'undefined') { Chart.register(ChartDataLabels); }
    Chart.register(themeArea, softShadow);
    window.__neoPluginsRegistered = true;
  }

  const rupiah = (v) => new Intl.NumberFormat('id-ID').format(v);
  const makeGrad = (ctx, w, horizontal=false) => {
    const g = horizontal ? ctx.createLinearGradient(0, 0, w, 0) : ctx.createLinearGradient(0, 0, 0, w);
    g.addColorStop(0, '#A4193D'); g.addColorStop(1, '#7F0F29'); return g;
  };
  const shorten = (s) => (typeof s === 'string' && s.length > 22) ? s.slice(0, 22) + '…' : s;

  // === Perbandingan Omset (grouped bar)
  (function(){
    const el = document.getElementById('chartCompareOmset'); if (!el) return;
    const labels = JSON.parse(el.getAttribute('data-labels') || '[]');
    const now    = JSON.parse(el.getAttribute('data-now') || '[]');
    const prev   = JSON.parse(el.getAttribute('data-prev') || '[]');
    const ctx = el.getContext('2d');

    const gradNow  = makeGrad(ctx, el.height, true);
    const gradPrev = ctx.createLinearGradient(0, 0, el.height, 0);
    gradPrev.addColorStop(0, 'rgba(164,25,61,.18)');
    gradPrev.addColorStop(1, 'rgba(164,25,61,.10)');

    new Chart(ctx, {
      type: 'bar',
      data: { labels,
        datasets: [
          { label: 'Sekarang', data: now, backgroundColor: gradNow, borderRadius: 10, borderSkipped: false, maxBarThickness: 32 },
          { label: 'Periode Lalu', data: prev, backgroundColor: gradPrev, borderRadius: 10, borderSkipped: false, maxBarThickness: 32 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { labels: { color: 'rgba(2,6,23,.8)' } },
          tooltip: { backgroundColor: 'rgba(24,24,27,.92)', titleColor:'#fff', bodyColor:'#fff',
            callbacks: { label: (c)=> `${c.dataset.label}: Rp. ${rupiah(c.parsed.y ?? 0)}` } },
          datalabels: {
            color: '#7A1029', backgroundColor: 'rgba(255,223,185,.85)', borderRadius: 6, padding: {top:2,bottom:2,left:6,right:6},
            anchor:'end', align:'end', offset:6, formatter:(v)=> v ? 'Rp. '+rupiah(v) : ''
          }
        },
        scales: {
          x: { grid: { color: 'rgba(164,25,61,.12)', borderDash:[3,6] }, ticks: { color:'rgba(2,6,23,.7)'} },
          y: { beginAtZero:true, grid:{ color:'rgba(2,6,23,.08)'}, ticks:{ color:'rgba(2,6,23,.7)', callback:(v)=> 'Rp. '+rupiah(v)} }
        }
      }
    });
  })();

  // === Helper Horizontal Top 10
  function buildHorizontalTop10(canvasId){
    const el = document.getElementById(canvasId); if (!el) return;
    const labelsFull = JSON.parse(el.getAttribute('data-labels') || '[]');
    const values = JSON.parse(el.getAttribute('data-values') || '[]');
    const labelsShort = labelsFull.map(shorten);
    const ctx = el.getContext('2d');
    el.height = Math.max(260, labelsShort.length * 30 + 56);
    const grad = makeGrad(ctx, el.width, true);

    new Chart(ctx, {
      type: 'bar',
      data: { labels: labelsShort, datasets: [{ label: 'Qty', data: values, backgroundColor: grad, borderRadius: 10, borderSkipped: false, maxBarThickness: 24, categoryPercentage: 0.78, barPercentage: 0.9 }] },
      options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        layout: { padding: { top: 6, right: 12, bottom: 6, left: 6 } },
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor: 'rgba(24,24,27,.92)', titleColor:'#fff', bodyColor:'#fff',
            callbacks: { title: (c)=> labelsFull[c[0].dataIndex] || '', label: (c)=> 'Qty: ' + (c.parsed.x ?? 0) } },
          datalabels: {
            anchor:'end', align:'end', offset:6, clamp:true, borderRadius: 8,
            backgroundColor:'rgba(255,223,185,.85)', color:'#7A1029', font:{weight:700}, padding:{top:3,bottom:3,left:6,right:6},
            formatter: (v)=> new Intl.NumberFormat('id-ID').format(v || 0)
          }
        },
        scales: {
          y: { grid: { display:false }, ticks: { color:'rgba(2,6,23,.7)', autoSkip:false, maxRotation:0, minRotation:0 } },
          x: { beginAtZero:true, ticks:{ precision:0, color:'rgba(2,6,23,.7)' }, grid: { color:'rgba(164,25,61,.15)', lineWidth:1, borderDash:[3,6] } }
        }
      }
    });
  }

  // === Builder kolom Omset (vertical) untuk Mingguan/Bulanan/Tahunan
  function buildVerticalRevenue(canvasId){
    const el = document.getElementById(canvasId); if (!el) return;
    const labels = JSON.parse(el.getAttribute('data-labels') || '[]');
    const values = JSON.parse(el.getAttribute('data-values') || '[]');
    const ctx = el.getContext('2d');
    const grad = makeGrad(ctx, el.height, false);

    new Chart(ctx, {
      type: 'bar',
      data: { labels, datasets: [{ label: 'Omset', data: values, backgroundColor: grad, borderRadius: 10, borderSkipped: false, maxBarThickness: 36 }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: { backgroundColor:'rgba(24,24,27,.92)', titleColor:'#fff', bodyColor:'#fff',
            callbacks: { label: (c)=> 'Rp. '+rupiah(c.parsed.y ?? 0) } },
          datalabels: {
            color:'#7A1029', backgroundColor:'rgba(255,223,185,.85)', borderRadius:6, padding:{top:2,bottom:2,left:6,right:6},
            anchor:'end', align:'end', offset:6, formatter:(v)=> v ? 'Rp. '+rupiah(v) : ''
          }
        },
        scales: {
          x: { grid:{ color:'rgba(164,25,61,.12)', borderDash:[3,6] }, ticks:{ color:'rgba(2,6,23,.7)' } },
          y: { beginAtZero:true, grid:{ color:'rgba(2,6,23,.08)' }, ticks:{ color:'rgba(2,6,23,.7)', callback:(v)=> 'Rp. '+rupiah(v) } }
        }
      }
    });
  }

  // Render: Mingguan/Bulanan/Tahunan
  buildVerticalRevenue('chartWeekday');
  buildVerticalRevenue('chartMonth');
  buildVerticalRevenue('chartYear');

  // Render: Top 10
  buildHorizontalTop10('chartTopMonthBarang');
  buildHorizontalTop10('chartTopMonthJasa');
});
</script>
