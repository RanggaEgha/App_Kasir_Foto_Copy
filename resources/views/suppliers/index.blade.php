@extends('layouts.app')
@section('title','Data Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">Data Supplier</h2>

    <a href="{{ route('suppliers.create') }}"
       class="btn btn-primary btn-sm rounded-pill shadow-sm">
       <i class="bi bi-plus-circle me-1"></i> Tambah Supplier
    </a>
</div>

@if(session('success'))
   <x-alert type="success" :message="session('success')" />
@endif

<x-card>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Kontak</th>
            <th>Email</th>
            <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $s)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $s->name }}</td>
            <td>{{ $s->phone }}</td>
            <td>{{ $s->email }}</td>
            <td class="text-end">
              <a href="{{ route('suppliers.edit',$s) }}"
                 class="btn btn-outline-secondary btn-sm rounded-pill me-1">
                 <i class="bi bi-pencil-square"></i>
              </a>
              <form action="{{ route('suppliers.destroy',$s) }}"
                    method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit"
                        class="btn btn-outline-danger btn-sm rounded-pill"
                        onclick="return confirm('Hapus {{ $s->name }}?')">
                    <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Belum ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</x-card>
@endsection
