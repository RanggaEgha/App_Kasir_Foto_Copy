@extends('layouts.app')
@section('title','Audit Logs')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0">Audit Logs</h5>
    <small class="text-muted">Jejak aktivitas admin & kasir</small>
  </div>

  <div class="card-body">

    {{-- FILTERS: responsive, no overflow --}}
    <form method="GET" class="row g-2 mb-3 align-items-start">
      {{-- q --}}
      <div class="col-12 col-md-6 col-lg-3">
        <input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari deskripsi...">
      </div>

      {{-- event --}}
      <div class="col-6 col-md-3 col-lg-2">
        <input name="event" value="{{ request('event') }}" class="form-control" placeholder="Event">
      </div>

      {{-- actor_id --}}
      <div class="col-6 col-md-3 col-lg-2">
        <input name="actor_id" value="{{ request('actor_id') }}" class="form-control" placeholder="Actor ID">
      </div>

      {{-- subject_type --}}
      <div class="col-12 col-md-6 col-lg-2">
        <input name="subject_type" value="{{ request('subject_type') }}" class="form-control" placeholder="Subject Type">
      </div>

      {{-- date_from --}}
      <div class="col-6 col-md-3 col-lg-1">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
      </div>

      {{-- date_to --}}
      <div class="col-6 col-md-3 col-lg-1">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
      </div>

      {{-- actions --}}
      <div class="col-12 d-flex gap-2 justify-content-end">
        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">Reset</a>
        <button type="submit" class="btn btn-primary">Terapkan</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>Waktu</th>
            <th>Actor</th>
            <th>Event</th>
            <th>Subject</th>
            <th>Deskripsi</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
          <tr>
            <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
            <td>
              {{ $log->actor_name }}
              @if($log->actor_role)
                <small class="text-muted">({{ $log->actor_role }})</small>
              @endif
            </td>
            <td><code>{{ $log->event }}</code></td>
            <td>{{ class_basename($log->subject_type) }}#{{ $log->subject_id }}</td>
            <td class="text-break">{{ $log->description }}</td>
            <td class="text-end">
              <a href="{{ route('audit.show',$log) }}" class="btn btn-outline-secondary btn-sm">Detail</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{ $logs->links() }}
  </div>
</div>
@endsection
