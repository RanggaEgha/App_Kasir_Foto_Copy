@extends('layouts.app')

@section('title', 'History Transaksi')

@section('content')
@include('partials.neo-theme')

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

<style>
  :root{
    --font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans", "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji";
    --font-mono: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;

    --bg:#f6f8fb; --card:#ffffff; --ink:#0f172a; --muted:#64748b;
    --brand:#1D4ED8; --brand-ink:#0b3aa7;
    --ok:#16a34a; --warn:#f59e0b; --danger:#dc2626; --soft-danger:#fca5a5;
    --chip:#eef2ff; --chip-ink:#1d4ed8; --chip-border:#c7d2fe;
    --border:#e5e7eb; --hover:#f9fafb; --glow:0 12px 28px rgba(29,78,216,.12);
  }

  body{
    background:var(--bg); color:var(--ink);
    font-family:var(--font-sans);
    font-size:15px; line-height:1.55; letter-spacing:.01em;
    -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
  }
  h5{font-weight:700; letter-spacing:.005em; margin-bottom:.1rem}
  .small, small{color:var(--muted)}
  .num{ font-variant-numeric: tabular-nums; font-feature-settings:"tnum" 1; }

  .card{border:1px solid rgba(2,6,23,.06); border-radius:16px; box-shadow:var(--glow)}
  .card-header{
    border-bottom:1px solid var(--border);
    background:linear-gradient(135deg,#f7f9ff,#eef2ff);
    border-radius:16px 16px 0 0;
    padding:14px 16px;
  }

  .btn-pos{display:inline-flex;align-items:center;gap:10px;border-radius:12px;padding:.5rem .8rem;font-weight:600}
  .btn-pos .dot{width:8px;height:8px;border-radius:50%;background:var(--ok);box-shadow:0 0 0 4px rgba(22,163,74,.15)}

  .chips{display:flex;flex-wrap:wrap;gap:8px}
  .chip{
    --_bg:var(--chip); --_ink:var(--chip-ink); --_bd:var(--chip-border);
    display:inline-flex;align-items:center;gap:8px;
    padding:.44rem .72rem;border-radius:999px;border:1px solid var(--_bd);
    background:var(--_bg); color:var(--_ink); font-weight:600; font-size:.86rem;
    transition:.15s ease-in-out; text-decoration:none; line-height:1.25;
  }
  .chip .badge{background:#fff;border:1px solid var(--_bd); color:var(--_ink)}
  .chip:hover{filter:brightness(.98); transform:translateY(-1px)}
  .chip.is-active{ --_bg:#1d4ed8; --_ink:#fff; --_bd:#1d4ed8; }
  .chip.is-active .badge{background:#2c5fe0;border-color:#2c5fe0;color:#fff}

  .filter-wrap{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:.6rem .6rem}
  .filter-wrap .form-select{border-radius:10px; font-size:.9rem}

  .table-modern{--row:rgba(2,6,23,.03)}
  .table-modern thead th{
    position:sticky; top:0; z-index:1;
    background:#fff; border-bottom:1px solid var(--border);
    font-size:.8rem; letter-spacing:.02em; color:#475569; font-weight:700;
    padding-top:.7rem; padding-bottom:.6rem;
  }
  .table-modern td, .table-modern th{vertical-align:middle}
  .table-modern tbody td{font-size:.92rem}
  .table-modern tbody tr{transition:.15s ease}
  .table-modern tbody tr:hover{background:var(--row)}
  .table-modern .code{font-family:var(--font-mono); font-size:.9rem}

  .badge{font-weight:600; letter-spacing:.01em}
  .b-status{font-weight:700}
  .b-status.draft{background:#e5e7eb;color:#111827}
  .b-status.posted{background:#dcfce7;color:#065f46}
  .b-status.void{background:#fee2e2;color:#991b1b}
  .b-pay{font-weight:700}
  .b-pay.unpaid{background:#e5e7eb;color:#334155}
  .b-pay.partial{background:#fef3c7;color:#92400e}
  .b-pay.paid{background:#dcfce7;color:#166534}

  /* Tombol aksi */
  .btn-soft{
    border-radius:10px; border:1px solid var(--border); background:#fff; color:#0f172a;
    padding:.35rem .6rem; font-weight:600; font-size:.84rem;
    text-decoration:none;           /* hilangkan garis bawah */
  }
  .btn-soft:hover{background:var(--hover); text-decoration:none;} /* tetap tanpa underline */
  .btn-soft.primary{border-color:#c7d2fe}
  .btn-soft.success{border-color:#bbf7d0}
  .btn-soft.warn{border-color:#fde68a}
  .btn-soft.danger{border-color:#fecaca; color:#991b1b}
  .btn-soft.outline{background:transparent}

  .empty{
    border:1px dashed var(--border); border-radius:14px; padding:22px; text-align:center; color:var(--muted);
    background:linear-gradient(180deg,#fff,rgba(255,255,255,.6)); font-size:.95rem
  }
  .card-footer{background:#fff;border-top:1px solid var(--border);border-radius:0 0 16px 16px}
  /* Jarak nyaman antara header dan footer pada kartu history */
  .history-card{ margin: 16px 0 36px; }
  .history-card .card-header{ padding: 18px 18px 20px; margin-bottom: 8px; }
  .history-card .card-body{ min-height: clamp(260px, 36vh, 460px); }

  /* === Action Rotator (satu tombol tampil + panah, transisi halus arah sesuai) === */
  .action-rotator{display:flex;align-items:center;gap:8px}
  .action-rotator .nav{
    border-radius:12px; border:1px solid var(--border); background:#fff;
    padding:.35rem; font-weight:800; line-height:1; font-size:.9rem;
    user-select:none; display:inline-flex; align-items:center; justify-content:center;
    aspect-ratio:1/1; box-shadow:0 1px 0 rgba(2,6,23,.04);
  }
  .action-rotator .nav:hover{background:var(--hover)}
  .action-rotator .current{
    display:inline-flex; align-items:center; justify-content:center; min-width:0;
  }
  .action-rotator .current .btn-soft{
    white-space:nowrap;  /* cegah teks turun baris */
    display:inline-flex; align-items:center; justify-content:center;
  }
  .action-rotator .all-actions{display:none}

  /* Animasi arah */
  @keyframes inRight{from{opacity:0; transform:translateX(10px) scale(.98)} to{opacity:1; transform:translateX(0) scale(1)}}
  @keyframes outLeft{from{opacity:1; transform:translateX(0) scale(1)} to{opacity:0; transform:translateX(-10px) scale(.98)}}
  @keyframes inLeft{from{opacity:0; transform:translateX(-10px) scale(.98)} to{opacity:1; transform:translateX(0) scale(1)}}
  @keyframes outRight{from{opacity:1; transform:translateX(0) scale(1)} to{opacity:0; transform:translateX(10px) scale(.98)}}

  .anim-in-right{animation:inRight .18s ease forwards}
  .anim-out-left{animation:outLeft .16s ease forwards}
  .anim-in-left{animation:inLeft .18s ease forwards}
  .anim-out-right{animation:outRight .16s ease forwards}
</style>

<div class="card shadow-sm history-card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center gap-2">
      <div>
        <h5 class="mb-0">History Transaksi</h5>
        <div class="small text-muted">Lihat, filter, dan kelola transaksi ringkas & jelas.</div>
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

    {{-- Quick groups --}}
    <div class="mb-3 chips">
      @php
        $act = fn($g) => request()->fullUrlWithQuery(['group'=>$g,'status'=>null,'payment'=>null,'page'=>null]);
        $is  = fn($g) => (request('group')===$g);
      @endphp

      <a href="{{ $act(null) }}" class="chip {{ $is(null)?'is-active':'' }}">Semua</a>
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

    {{-- Filter --}}
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

    {{-- Tabel --}}
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
            <th style="width:220px">Ringkasan</th>
            <th style="width:320px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transaksis as $index => $t)
          @php
            $status = $t->status ?? 'posted';
            $paymentStatus = $t->payment_status ?? 'unpaid';
            $payClass = ['paid'=>'paid','partial'=>'partial','unpaid'=>'unpaid'][$paymentStatus] ?? 'unpaid';
            $paidInSum = (int) ($t->payments?->where('direction','in')->sum('amount') ?? 0);
            $refundSum = (int) ($t->payments?->where('direction','out')->sum('amount') ?? 0);
            $netPaid   = max(0, $paidInSum - $refundSum);
            $sisa = max(0, (int)$t->total_harga - $netPaid);
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
              @php $hasRefund = ($refundSum ?? 0) > 0; @endphp
              @if($hasRefund)
                <div class="mt-1">
                  @if(($netPaid ?? 0) <= 0)
                    <span class="badge text-bg-danger">Refund penuh</span>
                  @else
                    <span class="badge text-bg-warning">Refund sebagian</span>
                  @endif
                </div>
              @endif
            </td>
            <td class="text-end num">@rupiah((int)$t->total_harga)</td>
            <td>
              @php
                $refundSum = (int) ($t->payments?->where('direction','out')->sum('amount') ?? 0);
                $paidInSum = (int) ($t->payments?->where('direction','in')->sum('amount') ?? 0);
                $netPaid   = max(0, $paidInSum - $refundSum);
              @endphp
              <div class="d-flex flex-column gap-1 small">
                <span class="badge rounded-pill num" style="background:#eef2ff; color:#1d4ed8; border:1px solid #c7d2fe; width:fit-content;">Dibayar Bersih: @rupiah($netPaid)</span>
                @if($refundSum > 0)
                  <span class="badge rounded-pill num" style="background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; width:fit-content;">Terrefund: -@rupiah($refundSum)</span>
                @endif
                @if($sisa > 0)
                  <span class="badge rounded-pill num" style="background:#f1f5f9; color:#0f172a; border:1px solid #e2e8f0; width:fit-content;">Sisa: @rupiah($sisa)</span>
                @endif
              </div>
            </td>
            <td>
              {{-- Rotator aksi: satu tombol tampil + panah --}}
              <div class="action-rotator" data-default="detail">
                <button type="button" class="nav prev" aria-label="Sebelumnya">&lsaquo;</button>
                <div class="current"></div>
                <button type="button" class="nav next" aria-label="Berikutnya">&rsaquo;</button>

                <div class="all-actions">
                  <a href="{{ route('history.show', $t->id) }}" class="btn-soft btn-sm">Detail</a>
                  <a href="{{ route('history.pdf', $t->id) }}" class="btn-soft primary btn-sm" target="_blank">PDF</a>

                  @if($status === 'draft')
                    <button class="btn-soft success btn-sm"
                            data-coreui-toggle="modal" data-coreui-target="#postModal"
                            data-id="{{ $t->id }}"
                            data-kode="{{ $t->kode_transaksi }}">
                      Posting
                    </button>
                  @endif

                  @if($paymentStatus !== 'paid' && $status !== 'void')
                    <button class="btn-soft primary btn-sm"
                            data-coreui-toggle="modal" data-coreui-target="#payModal"
                            data-id="{{ $t->id }}"
                            data-sisa="{{ $sisa }}"
                            data-kode="{{ $t->kode_transaksi }}">
                      Tambah Pembayaran
                    </button>
                  @endif

                  @if($status !== 'void' && $netPaid > 0)
                    @php $maxRefund = (int) $t->dibayar; @endphp
                    <button class="btn-soft warn btn-sm"
                            data-coreui-toggle="modal" data-coreui-target="#refundModal"
                            data-id="{{ $t->id }}"
                            data-max="{{ $maxRefund }}"
                            data-kode="{{ $t->kode_transaksi }}">
                      Refund
                    </button>
                  @endif

                  @if($status === 'posted')
                    <button class="btn-soft danger btn-sm"
                            data-coreui-toggle="modal" data-coreui-target="#voidModal"
                            data-id="{{ $t->id }}" data-kode="{{ $t->kode_transaksi }}">
                      Batalkan
                    </button>
                  @endif
                </div>
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
    {{ $transaksis->withQueryString()->onEachSide(1)->links('components.pagination.pill-clean') }}
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
            <input type="text" inputmode="numeric" class="form-control money-input" name="amount" id="payAmount" placeholder="0">
            <div class="form-text">Sisa tagihan: <span id="paySisa">Rp0</span></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Metode</label>
            @php $shiftOpen = $shiftOpen ?? false; @endphp
            <select class="form-select" name="method">
              <option value="cash" {{ $shiftOpen ? '' : 'disabled' }}>Cash</option>
              <option value="transfer">Transfer</option>
              <option value="qris">QRIS</option>
            </select>
          </div>
          @if(!$shiftOpen)
            <div class="alert alert-warning d-flex justify-content-between align-items-center gap-2 py-2">
              <div>Shift belum dibuka. Metode <strong>Cash</strong> dinonaktifkan.</div>
              <a href="{{ route('shift.index') }}" class="btn btn-soft primary btn-sm">Buka Shift</a>
            </div>
          @endif
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

{{-- REFUND MODAL --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" id="refundForm">
      @csrf
      <div class="modal-content" style="border-radius:16px">
        <div class="modal-header" style="border-bottom:1px solid var(--border)">
          <h5 class="modal-title" id="refundTitle">Refund (Pengembalian Dana)</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small text-muted">TRX: <span id="refundKode">-</span></div>
          <div class="mb-3">
            <label class="form-label">Nominal (Rp)</label>
            <input type="text" inputmode="numeric" class="form-control money-input" name="amount" id="refundAmount" placeholder="0">
            <div class="form-text">Maksimal refund: <span id="refundMax">Rp0</span></div>
          </div>
          <div class="mb-3">
            <label class="form-label">Metode</label>
            @php $shiftOpen = $shiftOpen ?? false; @endphp
            <select class="form-select" name="method">
              <option value="cash" {{ $shiftOpen ? '' : 'disabled' }}>Cash</option>
              <option value="transfer">Transfer</option>
              <option value="qris">QRIS</option>
            </select>
          </div>
          @if(!$shiftOpen)
            <div class="alert alert-warning d-flex justify-content-between align-items-center gap-2 py-2">
              <div>Shift belum dibuka. Metode <strong>Cash</strong> dinonaktifkan.</div>
              <a href="{{ route('shift.index') }}" class="btn btn-soft primary btn-sm">Buka Shift</a>
            </div>
          @endif
          <div class="mb-3">
            <label class="form-label">Alasan Refund</label>
            <input type="text" class="form-control" name="reason" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Referensi (opsional)</label>
            <input type="text" class="form-control" name="reference" placeholder="No. transfer / catatan">
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border)">
          <button class="btn btn-warning">Simpan Refund</button>
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

{{-- === JS Modal Wiring + Action Rotator === --}}
<script>
(function(){
  const formatRibuan = (val) => {
    const n = Math.max(0, parseInt((val||'').toString().replace(/\D+/g,'')) || 0);
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n);
  };
  const currencyID = (v) => 'Rp' + formatRibuan(v);
  const normalizeDigits = (val) => ((val||'').toString().replace(/\D+/g,'') || '');
  const attachMoneyInput = (inp) => {
    if(!inp) return;
    inp.addEventListener('input', () => {
      const raw = normalizeDigits(inp.value);
      inp.value = formatRibuan(raw);
    });
    inp.form && inp.form.addEventListener('submit', () => {
      const raw = normalizeDigits(inp.value);
      inp.value = raw;
    });
  };

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
    if(inp){ inp.value = formatRibuan(sisa); inp.focus(); }
    document.getElementById('payForm').action       = "{{ url('/pembayaran/pay') }}/"+id;
  });

  document.getElementById('voidModal')?.addEventListener('show.coreui.modal', e=>{
    const btn = e.relatedTarget;
    const id  = btn?.dataset.id;
    const kode= btn?.dataset.kode;
    document.getElementById('voidKode').textContent = kode || '-';
    document.getElementById('voidForm').action      = "{{ url('/pembayaran/void') }}/"+id;
  });

  document.getElementById('refundModal')?.addEventListener('show.coreui.modal', e=>{
    const btn = e.relatedTarget;
    const id  = btn?.dataset.id;
    const kode= btn?.dataset.kode;
    const max = Number(btn?.dataset.max || 0);
    document.getElementById('refundKode').textContent = kode || '-';
    document.getElementById('refundMax').textContent  = currencyID(max);
    const inp = document.getElementById('refundAmount');
    if (inp) { inp.value = formatRibuan(max); inp.focus(); }
    document.getElementById('refundForm').action      = "{{ url('/pembayaran/refund') }}/"+id;
  });

  attachMoneyInput(document.getElementById('payAmount'));
  attachMoneyInput(document.getElementById('refundAmount'));

  // ==== Action Rotator (arah transisi sesuai panah) ====
  const initRotator = (rot) => {
    const slot = rot.querySelector('.current');
    const stash = rot.querySelector('.all-actions');
    const all  = Array.from(stash.children);
    const prev = rot.querySelector('.prev');
    const next = rot.querySelector('.next');
    if(all.length === 0){ rot.remove(); return; }

    // index default: prioritas "Detail"
    let idx = 0, busy = false;
    const want = (rot.dataset.default || 'detail').toLowerCase();
    const found = all.findIndex(n => (n.textContent || '').trim().toLowerCase().includes(want));
    if(found >= 0) idx = found;

    const showInitial = () => {
      slot.appendChild(all[idx]);
    };

    const swap = (dir) => {
      if(busy) return;
      busy = true;

      const oldNode = slot.firstChild;
      if(!oldNode){
        idx = (idx + (dir>0?1:-1) + all.length) % all.length;
        const newNodeOnly = all[idx];
        slot.appendChild(newNodeOnly);
        busy = false;
        return;
      }

      // animasikan keluar sesuai arah
      oldNode.classList.remove('anim-in-left','anim-in-right','anim-out-left','anim-out-right');
      oldNode.classList.add(dir>0 ? 'anim-out-left' : 'anim-out-right');

      oldNode.addEventListener('animationend', function handleOut(){
        oldNode.removeEventListener('animationend', handleOut);
        stash.appendChild(oldNode);

        idx = (idx + (dir>0?1:-1) + all.length) % all.length;
        const newNode = all[idx];
        slot.appendChild(newNode);

        // animasi masuk kebalikan arah
        newNode.classList.remove('anim-in-left','anim-in-right','anim-out-left','anim-out-right');
        newNode.classList.add(dir>0 ? 'anim-in-right' : 'anim-in-left');

        newNode.addEventListener('animationend', function handleIn(){
          newNode.removeEventListener('animationend', handleIn);
          newNode.classList.remove('anim-in-left','anim-in-right');
          busy = false;
        }, { once:true });
      }, { once:true });
    };

    showInitial();

    if(all.length <= 1){
      prev.style.display = 'none';
      next.style.display = 'none';
    }else{
      prev.addEventListener('click', ()=> swap(-1));
      next.addEventListener('click', ()=> swap(1));
    }
  };

  document.querySelectorAll('.action-rotator').forEach(initRotator);
})();
</script>
@endsection
