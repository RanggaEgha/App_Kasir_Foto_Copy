@extends('layouts.app')
@section('title','Edit Transaksi')

@section('content')
@php
  $jsonUnits = $barangs->mapWithKeys(fn($b) =>
      [$b->id => $b->units->map(fn($u) => [
          'id'=>$u->id,'kode'=>$u->kode,'konversi'=>$u->konversi,
          'harga'=>$u->pivot->harga,'stok'=>$u->pivot->stok,
      ])]
  );
  $jsonGudangUnits = $gudangs->mapWithKeys(fn($g) =>
      [$g->id => $g->units->map(fn($u) => [
          'id'=>$u->id,'kode'=>$u->kode,'konversi'=>$u->konversi,
          'harga'=>$u->pivot->harga,'stok'=>$u->pivot->stok,
      ])]
  );

  // siapkan data item existing
  $itemsData = $transaksi->items->map(function($it){
      return [
        'tipe'      => $it->tipe_item,
        'barang_id' => $it->barang_id,
        'jasa_id'   => $it->jasa_id,
        'gudang_id' => $it->gudang_item_id,
        'unit_id'   => $it->unit_id,
        'jumlah'    => (int)$it->jumlah,
        'harga'     => (int)$it->harga_satuan,
        'nama'      => $it->nama_manual,
        'satuan'    => $it->satuan_manual,
      ];
  })->values();
@endphp

<form method="post" action="{{ route('transaksi.update', $transaksi->id) }}" class="card shadow-sm">
  @csrf
  @method('PUT')

  <div class="card-body">
    {{-- ===== BADGE STATUS & PAYMENT (tambahan) ===== --}}
    @php
      $status = $transaksi->status ?? 'posted';
      $paymentStatus = $transaksi->payment_status ?? 'unpaid';
      $payClass = ['paid'=>'success','partial'=>'warning','unpaid'=>'secondary'][$paymentStatus] ?? 'secondary';
    @endphp
    <div class="mb-3">
      <span class="badge bg-{{ $status === 'void' ? 'danger' : 'success' }}">
        {{ ucfirst($status) }}
      </span>
      <span class="badge bg-{{ $payClass }}">
        {{ ucfirst($paymentStatus) }}
      </span>
    </div>
    {{-- ===== /BADGE ===== --}}

    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label">Metode Bayar</label>
        <select name="metode_bayar" class="form-select">
          <option value="cash" {{ $transaksi->metode_bayar==='cash'?'selected':'' }}>Cash</option>
          <option value="transfer" {{ $transaksi->metode_bayar==='transfer'?'selected':'' }}>Transfer</option>
          <option value="qris" {{ $transaksi->metode_bayar==='qris'?'selected':'' }}>QRIS</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Dibayar</label>
        <input type="text" name="dibayar" class="form-control text-end"
               value="{{ number_format((int)$transaksi->dibayar,0,',','.') }}"
               oninput="this.value=rupiahInput(this.value)">
      </div>
      <div class="col-md-6 text-end">
        <div class="fs-5">Total: <span id="grand-total">Rp0</span></div>
      </div>
    </div>

    <hr>

    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="tbl-items">
        <thead class="table-light">
          <tr>
            <th style="width:120px">Jenis</th>
            <th>Nama</th>
            <th style="width:140px">Unit</th>
            <th style="width:90px">Qty</th>
            <th style="width:140px">Harga</th>
            <th style="width:140px">Subtotal</th>
            <th style="width:90px">Stok</th>
            <th style="width:60px">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <td colspan="8">
              <button type="button" class="btn btn-sm btn-primary" id="btn-add">+ Tambah Baris</button>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="card-footer d-flex justify-content-between">
    <a href="{{ route('transaksi.show', $transaksi->id) }}" class="btn btn-light">Batal</a>
    <button class="btn btn-success">Simpan Perubahan</button>
  </div>
</form>

{{-- TEMPLATE ROW (sama dengan create) --}}
<template id="row-tpl">
  <tr>
    <td>
      <select name="tipe_item[]" class="form-select jenis">
        <option value="barang">Barang</option>
        <option value="jasa">Jasa</option>
        <option value="gudang">Gudang</option>
        <option value="manual">Manual</option>
      </select>
    </td>
    <td>
      <select name="barang_id[]" class="form-select select-barang">
        <option value="">— pilih barang —</option>
        @foreach ($barangs as $b)
          <option value="{{ $b->id }}">{{ $b->nama }}</option>
        @endforeach
      </select>

      <select name="jasa_id[]" class="form-select d-none select-jasa">
        <option value="">— pilih jasa —</option>
        @foreach ($jasas as $j)
          <option value="{{ $j->id }}" data-harga="{{ $j->harga }}" data-satuan="{{ $j->satuan ?? '-' }}">{{ $j->nama }}</option>
        @endforeach
      </select>

      <select name="gudang_item_id[]" class="form-select d-none select-gudang">
        <option value="">— pilih item gudang —</option>
        @foreach ($gudangs as $g)
          <option value="{{ $g->id }}">{{ $g->nama }}{{ $g->sku ? ' - '.$g->sku : '' }}</option>
        @endforeach
      </select>

      <div class="d-none manual-wrap">
        <input type="text" name="nama_manual[]" class="form-control mb-1" placeholder="Nama manual">
        <input type="text" name="satuan_manual[]" class="form-control" placeholder="Satuan (mis. pcs)">
      </div>
    </td>

    <td>
      <select name="unit_id[]" class="form-select select-unit"></select>
      <span class="unit-show d-none"></span>
    </td>

    <td><input type="number" min="1" value="1" name="jumlah[]" class="form-control qty"></td>
    <td><input name="harga_satuan[]" class="form-control harga text-end" value="0" readonly></td>
    <td><input name="subtotal[]" class="form-control subtotal text-end" value="0" readonly></td>
    <td class="stok-show text-end">0</td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove">X</button></td>
  </tr>
</template>

<script>
const barangUnitsMap  = @json($jsonUnits);
const gudangUnitsMap  = @json($jsonGudangUnits);
const existingItems   = @json($itemsData);

const tbody = document.querySelector('#tbl-items tbody');
const tpl   = document.querySelector('#row-tpl');

function rupiah(n){ n=Math.max(0,+n||0); return 'Rp'+n.toLocaleString('id-ID'); }
function rupiahInput(s){ s=(s||'').toString().replace(/[^0-9]/g,''); if(!s) return '0'; return s.replace(/^0+/,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
function bersih(s){ return +(String(s||'').replace(/[^0-9]/g,''))||0; }

function loadUnitDropdown(row, barangId){
  const sel=row.querySelector('.select-unit'); sel.innerHTML='';
  (barangUnitsMap[barangId]||[]).forEach(u=>{
    const o=document.createElement('option');
    o.value=u.id; o.textContent=u.kode; o.dataset.harga=u.harga; o.dataset.stok=u.stok;
    sel.appendChild(o);
  });
}
function loadGudangUnitDropdown(row, gid){
  const sel=row.querySelector('.select-unit'); sel.innerHTML='';
  (gudangUnitsMap[gid]||[]).forEach(u=>{
    const o=document.createElement('option');
    o.value=u.id; o.textContent=u.kode; o.dataset.harga=u.harga; o.dataset.stok=u.stok;
    sel.appendChild(o);
  });
}

function refreshRow(row){
  const tipe = row.querySelector('.jenis').value;
  const selBarang=row.querySelector('.select-barang');
  const selJasa  =row.querySelector('.select-jasa');
  const selGudang=row.querySelector('.select-gudang');
  const manualWrap=row.querySelector('.manual-wrap');
  const selUnit=row.querySelector('.select-unit');
  const unitShow=row.querySelector('.unit-show');
  const qtyEl=row.querySelector('.qty');
  const hargaEl=row.querySelector('.harga');
  const subEl=row.querySelector('.subtotal');
  const stokCell=row.querySelector('.stok-show');

  selBarang.classList.toggle('d-none', !(tipe==='barang'));
  selJasa.classList.toggle('d-none',   !(tipe==='jasa'));
  selGudang.classList.toggle('d-none', !(tipe==='gudang'));
  manualWrap.classList.toggle('d-none',!(tipe==='manual'));

  if (tipe==='barang') {
    unitShow.classList.add('d-none'); selUnit.classList.remove('d-none');
    loadUnitDropdown(row, selBarang.value);
    hargaEl.readOnly=true;
  } else if (tipe==='gudang') {
    unitShow.classList.add('d-none'); selUnit.classList.remove('d-none');
    loadGudangUnitDropdown(row, selGudang.value);
    hargaEl.readOnly=true;
  } else if (tipe==='jasa') {
    unitShow.classList.remove('d-none'); selUnit.classList.add('d-none');
    unitShow.textContent = selJasa.selectedOptions[0]?.dataset.satuan || '-';
    hargaEl.readOnly=true;
  } else {
    unitShow.classList.remove('d-none'); selUnit.classList.add('d-none');
    unitShow.textContent = row.querySelector('[name="satuan_manual[]"]').value || '-';
    hargaEl.readOnly=false;
  }

  let harga=0,stok=0;
  if (tipe==='barang'||tipe==='gudang') {
    const opt = selUnit.selectedOptions[0]; harga=+opt?.dataset.harga||0; stok=+opt?.dataset.stok||0;
  } else if (tipe==='jasa') {
    harga = +(selJasa.selectedOptions[0]?.dataset.harga||0);
  } else {
    harga = bersih(hargaEl.value);
  }

  hargaEl.value = rupiah(harga);
  const qty = +qtyEl.value||1;
  subEl.value = rupiah(harga*qty);
  stokCell.textContent = stok;

  const isStocked = (tipe==='barang'||tipe==='gudang');
  qtyEl.classList.toggle('is-invalid', isStocked && qty > stok);

  refreshTotal();
}

function refreshTotal(){
  let total=0;
  tbody.querySelectorAll('tr').forEach(tr=> total += bersih(tr.querySelector('.subtotal').value));
  document.getElementById('grand-total').textContent = rupiah(total);
}

function addRow(prefill=null){
  const row = tpl.content.firstElementChild.cloneNode(true);
  tbody.appendChild(row);

  if (prefill) {
    row.querySelector('.jenis').value = prefill.tipe;

    if (prefill.tipe==='barang') {
      row.querySelector('.select-barang').value = String(prefill.barang_id||'');
      loadUnitDropdown(row, prefill.barang_id);
      row.querySelector('.select-unit').value = String(prefill.unit_id||'');
    } else if (prefill.tipe==='gudang') {
      row.querySelector('.select-gudang').value = String(prefill.gudang_id||'');
      loadGudangUnitDropdown(row, prefill.gudang_id);
      row.querySelector('.select-unit').value = String(prefill.unit_id||'');
    } else if (prefill.tipe==='jasa') {
      row.querySelector('.select-jasa').value = String(prefill.jasa_id||'');
    } else { // manual
      const wrap=row.querySelector('.manual-wrap');
      wrap.querySelector('[name="nama_manual[]"]').value = prefill.nama||'';
      wrap.querySelector('[name="satuan_manual[]"]').value = prefill.satuan||'';
      row.querySelector('.harga').value = rupiah(prefill.harga||0);
    }

    row.querySelector('.qty').value = prefill.jumlah||1;
  }

  refreshRow(row);
}

document.getElementById('btn-add').addEventListener('click', ()=>addRow());
document.addEventListener('click', e=>{
  if(e.target.classList.contains('btn-remove')){
    e.target.closest('tr')?.remove();
    refreshTotal();
  }
});
tbody.addEventListener('change', e=>{
  const row=e.target.closest('tr'); if(!row) return;
  if (e.target.classList.contains('jenis')) refreshRow(row);
  if (e.target.classList.contains('select-barang')) refreshRow(row);
  if (e.target.classList.contains('select-gudang')) refreshRow(row);
  if (e.target.classList.contains('select-unit')) refreshRow(row);
  if (e.target.classList.contains('select-jasa')) refreshRow(row);
  if (e.target.name==='nama_manual[]' || e.target.name==='satuan_manual[]') refreshRow(row);
  if (e.target.classList.contains('qty')) refreshRow(row);
});
tbody.addEventListener('input', e=>{
  if (e.target.classList.contains('harga')) {
    const row=e.target.closest('tr');
    if (row?.querySelector('.jenis').value==='manual') {
      e.target.value = rupiahInput(e.target.value);
      refreshRow(row);
    }
  }
});

// Inisialisasi dari item existing
if (existingItems.length) {
  existingItems.forEach(it=>addRow(it));
} else {
  addRow(); // fallback
}
</script>
@endsection
