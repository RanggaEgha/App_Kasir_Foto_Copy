{{-- resources/views/purchase/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">

  <h3 class="mb-4">Pembelian / Restok Stok</h3>

  {{-- ❶ Flash sukses --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form action="{{ route('purchase.store') }}" method="POST">
    @csrf

    {{-- ❷ Tabel dynamic baris pembelian --}}
    <table class="table align-middle" id="purchaseRows">
      <thead class="table-light">
        <tr>
          <th style="width: 32%">Barang</th>
          <th style="width: 15%">Unit</th>
          <th style="width: 11%">Qty</th>
          <th style="width: 18%">Harga&nbsp;Beli</th>
          <th style="width: 4%"></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <button type="button" class="btn btn-outline-primary mb-3"
            onclick="addPurchaseRow()">
      + Tambah Baris
    </button>

    {{-- ❸ Tombol simpan --}}
    <div>
      <button class="btn btn-success">Simpan Pembelian</button>
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
    </div>
  </form>
</div>

{{-- ❹ Template baris --}}
<template id="purchaseTpl">
<tr>
  {{-- Barang --}}
  <td>
    <select name="items[__I__][barang_id]" class="form-select">
      <option value="">— pilih barang —</option>
      @foreach($barangs as $b)
        <option value="{{ $b->id }}">{{ $b->nama }}</option>
      @endforeach
    </select>
  </td>

  {{-- Unit --}}
  <td>
    <select name="items[__I__][unit_id]" class="form-select">
      @foreach($units as $u)
        <option value="{{ $u->id }}">{{ $u->kode }}</option>
      @endforeach
    </select>
  </td>

  {{-- Qty --}}
  <td>
    <input type="number" class="form-control"
           name="items[__I__][qty]" min="1" step="1">
  </td>

  {{-- Harga beli --}}
  <td>
    <input type="number" class="form-control"
           name="items[__I__][harga_beli]" min="0" step="1">
  </td>

  {{-- Tombol hapus --}}
  <td class="text-center">
    <button type="button" class="btn btn-sm btn-outline-danger"
            onclick="removeRow(this)">
      &times;
    </button>
  </td>
</tr>
</template>

{{-- ❺ Script tambah / hapus baris --}}
<script>
let rowIndex = 0;

function addPurchaseRow() {
  const tpl  = document.querySelector('#purchaseTpl').innerHTML
                   .replace(/__I__/g, rowIndex++);
  document.querySelector('#purchaseRows tbody')
          .insertAdjacentHTML('beforeend', tpl);
}

function removeRow(btn) {
  btn.closest('tr').remove();
}

window.addEventListener('DOMContentLoaded', addPurchaseRow); // mulai dgn 1 baris
</script>
@endsection
