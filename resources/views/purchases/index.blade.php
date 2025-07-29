@extends('layouts.app')
@section('title','Purchase Orders')

@section('content')
<div class="d-flex justify-content-between mb-3">
  <h2 class="fw-bold mb-0">Pembelian</h2>
  <a href="{{ route('purchases.create') }}"
     class="btn btn-primary btn-sm rounded-pill shadow-sm">
     <i class="bi bi-plus-circle me-1"></i> Tambah PO
  </a>
</div>

@if(session('success')) <x-alert type="success" :message="session('success')" /> @endif

<x-card>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead class="table-light">
        <tr><th>#</th><th>No. Faktur</th><th>Supplier</th><th>Tanggal</th><th>Total</th></tr>
      </thead>
      <tbody>
        @foreach($orders as $o)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $o->invoice_no }}</td>
            <td>{{ $o->supplier->name }}</td>
            <td>{{ $o->purchase_date->format('d M Y H:i') }}</td>
            <td class="text-end">{{ number_format($o->total,0,',','.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-card>
@endsection
