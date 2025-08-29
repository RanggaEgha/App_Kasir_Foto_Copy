@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-lg-5">
    <div class="card mb-3">
      <div class="card-header">Informasi PO</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5">No. Invoice</dt>
          <dd class="col-7">{{ $po->invoice_no }}</dd>

          <dt class="col-5">Tanggal</dt>
          <dd class="col-7">{{ optional($po->tanggal)->format('d M Y') }}</dd>

          <dt class="col-5">Metode Bayar</dt>
          <dd class="col-7 text-capitalize">{{ $po->metode_bayar }}</dd>

          <dt class="col-5">Supplier</dt>
          <dd class="col-7">{{ $po->supplier->name ?? '-' }}</dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <span>Daftar Item</span>
        <a href="{{ route('purchases.edit',$po) }}" class="btn btn-sm btn-secondary">Edit</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped mb-0">
            <thead>
              <tr>
                <th style="width:50px">#</th>
                <th>Barang</th>
                <th>Unit</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Harga</th>
                <th class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @foreach($po->items as $i => $it)
                <tr>
                  <td>{{ $i+1 }}</td>
                  <td>{{ $it->barang->nama ?? '-' }}</td>
                  <td>{{ $it->unit->kode ?? '-' }}</td>
                  <td class="text-end">{{ number_format($it->qty,0,',','.') }}</td>
                  <td class="text-end">@rupiah($it->unit_price)</td>
                  <td class="text-end">@rupiah($it->subtotal)</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th colspan="5" class="text-end">Subtotal</th>
                <th class="text-end">@rupiah($po->subtotal)</th>
              </tr>
              <tr>
                <th colspan="5" class="text-end">Diskon</th>
                <th class="text-end">- @rupiah($po->discount)</th>
              </tr>
              <tr>
                <th colspan="5" class="text-end">
                  PPN ({{ rtrim(rtrim(number_format($po->tax_percent,2,',','.'),'0'),',') }}%)
                </th>
                <th class="text-end">@rupiah($po->tax_amount)</th>
              </tr>
              <tr>
                <th colspan="5" class="text-end">Total</th>
                <th class="text-end">@rupiah($po->grand_total)</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
