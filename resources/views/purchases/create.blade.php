@extends('layouts.app')

@section('content')
<div class="card">
  <div class="card-header">Tambah Purchase Order</div>
  <div class="card-body">
    <form method="POST" action="{{ route('purchases.store') }}">
      @csrf

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select" required>
            @foreach($suppliers ?? [] as $s)
              <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">No. Invoice</label>
          <input name="invoice_no" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Metode Bayar</label>
          <select name="metode_bayar" class="form-select">
            <option value="tunai">Tunai</option>
            <option value="transfer">Transfer</option>
            <option value="tempo">Tempo</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Diskon (Rp)</label>
          <input name="discount" class="form-control" placeholder="cth: 100.000">
        </div>

        <div class="col-md-4">
          <label class="form-label">PPN (%)</label>
          <input name="tax_percent" class="form-control" placeholder="cth: 11">
        </div>
      </div>

      <hr>

      <div class="table-responsive">
        <table class="table align-middle" id="tbl-items">
          <thead>
            <tr>
              <th style="width:50px">#</th>
              <th>Barang</th>
              <th>Unit</th>
              <th class="text-end" style="width:120px">Qty</th>
              <th class="text-end" style="width:180px">Harga</th>
              <th style="width:80px"></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="rownum">1</td>
              <td>
                <select name="items[0][barang_id]" class="form-select" required>
                  @foreach($barangs ?? [] as $b)
                    <option value="{{ $b->id }}">{{ $b->nama }}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <select name="items[0][unit_id]" class="form-select" required>
                  @foreach($units ?? [] as $u)
                    <option value="{{ $u->id }}">{{ $u->kode }}</option>
                  @endforeach
                </select>
              </td>
              <td><input name="items[0][qty]" type="number" min="1" value="1" class="form-control text-end"></td>
              <td><input name="items[0][price]" class="form-control text-end" placeholder="cth: 5.000.000"></td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" type="button" id="btnAdd">+ Item</button>
        <button class="btn btn-primary ms-auto" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- Template baris untuk JS --}}
<script type="text/template" id="row-template">
<tr>
  <td class="rownum"></td>
  <td>
    <select name="items[__INDEX__][barang_id]" class="form-select" required>
      @foreach($barangs ?? [] as $b)
        <option value="{{ $b->id }}">{{ $b->nama }}</option>
      @endforeach
    </select>
  </td>
  <td>
    <select name="items[__INDEX__][unit_id]" class="form-select" required>
      @foreach($units ?? [] as $u)
        <option value="{{ $u->id }}">{{ $u->kode }}</option>
      @endforeach
    </select>
  </td>
  <td><input name="items[__INDEX__][qty]" type="number" min="1" value="1" class="form-control text-end"></td>
  <td><input name="items[__INDEX__][price]" class="form-control text-end" placeholder="cth: 30.000"></td>
  <td class="text-end">
    <button type="button" class="btn btn-sm btn-outline-danger btn-remove">Hapus</button>
  </td>
</tr>
</script>

<script>
(function(){
  const tbody = document.querySelector('#tbl-items tbody');
  const tpl   = document.getElementById('row-template').innerHTML;

  function renumber() {
    [...tbody.querySelectorAll('tr')].forEach((tr, idx) => {
      const num = tr.querySelector('.rownum'); if (num) num.textContent = idx + 1;
      tr.querySelectorAll('select, input').forEach(el => {
        const name = el.getAttribute('name'); if (!name) return;
        el.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + idx + ']'));
      });
    });
  }
  function addRow() {
    const idx = tbody.children.length;
    const html = tpl.replaceAll('__INDEX__', idx);
    const wrapper = document.createElement('tbody');
    wrapper.innerHTML = html.trim();
    tbody.appendChild(wrapper.firstElementChild);
    renumber();
  }
  tbody.addEventListener('click', e => {
    if (e.target.classList.contains('btn-remove')) {
      e.target.closest('tr').remove();
      renumber();
    }
  });
  document.getElementById('btnAdd').addEventListener('click', addRow);

  // Format ribuan sederhana saat blur (backend tetap normalisasi)
  tbody.addEventListener('blur', function(e){
    if (e.target.name && e.target.name.endsWith('[price]')) {
      let v = (e.target.value || '').toString().replace(/[^0-9]/g,'');
      if (v === '') return;
      e.target.value = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
  }, true);
})();
</script>
@endsection
