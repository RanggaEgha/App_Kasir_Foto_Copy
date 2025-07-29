@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0">Edit Barang</h5>
  </div>

  <div class="card-body">
    <form action="{{ route('barang.update', $barang) }}" method="POST">
      @csrf
      @method('PUT')

      {{--  ⬇️  panggil partial _form  --}}
      @include('barang._form', ['barang' => $barang, 'units' => $units])

      <div class="d-flex justify-content-between">
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Perbarui</button>
      </div>
    </form>
  </div>
</div>
@endsection
