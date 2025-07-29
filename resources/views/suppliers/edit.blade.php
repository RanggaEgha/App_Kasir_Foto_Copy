@extends('layouts.app')
@section('title','Edit Supplier')

@section('content')
<div class="mb-3 d-flex align-items-center">
    <a href="{{ route('suppliers.index') }}"
       class="btn btn-outline-secondary btn-sm me-2 rounded-pill">
       <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="mb-0 fw-bold">Edit Supplier</h2>
</div>

<x-card class="p-4 supplier-form">
    <form action="{{ route('suppliers.update',$supplier) }}" method="POST">
        @csrf @method('PUT')
        @include('suppliers._form', ['supplier'=>$supplier])
        <div class="text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-save me-1"></i> Perbarui
            </button>
        </div>
    </form>
</x-card>
@endsection
