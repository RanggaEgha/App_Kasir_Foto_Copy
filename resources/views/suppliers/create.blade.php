@extends('layouts.app')
@section('title','Tambah Supplier')

@section('content')
<div class="mb-3 d-flex align-items-center">
    <a href="{{ route('suppliers.index') }}"
       class="btn btn-outline-secondary btn-sm me-2 rounded-pill">
       <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="mb-0 fw-bold">Tambah Supplier</h2>
</div>

<x-card class="p-4 supplier-form">
    <form action="{{ route('suppliers.store') }}" method="POST">
        @csrf
        @include('suppliers._form', ['supplier'=>null])
        <div class="text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-save me-1"></i> Simpan
            </button>
        </div>
    </form>
</x-card>
@endsection
