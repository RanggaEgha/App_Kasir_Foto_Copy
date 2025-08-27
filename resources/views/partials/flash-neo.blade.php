@php
  $success = session('success');
  $error   = session('error');
  $anyErr  = isset($errors) && $errors->any();
  $errList = $anyErr ? collect($errors->all())->unique()->values() : collect();
@endphp

@if($success || $error || $anyErr)
  <div class="neo-flash-wrap mb-3">
    @if($success)
      <div class="neo-flash neo-flash--success">{{ $success }}</div>
    @endif
    @if($error)
      <div class="neo-flash neo-flash--error">{{ $error }}</div>
    @endif
    @if($errList->isNotEmpty())
      <div class="neo-flash neo-flash--error">
        <div class="fw-700 mb-1">Periksa kembali isian form:</div>
        <ul class="m-0 ps-3">
          @foreach($errList as $msg)
            <li>{{ $msg }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>

  <style>
    .neo-flash{ border-radius:12px; padding:.75rem 1rem; border:1px solid rgba(164,25,61,.25); background:rgba(255,223,185,.45); color:var(--brand); }
    .neo-flash + .neo-flash{ margin-top:.5rem; }
    .neo-flash--success{ background:rgba(255,223,185,.55); }
    .neo-flash--error{ background:rgba(220,53,69,.12); color:#b02a37; border-color:rgba(220,53,69,.28); }
  </style>
@endif
