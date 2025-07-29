@extends('layouts.app')
@section('title', 'Kelola Unit & Harga')

@section('content')
<div class="card shadow-sm">
  <div class="card-header">
    <h5>Kelola Unit & Harga — {{ $barang->nama }}</h5>
  </div>

  <form method="POST"
        action="{{ route('barang.units.update', $barang->id) }}">
    @csrf @method('PUT')

    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Unit</th><th>Harga</th><th>Stok</th><th></th>
          </tr>
        </thead>
        <tbody id="unitRows">
          @foreach ($barang->units as $pivot)
            @include('barang.partials.unit-row', [
              'index' => $loop->index,
              'row'   => [
                  'unit_id' => $pivot->id,
                  'harga'   => $pivot->pivot->harga,
                  'stok'    => $pivot->pivot->stok,
              ],
              'allUnits' => $units
            ])
          @endforeach
        </tbody>
      </table>
    </div>

    <button type="button" class="btn btn-outline-primary mt-3"
            onclick="addUnitRow()">+ Tambah Unit</button>

    <div class="mt-4">
      <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>

@include('barang.partials.unit-js')
@endsection
