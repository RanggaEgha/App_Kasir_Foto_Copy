@extends('layouts.app')

@section('title', 'Data Barang')

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Barang</h5>
    <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm">+ Tambah Barang</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table id="barangTable" class="table table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th>Nama Barang</th>
            <th>Satuan</th>
            <th>Isi per Paket</th>
            <th>Stok Satuan</th>
            <th>Stok Paket</th>
            <th>Harga Satuan</th>
            <th>Harga Paket</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($barangs as $barang)
          <tr>
            <td>{{ $barang->nama }}</td>
            <td>{{ $barang->satuan }}</td>
            <td>{{ $barang->isi_per_paket }}</td>
            <td>{{ $barang->stok_satuan }}</td>
            <td>{{ $barang->stok_paket }}</td>
            <td>Rp{{ number_format($barang->harga_satuan, 0, ',', '.') }}</td>
            <td>Rp{{ number_format($barang->harga_paket, 0, ',', '.') }}</td>
            <td>
              <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-sm btn-warning">Edit</a>
              <form action="{{ route('barang.destroy', $barang->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function () {
    $('#barangTable').DataTable({
      responsive: true
    });
  });
</script>
@endpush
