@extends('layouts.app')

@section('title', 'Edit Jasa')

@section('content')
@include('partials.neo-theme')
<style>
.card .btn-success,.card .btn-primary{ background: linear-gradient(135deg, var(--brand), var(--brand-2)); border-color: var(--brand-2); box-shadow: 0 6px 18px rgba(164,25,61,.28); color:#fff !important; }
.card .btn-success:hover,.card .btn-primary:hover{ filter:brightness(1.05); }
</style>
<div class="card shadow-sm mt-3 mb-4">
  <div class="card-header">
    <h5 class="mb-0">Edit Jasa</h5>
  </div>

  <div class="card-body">
    <form action="{{ route('jasa.update', $jasa->id) }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      @include('jasa.form')
      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('jasa.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>
@endsection
