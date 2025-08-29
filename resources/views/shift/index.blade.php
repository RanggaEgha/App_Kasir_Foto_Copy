@extends('layouts.app')

@section('title', 'Shift Kasir')

@section('content')
<div class="container-fluid">

  <div class="row mb-4">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Shift Saya</h5>
          @if(session('success')) <span class="text-success">{{ session('success') }}</span> @endif
          @if($errors->any()) <span class="text-danger">{{ $errors->first() }}</span> @endif
        </div>

        <div class="card-body">
          @if ($myOpen)
            <div class="alert alert-info mb-4">
              <div class="d-flex justify-content-between">
                <div>
                  <div><strong>Status:</strong> <span class="badge bg-primary">Open</span></div>
                  <div><strong>Dibuka:</strong> {{ $myOpen->opened_at?->format('d/m/Y H:i') }}</div>
                  <div><strong>Kas Awal:</strong> @rupiah($myOpen->opening_cash)</div>
                  @php
                    $opsOpen = \App\Models\CashMovement::where('shift_id',$myOpen->id)
                      ->selectRaw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) as masuk, SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) as keluar")
                      ->first();
                  @endphp
                  <div><strong>Kas Ops:</strong>
                    <span class="text-success">+@rupiah((int)($opsOpen->masuk ?? 0))</span>
                    <span class="text-muted">/</span>
                    <span class="text-danger">-@rupiah((int)($opsOpen->keluar ?? 0))</span>
                  </div>
                  @if($myOpen->notes)
                    <div><strong>Catatan:</strong> {{ $myOpen->notes }}</div>
                  @endif
                </div>
              </div>
            </div>

            {{-- Kas Masuk/Keluar (operasional) --}}
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <form action="{{ route('shift.cash_in') }}" method="post" class="border rounded p-2">
                  @csrf
                  <div class="fw-semibold mb-2">Kas Masuk</div>
                  <div class="mb-2">
                    <input type="text" id="cashInAmountView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Nominal (Rp)" required>
                    <input type="hidden" name="amount" id="cashInAmount" value="0">
                  </div>
                  <div class="mb-2"><input type="text" class="form-control" name="reference" placeholder="Referensi (opsional)"></div>
                  <div class="mb-2"><input type="text" class="form-control" name="note" placeholder="Catatan (opsional)"></div>
                  <button class="btn btn-success btn-sm">Simpan</button>
                </form>
              </div>
              <div class="col-md-6">
                <form action="{{ route('shift.cash_out') }}" method="post" class="border rounded p-2">
                  @csrf
                  <div class="fw-semibold mb-2">Kas Keluar</div>
                  <div class="mb-2">
                    <input type="text" id="cashOutAmountView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Nominal (Rp)" required>
                    <input type="hidden" name="amount" id="cashOutAmount" value="0">
                  </div>
                  <div class="mb-2"><input type="text" class="form-control" name="reference" placeholder="Referensi (opsional)"></div>
                  <div class="mb-2"><input type="text" class="form-control" name="note" placeholder="Catatan (opsional)"></div>
                  <button class="btn btn-outline-danger btn-sm">Simpan</button>
                </form>
              </div>
            </div>

            <form action="{{ route('shift.close', $myOpen) }}" method="POST" class="row g-3">
              @csrf
              <div class="col-md-6">
                <label class="form-label">Kas Akhir (Closing Cash)</label>
                <input type="text" id="closingCashView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Isi langsung atau pakai hitung lembar di kanan">
                <input type="hidden" name="closing_cash" id="closingCash" value="">
              </div>
              <div class="col-md-6">
                <label class="form-label">Catatan (opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="mis: selisih karena uang receh">
              </div>

              {{-- Hitung lembar uang --}}
              <div class="col-12">
                <div class="border rounded p-2">
                  <div class="fw-semibold mb-2">Hitung Lembar Uang</div>
                  <div class="row g-2">
                    @php $noms=[100000,50000,20000,10000,5000,2000,1000,500,200,100]; @endphp
                    @foreach($noms as $n)
                      <div class="col-6 col-md-3 col-lg-2">
                        <label class="form-label small">Rp{{ number_format($n,0,',','.') }}</label>
                        <input type="number" name="denom[{{ $n }}]" class="form-control" min="0" value="0">
                      </div>
                    @endforeach
                  </div>
                  <div class="small text-muted mt-1">Jika diisi, sistem menghitung otomatis Kas Akhir dari lembar uang.</div>
                </div>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-danger">Tutup Shift</button>
              </div>
            </form>
          @else
            <form action="{{ route('shift.open') }}" method="POST" class="row g-3">
              @csrf
              <div class="col-md-6">
                <label class="form-label">Kas Awal (Opening Cash)</label>
                <input type="text" id="openingCashView" class="form-control" inputmode="numeric" autocomplete="off" placeholder="0" required>
                <input type="hidden" name="opening_cash" id="openingCash" value="0">
              </div>
              <div class="col-md-6">
                <label class="form-label">Catatan (opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="mis: uang awal dari brankas">
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Buka Shift</button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h6 class="mb-0">Tips</h6>
        </div>
        <div class="card-body small text-muted">
          <ul class="mb-0">
            <li>Hanya boleh ada satu shift <em>open</em> per kasir.</li>
            <li><strong>Expected Cash</strong> sementara = Kas Awal. Setelah modul POS aktif, expected = kas awal + pemasukan cash − refund cash.</li>
            <li>Tutup shift setiap selesai jaga agar rekonsiliasi rapi.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-header">
      <h5 class="mb-0">Riwayat Shift</h5>
    </div>
    <div class="card-body p-0">
      @if($myOpen)
        @php
          $movs = \App\Models\CashMovement::where('shift_id',$myOpen->id)->orderByDesc('id')->limit(10)->get();
        @endphp
        @if(count($movs))
          <div class="p-3">
            <div class="fw-semibold mb-2">Kas Masuk/Keluar Terakhir</div>
            <div class="table-responsive">
              <table class="table table-sm">
                <thead><tr><th>Waktu</th><th>Arah</th><th>Nominal</th><th>Ref</th><th>Catatan</th></tr></thead>
                <tbody>
                  @foreach($movs as $m)
                    <tr>
                      <td>{{ $m->paid_at?->format('Y-m-d H:i') }}</td>
                      <td><span class="badge {{ $m->direction==='in'?'text-bg-success':'text-bg-danger' }}">{{ strtoupper($m->direction) }}</span></td>
                      <td>@rupiah($m->amount)</td>
                      <td>{{ $m->reference }}</td>
                      <td>{{ $m->note }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif
      @endif
      <div class="table-responsive">
        <table class="table table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Kasir</th>
              <th>Open</th>
              <th>Kas Awal</th>
              <th>Close</th>
              <th>Kas Akhir</th>
              <th>Expected</th>
              <th>Kas Ops</th>
              <th>Selisih</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recent as $idx => $s)
              <tr>
                <td>{{ $recent->firstItem() + $idx }}</td>
                <td>{{ $s->user?->name ?? '—' }}</td>
                <td>{{ $s->opened_at?->format('d/m/Y H:i') }}</td>
                <td>@rupiah($s->opening_cash)</td>
                <td>{{ $s->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>@if(!is_null($s->closing_cash)) @rupiah($s->closing_cash) @else — @endif</td>
                <td>@rupiah($s->expected_cash)</td>
                @php
                  $ops = \App\Models\CashMovement::where('shift_id',$s->id)
                    ->selectRaw("SUM(CASE WHEN direction='in' THEN amount ELSE 0 END) as masuk, SUM(CASE WHEN direction='out' THEN amount ELSE 0 END) as keluar")
                    ->first();
                  $in  = (int)($ops->masuk ?? 0); $out = (int)($ops->keluar ?? 0);
                @endphp
                <td>
                  <span class="text-success">+@rupiah($in)</span>
                  @if($out>0)
                    <span class="text-muted"> / </span>
                    <span class="text-danger">-@rupiah($out)</span>
                  @endif
                </td>
                @php
                  $diff = (int) $s->difference;
                  $cls  = $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-secondary');
                @endphp
                <td class="{{ $cls }}">
                  @if($diff>0)+@elseif($diff<0)-@endif@rupiah(abs($diff))
                </td>
                <td>
                  <span class="badge {{ $s->status==='open' ? 'bg-primary' : 'bg-secondary' }}">
                    {{ ucfirst($s->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center text-muted">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      {{ $recent->withQueryString()->onEachSide(1)->links('components.pagination.pill-clean') }}
    </div>
  </div>

</div>
@endsection
@push('scripts')
<script>
  const clean = s => +(String(s||'').replace(/[^0-9]/g,''))||0;
  const idFormat = n => (Number(n)||0).toLocaleString('id-ID');
  function bindMoneyPair(viewId, hidId){
    const v=document.getElementById(viewId), h=document.getElementById(hidId); if(!v||!h) return;
    const sync=()=>{ if(v.value.trim()===''){ h.value=''; return; } const raw=clean(v.value); v.value = raw? idFormat(raw):''; h.value=raw; };
    v.addEventListener('input', sync); v.addEventListener('blur', sync);
    // init
    if(h.value){ v.value = idFormat(h.value); }
  }
  bindMoneyPair('openingCashView','openingCash');
  bindMoneyPair('closingCashView','closingCash');
  bindMoneyPair('cashInAmountView','cashInAmount');
  bindMoneyPair('cashOutAmountView','cashOutAmount');
</script>
@endpush
