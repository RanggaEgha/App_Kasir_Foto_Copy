@extends('layouts.app')
@section('title', 'Tambah Barang')

@section('content')
<div class="card shadow-sm">
  <div class="card-header"><h5>Tambah Barang</h5></div>
  <div class="card-body">
    <form action="{{ route('barang.store') }}" method="POST">
      @include('barang.form')
    </form>
  </div>
</div>
@endsection
