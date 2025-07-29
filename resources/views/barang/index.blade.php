@extends('layouts.app')
@section('title','Daftar Barang')

@section('content')
<div id="app-barang-index" class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Daftar Barang</h5>
    <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm">+ Tambah</a>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered mb-0 align-middle">
        <thead class="table-light text-center">
          <tr>
            <th>#</th><th>Nama</th><th>Kategori</th>
            <th>Stok&nbsp;(pcs)</th><th>Harga&nbsp;(pcs)</th>
            <th>Stok&nbsp;(pack)</th><th>Harga&nbsp;(pack)</th>
            <th>Stok&nbsp;(lusin)</th><th>Harga&nbsp;(lusin)</th>
            <th>Keterangan</th><th>Aksi</th>
          </tr>
        </thead>

        <tbody>
        @php
          $fmt  = fn($n)=> is_null($n)? '' : 'Rp'.number_format($n,0,',','.');
          $get  = fn($u,$k,$f)=> optional($u->firstWhere('kode',$k))->pivot?->{$f};
          $cell = fn($v)=> $v===''? "<td class='empty-cell text-center'>—</td>"
                                  : "<td class='text-end'>$v</td>";
        @endphp

        @forelse($barangs as $i=>$b)
          @php
            $pcsS = $get($b->units,'pcs','stok');   $pcsH = $get($b->units,'pcs','harga');
            $pakS = $get($b->units,'pack','stok');  $pakH = $get($b->units,'pack','harga');
            $lusS = $get($b->units,'lusin','stok'); $lusH = $get($b->units,'lusin','harga');
          @endphp
          <tr>
            <td class="text-center">{{ $barangs->firstItem()+$i }}</td>
            <td>{{ $b->nama }}</td>
            <td>{{ $b->kategori }}</td>

            {!! $cell($pcsS) !!}{!! $cell($fmt($pcsH)) !!}
            {!! $cell($pakS) !!}{!! $cell($fmt($pakH)) !!}
            {!! $cell($lusS) !!}{!! $cell($fmt($lusH)) !!}

            <td>{!! $b->keterangan ? e(Str::limit($b->keterangan,40)) : '<span class="empty-cell">—</span>' !!}</td>

            <td class="text-center">
              <a href="{{ route('barang.edit',$b) }}" class="btn btn-sm btn-warning">Edit</a>
              <form action="{{ route('barang.destroy',$b) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Hapus {{ $b->nama }}?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="11" class="text-center py-4">Belum ada data</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer py-2">
    {{ $barangs->links() }}
  </div>
</div>
@endsection
