@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')
@include('partials.neo-theme')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Edit Barang</h5>
      <small class="text-muted">{{ $barang->nama }}</small>
    </div>
    <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">← Kembali</a>
  </div>

  <div class="card-body">
    @include('partials.flash-neo')
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
<style>
/* Scoped brand buttons for edit page */
.card .btn-primary{ background: linear-gradient(135deg, var(--brand), var(--brand-2)); border-color: var(--brand-2); box-shadow: 0 6px 18px rgba(164,25,61,.28); }
.card .btn-primary:hover{ filter:brightness(1.05); }
.card .btn-outline-primary{ color: var(--brand); border-color: rgba(164,25,61,.45); }
.card .btn-outline-primary:hover{ background: rgba(255,223,185,.65); color: var(--brand); border-color: rgba(164,25,61,.55); }
</style>
@endsection
