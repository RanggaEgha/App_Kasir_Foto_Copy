@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<div class="container-fluid px-4">
  <h1 class="mt-4">Dashboard</h1>

  {{-- Ringkasan omzet --}}
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card text-bg-primary">
        <div class="card-body">
          <h5 class="card-title">Pemasukan Hari Ini</h5>
          <p class="fs-4 mb-0">Rp{{ number_format($harian,0,',','.') }}</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-bg-success">
        <div class="card-body">
          <h5 class="card-title">Pemasukan 7 Hari</h5>
          <p class="fs-4 mb-0">Rp{{ number_format($mingguan,0,',','.') }}</p>
        </div>
      </div>
    </div>
    <div class="col-md-6 d-flex justify-content-end align-items-start gap-2">
      <a href="{{ route('dashboard.pdf')   }}" class="btn btn-outline-primary">Export PDF</a>
      <a href="{{ route('dashboard.excel') }}" class="btn btn-outline-success">Export Excel</a>
    </div>
  </div>

  {{-- Top 10 --}}
  <div class="card mb-4">
    <div class="card-header">Top 10 Barang / Jasa</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>Nama</th><th>Qty</th><th>Omzet</th></tr>
        </thead>
        <tbody>
          @foreach($topItems as $i=>$t)
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $t->nama }}</td>
              <td>{{ $t->qty }}</td>
              <td>Rp{{ number_format($t->omzet,0,',','.') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  {{-- Stok kritis --}}
  <div class="card">
    <div class="card-header">Stok Hampir Habis (≤ 50 pcs / ≤ 2 pack)</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light">
          <tr><th>#</th><th>Nama</th><th>Pcs</th><th>Pack</th></tr>
        </thead>
        <tbody>
          @php
            $stok = fn($b,$kode)=> optional($b->units->firstWhere('kode',$kode))->pivot->stok ?? '—';
          @endphp
          @forelse($stokKritis as $i=>$b)
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $b->nama }}</td>
              <td>{{ $stok($b,'pcs') }}</td>
              <td>{{ $stok($b,'pack') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center py-3">Semua stok aman</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
