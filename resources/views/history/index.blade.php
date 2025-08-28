@extends('layouts.app')

@section('title', 'History Transaksi')

@section('content')
@include('partials.neo-theme')
<style>
  :root{
    --bg:#f6f8fb; --card:#ffffff; --ink:#0f172a; --muted:#64748b;
    --brand:#1D4ED8; /* aksen biru lembut, selaras sidebar kamu */
    --brand-ink:#0b3aa7;
    --ok:#16a34a; --warn:#f59e0b; --danger:#dc2626; --soft-danger:#fca5a5;
    --chip:#eef2ff; --chip-ink:#1d4ed8; --chip-border:#c7d2fe;
    --border:#e5e7eb; --hover:#f9fafb; --glow:0 12px 28px rgba(29,78,216,.12);
  }

  body{background:var(--bg); color:var(--ink)}
  .card{border:1px solid rgba(2,6,23,.06); border-radius:16px; box-shadow:var(--glow)}
  .card-header{
    border-bottom:1px solid var(--border);
    background:linear-gradient(135deg,#f7f9ff,#eef2ff);
    border-radius:16px 16px 0 0;
    padding:14px 16px;
  }

  /* ===== Top actions ===== */
  .btn-pos{display:inline-flex;align-items:center;gap:10px;border-radius:12px;padding:.5rem .8rem}
  .btn-pos .dot{width:8px;height:8px;border-radius:50%;background:var(--ok);box-shadow:0 0 0 4px rgba(22,163,74,.15)}

  /* ===== Quick groups as Chips ===== */
  .chips{display:flex;flex-wrap:wrap;gap:8px}
  .chip{
    --_bg:var(--chip); --_ink:var(--chip-ink); --_bd:var(--chip-border);
    display:inline-flex;align-items:center;gap:8px;
    padding:.4rem .7rem;border-radius:999px;border:1px solid var(--_bd);
    background:var(--_bg); color:var(--_ink); font-weight:600; font-size:.84rem;
    transition:.15s ease-in-out; text-decoration:none;
  }
  .chip .badge{background:#fff;border:1px solid var(--_bd); color:var(--_ink)}
  .chip:hover{filter:brightness(.98); transform:translateY(-1px)}
  .chip.is-active{
    --_bg:#1d4ed8; --_ink:#fff; --_bd:#1d4ed8;
  }
  .chip.is-active .badge{background:#2c5fe0;border-color:#2c5fe0;color:#fff}

  /* ===== Filter bar ===== */
  .filter-wrap{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:.6rem .6rem}
  .filter-wrap .form-select{border-radius:10px}

  /* ===== Table modern ===== */
  .table-modern{--row:rgba(2,6,23,.03)}
  .table-modern thead th{
    position:sticky; top:0; z-index:1;
    background:#fff; border-bottom:1px solid var(--border);
    font-size:.82rem; letter-spacing:.02em; color:#475569;
  }
  .table-modern tbody tr{transition:.15s ease}
  .table-modern tbody tr:hover{background:var(--row)}
  .table-modern td, .table-modern th{vertical-align:middle}
  .table-modern .code{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace}

  /* ===== Badges ===== */
  .b-status{font-weight:700}
  .b-status.draft{background:#e5e7eb;color:#111827}
  .b-status.posted{background:#dcfce7;color:#065f46}
  .b-status.void{background:#fee2e2;color:#991b1b}
  .b-pay{font-weight:700}
  .b-pay.unpaid{background:#e5e7eb;color:#334155}
  .b-pay.partial{background:#fef3c7;color:#92400e}
  .b-pay.paid{background:#dcfce7;color:#166534}

  /* ===== Action buttons ===== */
  .action-bar{display:flex;flex-wrap:wrap;gap:6px}
  .btn-soft{
    border-radius:10px; border:1px solid var(--border); background:#fff; color:#0f172a;
    padding:.35rem .6rem; font-weight:600; font-size:.82rem;
  }
  .btn-soft:hover{background:var(--hover)}
  .btn-soft.primary{border-color:#c7d2fe}
  .btn-soft.success{border-color:#bbf7d0}
  .btn-soft.warn{border-color:#fde68a}
  .btn-soft.danger{border-color:#fecaca; color:#991b1b}
  .btn-soft.outline{background:transparent}

  /* ===== Empty state ===== */
  .empty{
    border:1px dashed var(--border); border-radius:14px; padding:22px; text-align:center; color:var(--muted);
    background:linear-gradient(180deg,#fff,rgba(255,255,255,.6));
  }

  /* Pagination spacing */
  .card-footer{background:#fff;border-top:1px solid var(--border);border-radius:0 0 16px 16px}
</style>

<div class="card shadow-sm">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center gap-2">
      <div>
        <h5 class="mb-0">History Transaksi</h5>
        <div class="small text-muted">Lihat, filter, dan kelola transaksi—ringkas & jelas.</div>
      </div>
      <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm btn-pos">
        <span class="dot"></span> Buka POS
      </a>
    </div>
  </div>

  <div class="card-body">
    @php
      $statusId = fn($s) => match($s){
        'draft'  => 'Draf',
        'posted' => 'Diposting',
        'void'   => 'Dibatalkan',
        default  => ucfirst((string)$s),
      };
      $payStatus = fn($s) => match($s){
        'paid'    => 'Lunas',
        'partial' => 'Sebagian (parsial)',
        'unpaid'  => 'Belum dibayar',
        default   => ucfirst((string)$s),
      };
    @endphp

    {{-- === Quick Groups (Chips) === --}}
    <div class="mb-3 chips">
      @php
        $act = fn($g) => request()->fullUrlWithQuery(['group'=>$g,'status'=>null,'payment'=>null,'page'=>null]);
        $is  = fn($g) => (request('group')===$g);
      @endphp

      <a href="{{ $act(null) }}" class="chip {{ $is(null)?'is-active':'' }}">
        Semua
      </a>
      <a href="{{ $act('draft') }}" class="chip {{ $is('draft')?'is-active':'' }}">
        Draf <span class="badge rounded-pill">{{ (int)($byStatus['draft'] ?? 0) }}</span>
      </a>
      <a href="{{ $act('not_paid') }}" class="chip {{ $is('not_paid')?'is-active':'' }}">
        Belum Lunas <span class="badge rounded-pill">{{ (int)(($byPayment['unpaid']??0)+($byPayment['partial']??0)) }}</span>
      </a>
      <a href="{{ $act('posted') }}" class="chip {{ $is('posted')?'is-active':'' }}">
        Diposting <span class="badge rounded-pill">{{ (int)($byStatus['posted'] ?? 0) }}</span>
      </a>
      <a href="{{ $act('paid') }}" class="chip {{ $is('paid')?'is-active':'' }}">
        Lunas <span class="badge rounded-pill">{{ (int)($byPayment['paid'] ?? 0) }}</span>
      </a>
      <a href="{{ $act('void') }}" class="chip {{ $is('void')?'is-active':'' }}">
        Dibatalkan <span class="badge rounded-pill">{{ (int)($byStatus['void'] ?? 0) }}</span>
      </a>
    </div>

    {{-- === Filter ringkas === --}}
    <form class="row g-2 mb-3 filter-wrap" method="get">
      <div class="col-12 col-md-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">— status —</option>
          @foreach(['draft','posted','void'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s ? 'selected':'' }}>{{ $statusId($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-auto">
        <select name="payment" class="form-select form-select-sm">
          <option value="">— status bayar —</option>
          @foreach(['unpaid','partial','paid'] as $p)
            <option value="{{ $p }}" {{ request('payment')===$p ? 'selected':'' }}>{{ $payStatus($p) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-auto">
        <button class="btn btn-soft primary btn-sm">Terapkan</button>
        <a href="{{ route('history.index') }}" class="btn btn-soft outline btn-sm">Reset</a>
      </div>
    </form>

    {{-- === Table === --}}
    <div class="table-responsive">
      <table class="table table-hover table-modern align-middle mb-0">
        <thead>
          <tr>
            <th style="width:56px">#</th>
            <th>Kode</th>
            <th style="width:170px">Tanggal</th>
            <th style="width:120px">Status</th>
            <th style="width:120px">Status Bayar</th>
            <th style="width:140px" class="text-end">Total</th>
            <th style="width:320px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transaksis as $index => $t)
          @php
            $status = $t->status ?? 'posted';
            $paymentStatus = $t->payment_status ?? 'unpaid';
            $payClass = ['paid'=>'paid','partial'=>'partial','unpaid'=>'unpaid'][$paymentStatus] ?? 'unpaid';
            $sisa = max(0, (int)$t->total_harga - (int)$t->dibayar);
          @endphp
          <tr>
            <td>{{ $transaksis->firstItem() + $index }}</td>
            <td class="code">{{ $t->kode_transaksi }}</td>
            <td>{{ optional($t->created_at)->format('d M Y H:i') }}</td>
            <td>
              <span class="badge b-status {{ $status }}">{{ $statusId($status) }}</span>
            </td>
            <td>
              <span class="badge b-pay {{ $payClass }}">{{ $payStatus($paymentStatus) }}</span>
            </td>
            <td class="text-end">Rp{{ number_format((int)$t->total_harga, 0, ',', '.') }}</td>
            <td>
              <div class="action-bar">
                {{-- DETAIL / PDF --}}
                <a href="{{ route('history.show', $t->id) }}" class="btn-soft btn-sm">Detail</a>
                <a href="{{ route('history.pdf', $t->id) }}" class="btn-soft primary btn-sm" target="_blank">PDF</a>

                {{-- POSTING (hanya draft) --}}
                @if($status === 'draft')
                  <button class="btn-soft success btn-sm"
                          data-coreui-toggle="modal" data-coreui-target="#postModal"
                          data-id="{{ $t->id }}"
                          data-kode="{{ $t->kode_transaksi }}">
                    Posting
                  </button>
                @endif

                {{-- PAY (jika belum paid) --}}
                @if($paymentStatus !== 'paid' && $status !== 'void')
                  <button class="btn-soft primary btn-sm"
                          data-coreui-toggle="modal" data-coreui-target="#payModal"
                          data-id="{{ $t->id }}"
                          data-sisa="{{ $sisa }}"
                          data-kode="{{ $t->kode_transaksi }}">
                    Tambah Pembayaran
                  </button>
                @endif

                {{-- VOID (opsional, hanya posted & belum void) --}}
                @if($status === 'posted')
                  <button class="btn-soft danger btn-sm"
                          data-coreui-toggle="modal" data-coreui-target="#voidModal"
                          data-id="{{ $t->id }}" data-kode="{{ $t->kode_transaksi }}">
                    Batalkan
                  </button>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7">
              <div class="empty">
                <div class="fw-bold mb-1">Belum ada transaksi</div>
                <div class="small">Mulai dari halaman POS untuk membuat transaksi baru.</div>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer">
    {{ $transaksis->links() }}
  </div>
</div>

{{-- ======================= MODALS ======================= --}}

{{-- POST MODAL --}}
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="postForm">
      @csrf
      <div class="modal-content" style="border-radius:16px">
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" id="postTitle">Posting Transaksi</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small text-muted">TRX: <span id="postKode">-</span></div>
          <div class="alert alert-warning" style="border-radius:12px">
            Pilih mode posting:
            <ul class="mb-0 ps-3">
              <li><strong>Soft</strong>: tandai posted tanpa potong stok—aman untuk data lama.</li>
              <li><strong>Hard</strong>: hitung ulang total & potong stok untuk item <em>barang</em>.</li>
            </ul>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="mode" id="modeSoft" value="soft" checked>
            <label class="form-check-label" for="modeSoft">Soft (tanpa potong stok)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeHard" value="hard">
            <label class="form-check-label" for="modeHard">Hard (potong stok)</label>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button class="btn btn-success">Posting</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- PAY MODAL --}}
<div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="payForm">
      @csrf
      <div class="modal-content" style="border-radius:16px">
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" id="payTitle">Tambah Pembayaran</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small text-muted">TRX: <span id="payKode">-</span></div>
          <div class="mb-3">
            <label class="form-label">Nominal (Rp)</label>
            <input type="number" min="1" class="form-control" name="amount" id="payAmount">
            <div class="form-text">Sisa tagihan: <span id="paySisa">Rp0</span></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Metode</label>
            <select class="form-select" name="method">
              <option value="cash">Cash</option>
              <option value="transfer">Transfer</option>
              <option value="qris">QRIS</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Referensi (opsional)</label>
            <input type="text" class="form-control" name="reference" placeholder="No. transfer / QR ref">
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button class="btn btn-primary">Simpan Pembayaran</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- VOID MODAL --}}
<div class="modal fade" id="voidModal" tabindex="-1" aria-labelledby="voidTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="voidForm">
      @csrf
      <div class="modal-content" style="border-radius:16px">
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" id="voidTitle">Batalkan Transaksi</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small text-muted">TRX: <span id="voidKode">-</span></div>
          <label class="form-label">Alasan</label>
          <input type="text" class="form-control" name="reason" required>
          <div class="form-text text-danger">Stok barang akan dikembalikan.</div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button class="btn btn-outline-danger">Batalkan</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- === JS Modal Wiring === --}}
<script>
(function(){
  const currencyID = v => new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'})
                            .format(v).replace('IDR','Rp').trim();

  document.getElementById('postModal')?.addEventListener('show.coreui.modal', e=>{
    const btn = e.relatedTarget;
    const id  = btn?.dataset.id;
    const kode= btn?.dataset.kode;
    document.getElementById('postKode').textContent = kode || '-';
    document.getElementById('postForm').action = "{{ route('history.index') }}"+"/"+id+"/post";
  });

  document.getElementById('payModal')?.addEventListener('show.coreui.modal', e=>{
    const btn = e.relatedTarget;
    const id  = btn?.dataset.id;
    const kode= btn?.dataset.kode;
    const sisa= Number(btn?.dataset.sisa || 0);
    document.getElementById('payKode').textContent  = kode || '-';
    document.getElementById('paySisa').textContent  = currencyID(sisa);
    const inp = document.getElementById('payAmount');
    if(inp){ inp.value = sisa || ''; inp.focus(); }
    document.getElementById('payForm').action       = "{{ url('/pembayaran/pay') }}/"+id;
  });

  document.getElementById('voidModal')?.addEventListener('show.coreui.modal', e=>{
    const btn = e.relatedTarget;
    const id  = btn?.dataset.id;
    const kode= btn?.dataset.kode;
    document.getElementById('voidKode').textContent = kode || '-';
    document.getElementById('voidForm').action      = "{{ url('/pembayaran/void') }}/"+id;
  });
})();
</script>
@endsection
