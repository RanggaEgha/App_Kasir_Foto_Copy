@extends('layouts.app')
@section('title','Tambah User')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Tambah User</h5>
      <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">← Kembali</a>
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
