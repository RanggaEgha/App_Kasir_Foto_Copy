@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')
<div class="card shadow-sm">
  <div class="card-header"><h5>Edit Barang</h5></div>
  <div class="card-body">
    <form action="{{ route('barang.update', $barang->id) }}" method="POST">
      @include('barang.form', ['barang' => $barang])
    </form>
  </div>
</div>
@endsection
