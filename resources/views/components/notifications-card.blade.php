@props(['notifications' => collect()])

<div class="card mb-3" id="alerts-card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>Notifikasi</strong>
    <span class="badge bg-primary">{{ $notifications->count() }}</span>
  </div>
  <div class="card-body p-0">
    <ul class="list-group list-group-flush">
      @forelse($notifications as $n)
        @php $d = $n->data; @endphp
        <li class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold">{{ $d['title'] ?? $n->type }}</div>
            <small class="text-muted">
              @if(($d['type'] ?? '')==='stock_low')
                {{ $d['barang'] ?? '—' }} ({{ $d['unit'] ?? '—' }}) — sisa {{ $d['stok'] ?? 0 }}
              @elseif(($d['type'] ?? '')==='cash_diff')
                Shift #{{ $d['shift_id'] ?? '—' }} — selisih Rp {{ number_format((int)($d['difference'] ?? 0),0,',','.') }}
              @elseif(($d['type'] ?? '')==='below_cost')
                {{ $d['barang'] ?? '—' }} ({{ $d['unit'] ?? '—' }}) — Harga < HPP
              @elseif(($d['type'] ?? '')==='void_burst')
                {{ $d['count'] ?? 0 }} void hari ini
              @elseif(($d['type'] ?? '')==='daily_summary')
                Ringkasan penjualan {{ $d['data']['date'] ?? '' }}
              @endif
            </small>
          </div>
          <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
        </li>
      @empty
        <li class="list-group-item">Tidak ada notifikasi.</li>
      @endforelse
    </ul>
  </div>
</div>
