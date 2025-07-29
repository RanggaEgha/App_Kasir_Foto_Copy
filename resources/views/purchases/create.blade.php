@extends('layouts.app')
@section('title','Tambah Purchase Order')

@section('content')
<form action="{{ route('purchases.store') }}" method="POST" id="poForm">
 @csrf

 <x-card class="p-4">

  {{-- ALERT VALIDASI ----------------------------------------------------- --}}
  @if ($errors->any())
    <div class="alert alert-danger rounded-pill py-2 px-3 mb-3">
      <ul class="mb-0 small">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- HEADER ------------------------------------------------------------- --}}
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
      <select name="supplier_id" class="form-select" required>
        <option value="" disabled selected>- Pilih Supplier -</option>
        @foreach($suppliers as $id => $nama)
          <option value="{{ $id }}" {{ old('supplier_id')==$id?'selected':'' }}>{{ $nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-semibold">No. Faktur <span class="text-danger">*</span></label>
      <input type="text" name="invoice_no" class="form-control"
             value="{{ old('invoice_no') }}" required>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-semibold">Tanggal</label>
      <input type="datetime-local" name="purchase_date"
             value="{{ old('purchase_date', now()->format('Y-m-d\TH:i')) }}"
             class="form-control">
    </div>
  </div>

  {{-- TABEL ITEM --------------------------------------------------------- --}}
  <h6 class="fw-bold mb-2">Daftar Barang <span class="text-danger">*</span></h6>
  <table class="table table-sm align-middle" id="itemsTable">
    <thead class="table-light">
      <tr>
        <th style="width:45%">Barang</th>
        <th style="width:10%">Qty</th>
        <th style="width:20%">Harga</th>
        <th style="width:20%">Subtotal</th>
        <th style="width:5%"></th>
      </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
      <tr>
        <td colspan="5">
          <button type="button" class="btn btn-outline-primary btn-sm rounded-pill"
                  onclick="addRow()">
            <i class="bi bi-plus-circle"></i> Tambah Baris
          </button>
        </td>
      </tr>
    </tfoot>
  </table>

  {{-- PEMBAYARAN & TOTAL ------------------------------------------------- --}}
  <div class="row g-3 mt-4">
    <div class="col-md-3">
      <label class="form-label fw-semibold">Metode Bayar</label>
      <select name="payment_method" class="form-select">
        <option value="cash"     {{ old('payment_method')=='cash'?'selected':'' }}>Cash</option>
        <option value="transfer" {{ old('payment_method')=='transfer'?'selected':'' }}>Transfer</option>
        <option value="qris"     {{ old('payment_method')=='qris'?'selected':'' }}>QRIS</option>
        <option value="credit"   {{ old('payment_method')=='credit'?'selected':'' }}>Credit</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label fw-semibold">Nominal Dibayar</label>
      <input type="number" name="amount_paid" step="0.01"
             value="{{ old('amount_paid') }}" class="form-control">
    </div>
    <div class="col-md-6 text-end align-self-end">
      <h5 class="fw-semibold mb-1">Total</h5>
      <h2 id="grandTotal" class="mb-0">Rp 0</h2>
    </div>
  </div>

  {{-- CATATAN & TOMBOL SIMPAN ------------------------------------------- --}}
  <div class="mt-4">
    <label class="form-label fw-semibold">Catatan</label>
    <textarea name="notes" rows="2" class="form-control rounded-4">{{ old('notes') }}</textarea>

    <button class="btn btn-primary rounded-pill px-5 mt-3">
      <i class="bi bi-save me-1"></i> Simpan
    </button>
  </div>

 </x-card>
</form>

{{-- SCRIPT DINAMIS ------------------------------------------------------- --}}
@push('scripts')
<script>
  const barangOptions = @json($barangs);   // {id: "nama barang", ...}

  function addRow(prefill = {}) {
    const tbody = document.querySelector('#itemsTable tbody');
    const idx   = tbody.rows.length;
    const tr    = document.createElement('tr');

    tr.innerHTML = `
      <td>
        <select name="items[${idx}][barang_id]" class="form-select" required>
          <option value="" disabled selected>- pilih -</option>
          ${Object.entries(barangOptions).map(([id,nama]) =>
              `<option value="${id}">${nama}</option>`).join('')}
        </select>
      </td>
      <td>
        <input type="number" name="items[${idx}][qty]" class="form-control text-end"
               min="1" value="${prefill.qty ?? 1}"
               oninput="calcSubtotal(this)">
      </td>
      <td>
        <input type="number" name="items[${idx}][price]" class="form-control text-end"
               min="0" step="0.01" value="${prefill.price ?? 0}"
               oninput="calcSubtotal(this)">
      </td>
      <td class="text-end fw-semibold">0</td>
      <td class="text-center">
        <button type="button" class="btn btn-link text-danger p-0"
                onclick="this.closest('tr').remove(); updateTotal();">
          <i class="bi bi-x-lg"></i>
        </button>
      </td>`;
    tbody.appendChild(tr);
  }

  function calcSubtotal(el) {
    const tr    = el.closest('tr');
    const qty   = parseFloat(tr.querySelector('[name$="[qty]"]').value)   || 0;
    const price = parseFloat(tr.querySelector('[name$="[price]"]').value) || 0;
    const sub   = qty * price;
    tr.children[3].textContent = sub.toLocaleString('id-ID');
    updateTotal();
  }

  function updateTotal() {
    let total = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
      const qty   = parseFloat(tr.querySelector('[name$="[qty]"]').value)   || 0;
      const price = parseFloat(tr.querySelector('[name$="[price]"]').value) || 0;
      total += qty * price;
    });
    document.getElementById('grandTotal').textContent =
        'Rp ' + total.toLocaleString('id-ID');
  }

  // Tambahkan baris pertama saat page load
  addRow();
</script>
@endpush
@endsection
