@extends('layouts.app')
@section('title', 'Tambah Barang')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0">Tambah Barang</h5>
  </div>

  <div class="card-body">
    <form action="{{ route('barang.store') }}" method="POST">
      @csrf

      {{--  ⬇️  panggil partial _form  --}}
      @include('barang._form', ['units' => $units])

      <div class="d-flex justify-content-between">
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
