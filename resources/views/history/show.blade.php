@extends('layouts.app')
@section('title','Detail Transaksi')

@section('content')
<div class="container-fluid px-4">
  <h1 class="mt-4">Detail Transaksi</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body">

      @php
        $status = $transaksi->status ?? 'posted';
        $paymentStatus = $transaksi->payment_status ?? 'unpaid';
        $payClass = ['paid'=>'success','partial'=>'warning','unpaid'=>'secondary'][$paymentStatus] ?? 'secondary';
      @endphp
      <div class="mb-3">
        @php
          $statusId = fn($s) => match($s){ 'draft'=>'Draf','posted'=>'Diposting','void'=>'Dibatalkan', default=>ucfirst((string)$s) };
          $payId    = fn($s) => match($s){ 'paid'=>'Lunas','partial'=>'Sebagian (parsial)','unpaid'=>'Belum dibayar', default=>ucfirst((string)$s) };
          $methodId = fn($m) => match($m){ 'cash'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS', default=>ucfirst((string)$m) };
          $refundSum = (int) ($transaksi->payments?->where('direction','out')->sum('amount') ?? 0);
          $paidInSum = (int) ($transaksi->payments?->where('direction','in')->sum('amount') ?? 0);
          $netPaid   = max(0, $paidInSum - $refundSum);
        @endphp
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
      <table class="table table-borderless w-auto mb-4">
        <tbody>
          <tr><th>Kode Transaksi</th><td>{{ $transaksi->kode_transaksi }}</td></tr>
          <tr><th>Tanggal</th>
              <td>{{ optional($transaksi->tanggal)->translatedFormat('d F Y • H:i') ?? optional($transaksi->created_at)->format('d M Y H:i') }} WIB</td></tr>
          @if(!empty($transaksi->metode_bayar))
          <tr><th>Metode Bayar</th><td>{{ $methodId($transaksi->metode_bayar) }}</td></tr>
          @endif
          @php
            $gross=0; $netItems=0; foreach($transaksi->items as $it){ $gross+=(int)$it->jumlah*(int)$it->harga_satuan; $netItems+=(int)$it->subtotal; }
            $itemDisc = max(0, $gross - $netItems);

            // Ringkasan pembayaran
            $totalIn  = (int) ($transaksi->payments?->where('direction','in')->sum('amount') ?? 0);
            $totalOut = (int) ($transaksi->payments?->where('direction','out')->sum('amount') ?? 0);
            $netPaid  = max(0, $totalIn - $totalOut);
          @endphp
          @if($itemDisc>0)
            <tr><th>Diskon Item</th><td>Rp{{ number_format($itemDisc,0,',','.') }}</td></tr>
          @endif
          @if((int)$transaksi->discount_amount > 0)
            <tr><th>Diskon Nota</th><td>Rp{{ number_format((int)$transaksi->discount_amount,0,',','.') }}</td></tr>
            @if(!empty($transaksi->discount_reason))
              <tr><th>Alasan Diskon</th><td>{{ $transaksi->discount_reason }}</td></tr>
            @endif
            @if(!empty($transaksi->coupon_code))
              <tr><th>Kupon</th><td>{{ $transaksi->coupon_code }}</td></tr>
            @endif
          @endif
          @if($totalIn>0)
            <tr><th>Total Pembayaran</th>
                <td>Rp. {{ number_format($totalIn,0,',','.') }}</td></tr>
          @endif
          <tr><th>Dibayar</th>
              <td>Rp. {{ number_format((int)$transaksi->dibayar,0,',','.') }}</td></tr>
          @if($totalOut>0)
            <tr><th>Total Refund</th>
                <td class="text-danger">- Rp. {{ number_format($totalOut,0,',','.') }}</td></tr>
          @endif
          <tr><th>Kembalian</th>
              <td>Rp. {{ number_format((int)$transaksi->kembalian,0,',','.') }}</td></tr>
        </tbody>
      </table>

      {{-- ===== TABLE ITEM ===== --}}
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nama Barang / Jasa</th>
              <th>Jumlah</th>
              <th>Harga Satuan</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transaksi->items as $it)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $it->tipe_item == 'barang' ? ($it->barang->nama ?? '-') : ($it->jasa->nama ?? '-') }}</td>
                <td>
                  {{ (int)$it->jumlah }}
                  {{ $it->tipe_item == 'barang'
                       ? (isset($it->tipe_qty) && $it->tipe_qty == 'paket' ? 'paket' : 'pcs')
                       : ($it->jasa->satuan ?? '') }}
                  @if($it->tipe_item == 'barang' && (isset($it->tipe_qty) && $it->tipe_qty == 'paket') && isset($it->barang->isi_per_paket))
                    (isi {{ $it->barang->isi_per_paket }})
                  @endif
                  @php $refunded = (int)($it->refunded_qty ?? 0); $sold=(int)$it->jumlah; $remain = max(0,$sold-$refunded); @endphp
                  @if($refunded>0 || $remain < $sold)
                    <div class="small text-muted mt-1">Terrefund: {{ $refunded }} • Sisa: {{ $remain }}</div>
                  @endif
                </td>
                <td>Rp. {{ number_format((int)$it->harga_satuan,0,',','.') }}</td>
                <td>Rp. {{ number_format((int)$it->subtotal,0,',','.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total</th>
              <th>Rp. {{ number_format((int)$transaksi->total_harga,0,',','.') }}</th>
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
              <th style="width: 170px">Waktu</th>
              <th>Jenis</th>
              <th>Metode</th>
              <th>Referensi</th>
              <th class="text-end" style="width: 160px">Jumlah</th>
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
                <td>{{ $p->reference ?? ($p->note ?? '-') }}</td>
                <td class="text-end {{ $p->direction==='out'?'text-danger':'' }}">
                  {{ $p->direction==='out' ? '-' : '' }}Rp. {{ number_format((int)$p->amount,0,',','.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total Pembayaran</th>
              <th class="text-end">Rp. {{ number_format($totalIn,0,',','.') }}</th>
            </tr>
            @if($totalOut>0)
            <tr>
              <th colspan="4" class="text-end">Total Refund</th>
              <th class="text-end text-danger">- Rp. {{ number_format($totalOut,0,',','.') }}</th>
            </tr>
            @endif
            <tr>
              <th colspan="4" class="text-end">Dibayar Bersih</th>
              <th class="text-end">Rp. {{ number_format($netPaid,0,',','.') }}</th>
            </tr>
          </tfoot>
        </table>
      </div>
      @endif

      {{-- ===== ACTION BUTTONS ===== --}}
      <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('history.pdf', $transaksi->id) }}"
           class="btn btn-outline-primary" target="_blank">Cetak&nbsp;PDF</a>
        @if(($status ?? 'posted') !== 'void' && (int)($transaksi->dibayar ?? 0) > 0)
          @php $maxRefund = (int) ($transaksi->dibayar ?? 0); @endphp
          <button class="btn btn-warning"
                  data-coreui-toggle="modal" data-coreui-target="#refundModal"
                  data-id="{{ $transaksi->id }}"
                  data-kode="{{ $transaksi->kode_transaksi }}"
                  data-max="{{ $maxRefund }}">
            Refund
          </button>
          <button class="btn btn-outline-warning"
                  data-coreui-toggle="modal" data-coreui-target="#refundItemsModal">
            Refund Per Item
          </button>
        @endif
        <a href="{{ route('history.index') }}" class="btn btn-secondary">← Kembali</a>
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
      <div class="modal-content" style="border-radius:16px">
        <div class="modal-header" style="border-bottom:1px solid #e5e7eb">
          <h5 class="modal-title" id="refundTitle">Refund (Pengembalian Dana)</h5>
          <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Tutup"></button>
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
        <div class="modal-footer" style="border-top:1px solid #e5e7eb">
          <button class="btn btn-warning">Simpan Refund</button>
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
      <div class="modal-content" style="border-radius:16px">
        <div class="modal-header" style="border-bottom:1px solid #e5e7eb">
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
                    <td>
                      <div class="fw-semibold">{{ $nama }}</div>
                      <div class="small text-muted">
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
                      <input type="number" min="0" max="{{ $remain }}" value="0" name="items[{{ $it->id }}]" class="form-control form-control-sm" style="max-width:90px; display:inline-block" {{ $remain<=0 ? 'disabled' : '' }}>
                    </td>
                    <td class="text-end">Rp. {{ number_format((int)$it->subtotal,0,',','.') }}</td>
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
        <div class="modal-footer" style="border-top:1px solid #e5e7eb">
          <button class="btn btn-warning">Simpan Refund Per Item</button>
        </div>
      </div>
    </form>
  </div>
</div>
{{-- JS untuk wiring modal --}}
<script>
(function(){
  const formatRibuan = (val) => {
    const n = Math.max(0, parseInt((val||'').toString().replace(/\D+/g,'')) || 0);
    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n);
  };
  const currencyID = (v) => 'Rp. ' + formatRibuan(v);
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
    document.getElementById('refundForm').action      = "{{ url('/pembayaran/refund') }}/"+id;
  });
  // format uang & normalisasi saat submit
  const moneyInp = document.getElementById('refundAmount');
  if (moneyInp) {
    moneyInp.addEventListener('input', ()=>{
      moneyInp.value = formatRibuan(moneyInp.value);
    });
    moneyInp.form && moneyInp.form.addEventListener('submit', ()=>{
      moneyInp.value = normalizeDigits(moneyInp.value);
    });
  }
})();
</script>
@endsection
