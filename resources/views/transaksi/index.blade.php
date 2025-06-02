@extends('layouts.app')

@section('title', 'Daftar Transaksi')

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Transaksi</h5>
    <a href="{{ route('transaksi.create') }}" class="btn btn-primary btn-sm">+ Tambah Transaksi</a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table id="transaksiTable" class="table table-bordered table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Kode Transaksi</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($transaksis as $index => $transaksi)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $transaksi->kode_transaksi }}</td>
            <td>{{ $transaksi->created_at->format('d M Y H:i') }}</td>
            <td>Rp{{ number_format($transaksi->total_harga, 0, ',', '.') }}</td>
            <td>
              <a href="{{ route('transaksi.show', $transaksi->id) }}" class="btn btn-info btn-sm">Detail</a>
              <form action="{{ route('transaksi.destroy', $transaksi->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center">Tidak ada data transaksi.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
