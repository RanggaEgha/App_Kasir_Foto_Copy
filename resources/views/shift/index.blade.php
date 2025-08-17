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
                  <div><strong>Kas Awal:</strong> Rp{{ number_format($myOpen->opening_cash,0,',','.') }}</div>
                  @if($myOpen->notes)
                    <div><strong>Catatan:</strong> {{ $myOpen->notes }}</div>
                  @endif
                </div>
              </div>
            </div>

            <form action="{{ route('shift.close', $myOpen) }}" method="POST" class="row g-3">
              @csrf
              <div class="col-md-6">
                <label class="form-label">Kas Akhir (Closing Cash)</label>
                <input type="number" name="closing_cash" class="form-control" min="0" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Catatan (opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="mis: selisih karena uang receh">
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
                <input type="number" name="opening_cash" class="form-control" min="0" required>
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
                <td>Rp{{ number_format($s->opening_cash,0,',','.') }}</td>
                <td>{{ $s->closed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>{{ $s->closing_cash !== null ? 'Rp'.number_format($s->closing_cash,0,',','.') : '—' }}</td>
                <td>Rp{{ number_format($s->expected_cash,0,',','.') }}</td>
                <td class="{{ $s->difference === 0 ? 'text-success' : 'text-danger' }}">
                  {{ ($s->difference>0?'+':'') . number_format($s->difference,0,',','.') }}
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
      {{ $recent->links() }}
    </div>
  </div>

</div>
@endsection
