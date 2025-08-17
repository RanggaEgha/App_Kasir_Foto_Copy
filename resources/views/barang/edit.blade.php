@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Edit Barang</h5>
      <small class="text-muted">{{ $barang->nama }}</small>
    </div>
    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">← Kembali</a>
  </div>

  <div class="card-body">
    <form action="{{ route('barang.update', $barang) }}" method="POST" enctype="multipart/form-data">
      @csrf @method('PUT')
      @include('barang._form', ['barang' => $barang, 'units' => $units])

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Perbarui</button>
      </div>
    </form>
  </div>
</div>
@endsection
