@extends('layouts.app')
@section('title','Tambah User')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Tambah User</h5>
      <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
        <svg class="me-1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline><line x1="9" y1="12" x2="21" y2="12"></line></svg>
        Kembali
      </a>
    </div>
    <div class="card-body">
      <form action="{{ route('users.store') }}" method="POST">
        @csrf
        @include('users._form', ['user' => $user])
        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
          <button class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
