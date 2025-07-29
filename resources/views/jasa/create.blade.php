@extends('layouts.app')

@section('title', 'Tambah Jasa')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0">Tambah Jasa Baru</h5>
  </div>

  <div class="card-body">
    <form action="{{ route('jasa.store') }}" method="POST">
      @csrf
      @include('jasa.form')
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
