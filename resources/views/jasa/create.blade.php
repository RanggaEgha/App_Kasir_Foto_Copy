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
      <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>
@endsection
