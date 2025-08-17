@extends('layouts.app')

@section('title', 'History Transaksi')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="mb-0">History Transaksi</h5>
      <a href="{{ route('pembayaran.create') }}" class="btn btn-primary btn-sm">Buka POS</a>
    </div>
  </div>

  <div class="card-body">

    {{-- === Quick Groups === --}}
    <div class="mb-3 d-flex flex-wrap gap-2">
      @php
        $act = fn($g) => request()->fullUrlWithQuery(['group'=>$g,'status'=>null,'payment'=>null,'page'=>null]);
        $is = fn($g) => (request('group')===$g);
      @endphp
      <a href="{{ $act(null) }}" class="btn btn-sm {{ $is(null)?'btn-dark':'btn-outline-dark' }}">Semua</a>
      <a href="{{ $act('draft') }}" class="btn btn-sm {{ $is('draft')?'btn-secondary':'btn-outline-secondary' }}">
        Draft <span class="badge text-bg-light ms-1">{{ (int)($byStatus['draft'] ?? 0) }}</span>
      </a>
      <a href="{{ $act('not_paid') }}" class="btn btn-sm {{ $is('not_paid')?'btn-warning':'btn-outline-warning' }}">
        Belum Lunas <span class="badge text-bg-light ms-1">{{ (int)(($byPayment['unpaid']??0)+($byPayment['partial']??0)) }}</span>
      </a>
      <a href="{{ $act('posted') }}" class="btn btn-sm {{ $is('posted')?'btn-success':'btn-outline-success' }}">
        Posted <span class="badge text-bg-light ms-1">{{ (int)($byStatus['posted'] ?? 0) }}</span>
      </a>
      <a href="{{ $act('paid') }}" class="btn btn-sm {{ $is('paid')?'btn-success':'btn-outline-success' }}">
        Paid <span class="badge text-bg-light ms-1">{{ (int)($byPayment['paid'] ?? 0) }}</span>
      </a>
      <a href="{{ $act('void') }}" class="btn btn-sm {{ $is('void')?'btn-danger':'btn-outline-danger' }}">
        Void <span class="badge text-bg-light ms-1">{{ (int)($byStatus['void'] ?? 0) }}</span>
      </a>
    </div>

    {{-- === Filter detail (opsional) === --}}
    <form class="row g-2 mb-3" method="get">
      <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">— status —</option>
          @foreach(['draft','posted','void'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <select name="payment" class="form-select form-select-sm">
          <option value="">— payment —</option>
          @foreach(['unpaid','partial','paid'] as $p)
            <option value="{{ $p }}" {{ request('payment')===$p ? 'selected':'' }}>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-sm btn-outline-primary">Filter</button>
        <a href="{{ route('history.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-striped mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Kode Transaksi</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Total</th>
            <th style="width:290px">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transaksis as $index => $t)
          @php
            $status = $t->status ?? 'posted';
            $paymentStatus = $t->payment_status ?? 'unpaid';
            $payClass = ['paid'=>'success','partial'=>'warning','unpaid'=>'secondary'][$paymentStatus] ?? 'secondary';
          @endphp
          <tr>
            <td>{{ $transaksis->firstItem() + $index }}</td>
            <td>{{ $t->kode_transaksi }}</td>
            <td>{{ optional($t->created_at)->format('d M Y H:i') }}</td>
            <td>
              <span class="badge bg-{{ $status === 'void' ? 'danger' : ($status==='draft'?'secondary':'success') }}">
                {{ ucfirst($status) }}
              </span>
            </td>
            <td>
              <span class="badge bg-{{ $payClass }}">{{ ucfirst($paymentStatus) }}</span>
            </td>
            <td>Rp{{ number_format((int)$t->total_harga, 0, ',', '.') }}</td>
            <td class="d-flex flex-wrap gap-1">

              {{-- DETAIL / PDF --}}
              <a href="{{ route('history.show', $t->id) }}" class="btn btn-sm btn-info">Detail</a>
              <a href="{{ route('history.pdf', $t->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">PDF</a>

              {{-- POSTING (hanya draft) --}}
              @if($status === 'draft')
                <button class="btn btn-sm btn-success"
                        data-coreui-toggle="modal" data-coreui-target="#postModal"
                        data-id="{{ $t->id }}"
                        data-kode="{{ $t->kode_transaksi }}">
                  Posting
                </button>
              @endif

              {{-- PAY (jika belum paid) --}}
              @if($paymentStatus !== 'paid' && $status !== 'void')
                <button class="btn btn-sm btn-primary"
                        data-coreui-toggle="modal" data-coreui-target="#payModal"
                        data-id="{{ $t->id }}"
                        data-sisa="{{ max(0, (int)$t->total_harga - (int)$t->dibayar) }}"
                        data-kode="{{ $t->kode_transaksi }}">
                  Tambah Pembayaran
                </button>
              @endif

              {{-- VOID (opsional, hanya posted & belum void) --}}
              @if($status === 'posted')
                <button class="btn btn-sm btn-outline-danger"
                        data-coreui-toggle="modal" data-coreui-target="#voidModal"
                        data-id="{{ $t->id }}" data-kode="{{ $t->kode_transaksi }}">
                  Void
                </button>
              @endif

            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center">Belum ada transaksi.</td></tr>
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
  <div class="modal-dialog">
    <form method="POST" id="postForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="postTitle">Posting Transaksi</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small text-muted">TRX: <span id="postKode">-</span></div>
          <div class="alert alert-warning">
            Pilih mode posting:
            <ul class="mb-0">
              <li><strong>Soft</strong>: hanya tandai posted (tanpa potong stok) — aman untuk data lama.</li>
              <li><strong>Hard</strong>: hitung ulang total & potong stok untuk item <em>barang</em>.</li>
            </ul>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeSoft" value="soft" checked>
            <label class="form-check-label" for="modeSoft">Soft (tanpa potong stok)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeHard" value="hard">
            <label class="form-check-label" for="modeHard">Hard (potong stok)</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success">Posting</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- PAY MODAL --}}
<div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payTitle" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="payForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
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
        <div class="modal-footer">
          <button class="btn btn-primary">Simpan Pembayaran</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- VOID MODAL --}}
<div class="modal fade" id="voidModal" tabindex="-1" aria-labelledby="voidTitle" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="voidForm">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="voidTitle">Batalkan (Void) Transaksi</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2 small text-muted">TRX: <span id="voidKode">-</span></div>
          <label class="form-label">Alasan</label>
          <input type="text" class="form-control" name="reason" required>
          <div class="form-text text-danger">Stok barang akan dikembalikan.</div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-danger">Void</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- === JS Modal Wiring === --}}
<script>
document.getElementById('postModal')?.addEventListener('show.coreui.modal', e=>{
  const btn = e.relatedTarget;
  const id  = btn?.dataset.id;
  const kode= btn?.dataset.kode;
  document.getElementById('postKode').textContent = kode || '-';
  // set action
  document.getElementById('postForm').action = "{{ route('history.index') }}"+"/"+id+"/post";
});

document.getElementById('payModal')?.addEventListener('show.coreui.modal', e=>{
  const btn = e.relatedTarget;
  const id  = btn?.dataset.id;
  const kode= btn?.dataset.kode;
  const sisa= btn?.dataset.sisa || 0;
  document.getElementById('payKode').textContent  = kode || '-';
  document.getElementById('paySisa').textContent  = new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(sisa).replace('IDR','Rp').trim();
  document.getElementById('payAmount').value      = sisa;
  document.getElementById('payForm').action       = "{{ url('/pembayaran/pay') }}/"+id;
});

document.getElementById('voidModal')?.addEventListener('show.coreui.modal', e=>{
  const btn = e.relatedTarget;
  const id  = btn?.dataset.id;
  const kode= btn?.dataset.kode;
  document.getElementById('voidKode').textContent = kode || '-';
  document.getElementById('voidForm').action      = "{{ url('/pembayaran/void') }}/"+id;
});
</script>
@endsection
