@extends('layouts.app')

@section('title', 'Daftar Jasa')

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Jasa</h5>
    <a href="{{ route('jasa.create') }}" class="btn btn-primary btn-sm">+ Tambah Jasa</a>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Nama Jasa</th>
            <th>Jenis</th>
            <th>Satuan</th>
            <th>Harga / Satuan</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($jasas as $index => $jasa)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $jasa->nama }}</td>
            <td>{{ $jasa->jenis }}</td>
            <td>{{ $jasa->satuan }}</td>
            <td>Rp{{ number_format($jasa->harga_per_satuan, 0, ',', '.') }}</td>
            <td>{{ $jasa->keterangan }}</td>
            <td>
              <a href="{{ route('jasa.edit', $jasa->id) }}" class="btn btn-warning btn-sm">Edit</a>
              <form action="{{ route('jasa.destroy', $jasa->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center">Belum ada data jasa.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
