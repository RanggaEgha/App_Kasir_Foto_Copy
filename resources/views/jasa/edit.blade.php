@extends('layouts.app')

@section('title', 'Edit Jasa')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <h5 class="mb-0">Edit Jasa</h5>
  </div>

  <div class="card-body">
    <form action="{{ route('jasa.update', $jasa->id) }}" method="POST">
      @csrf
      @method('PUT')
      @include('jasa.form')
      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>
@endsection
