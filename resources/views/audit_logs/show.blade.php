@extends('layouts.app')
@section('title','Detail Log')

@section('content')
@php
  $old   = is_array($auditLog->old_values) ? $auditLog->old_values : [];
  $new   = is_array($auditLog->new_values) ? $auditLog->new_values : [];
  $media = isset($auditLog->properties['media_changes']) && is_array($auditLog->properties['media_changes'])
           ? $auditLog->properties['media_changes'] : [];
  $fmt = fn($v) => is_numeric($v) ? number_format($v, 0, ',', '.') : (is_bool($v) ? ($v ? 'true' : 'false') : (is_null($v) ? '—' : (string) $v));
@endphp

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Detail Audit Log #{{ $auditLog->id }}</h5>
    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">← Kembali</a>
  </div>

  <div class="card-body">
    <dl class="row">
      <dt class="col-sm-3">Waktu</dt>
      <dd class="col-sm-9">{{ $auditLog->created_at?->format('Y-m-d H:i:s') }}</dd>

      <dt class="col-sm-3">Actor</dt>
      <dd class="col-sm-9">{{ $auditLog->actor_name }} @if($auditLog->actor_role)<span class="text-muted">({{ $auditLog->actor_role }})</span>@endif</dd>

      <dt class="col-sm-3">Event</dt>
      <dd class="col-sm-9"><code>{{ $auditLog->event }}</code></dd>

      <dt class="col-sm-3">Subject</dt>
      <dd class="col-sm-9">{{ class_basename($auditLog->subject_type) }} #{{ $auditLog->subject_id }}</dd>

      <dt class="col-sm-3">Deskripsi</dt>
      <dd class="col-sm-9 text-break">{{ $auditLog->description }}</dd>

      <dt class="col-sm-3">URL</dt>
      <dd class="col-sm-9">{{ $auditLog->method }} {{ $auditLog->url }}</dd>

      <dt class="col-sm-3">IP</dt>
      <dd class="col-sm-9">{{ $auditLog->ip }}</dd>

      <dt class="col-sm-3">User Agent</dt>
      <dd class="col-sm-9 text-break">{{ $auditLog->user_agent }}</dd>
    </dl>

    {{-- Rincian perubahan field --}}
    <hr>
    <h6 class="mb-3">Perubahan Data</h6>

    @if(count($old))
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th style="width: 260px;">Field</th>
              <th>Before</th>
              <th>After</th>
            </tr>
          </thead>
          <tbody>
            @foreach($old as $field => $oldVal)
              @php $newVal = $new[$field] ?? null; @endphp
              <tr>
                <td class="text-muted">{{ $field }}</td>
                <td class="text-danger">{{ $fmt($oldVal) }}</td>
                <td class="text-success">{{ $fmt($newVal) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <p class="text-muted mb-0">Tidak ada perubahan field yang tercatat.</p>
    @endif

    {{-- Rincian perubahan file/gambar --}}
    @if(count($media))
      <hr>
      <h6 class="mb-3">Perubahan File/Gambar</h6>
      <div class="row g-3">
        @foreach($media as $m)
          <div class="col-md-6">
            <div class="border rounded p-2 h-100">
              <div class="small text-muted mb-2"><strong>{{ $m['attribute'] ?? '-' }}</strong></div>
              <div class="row">
                <div class="col-6">
                  <div class="small text-muted mb-1">Sebelumnya</div>
                  @if(!empty($m['old']))
                    <img src="{{ $m['old'] }}" alt="old" class="img-fluid rounded border">
                  @else
                    <div class="text-muted small fst-italic">— tidak ada —</div>
                  @endif
                </div>
                <div class="col-6">
                  <div class="small text-muted mb-1">Sesudah</div>
                  @if(!empty($m['new']))
                    <img src="{{ $m['new'] }}" alt="new" class="img-fluid rounded border">
                  @else
                    <div class="text-muted small fst-italic">— tidak ada —</div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- Raw JSON (opsional untuk debugging) --}}
    <hr>
    <details>
      <summary class="mb-2"><strong>Raw JSON</strong> (debug)</summary>
      <div class="row">
        <div class="col-md-6">
          <strong>old_values</strong>
          <pre class="bg-light p-2 rounded small">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        <div class="col-md-6">
          <strong>new_values</strong>
          <pre class="bg-light p-2 rounded small">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @if($auditLog->properties)
        <div class="col-12">
          <strong>properties</strong>
          <pre class="bg-light p-2 rounded small">{{ json_encode($auditLog->properties, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif
      </div>
    </details>
  </div>
</div>
@endsection
