@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Purchase Orders</h4>
  <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Buat PO</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead>
        <tr>
          <th style="width:60px">#</th>
          <th>Tanggal</th>
          <th>Invoice</th>
          <th>Supplier</th>
          <th>Metode</th>
          <th>Ringkasan Item (Unit)</th>
          <th class="text-end">Total</th>
          <th style="width:140px" class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($orders as $i => $po)
          @php
            $firstUnit = optional(optional($po->items->first())->unit)->kode;
          @endphp
          <tr>
            <td>{{ ($orders->currentPage()-1)*$orders->perPage() + $i + 1 }}</td>
            <td>{{ optional($po->tanggal)->format('d M Y') }}</td>
            <td>{{ $po->invoice_no }}</td>
            <td>{{ $po->supplier->name ?? '-' }}</td>
            <td class="text-capitalize">{{ $po->metode_bayar }}</td>
            <td>
              {{ $po->items_count }} item
              @if($firstUnit) <span class="text-muted">({{ $firstUnit }})</span>@endif
            </td>
            <td class="text-end">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('purchases.show', $po) }}"><i class="bi bi-eye"></i></a>
              <a class="btn btn-sm btn-outline-secondary" href="{{ route('purchases.edit', $po) }}"><i class="bi bi-pencil"></i></a>
              <form action="{{ route('purchases.destroy', $po) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Hapus PO ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if(method_exists($orders, 'links'))
    <div class="card-footer">
      {{ $orders->links() }}
    </div>
  @endif
</div>
@endsection
