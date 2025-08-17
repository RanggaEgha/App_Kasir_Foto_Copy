@extends('layouts.app')
@section('title','Tambah Transaksi')

@section('content')
@php
  /* JSON unit untuk JS */
  $jsonUnits = $barangs->mapWithKeys(fn($b) =>
      [$b->id => $b->units->map(fn($u) => [
          'id'   => $u->id,
          'kode' => $u->kode,
          'konversi' => $u->konversi,
          'harga' => $u->pivot->harga,
          'stok'  => $u->pivot->stok,
      ])]
  );
@endphp

<style>
  td.text-end,.stok-show{white-space:nowrap}
  input[readonly]{background:#f7f7f7}
  .quick-btn{min-width:70px}
</style>

<div class="container-fluid px-4">
  <h1 class="mt-4">Tambah Transaksi</h1>

  @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">
      @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul></div>
  @endif

  {{-- ===== BADGE STATUS & PAYMENT (tambahan) ===== --}}
  <div class="mb-3">
    <span class="badge bg-secondary">Draft</span>
    <span class="badge bg-secondary">Unpaid</span>
  </div>
  {{-- ===== /BADGE ===== --}}

  <form id="frmTransaksi" method="POST" action="{{ route('transaksi.store') }}">
    @csrf

    {{-- ================= TABLE ITEMS ================= --}}
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between">
        <span>Daftar Item</span>
        <button type="button" id="btn-add" class="btn btn-sm btn-secondary">+ Tambah Item</button>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table id="tbl-items" class="table mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:3%">#</th>
                <th style="width:11%">Jenis</th>
                <th>Barang / Jasa</th>
                <th style="width:10%">Unit</th>
                <th style="width:8%">Qty</th>
                <th style="width:11%">Harga (Rp)</th>
                <th style="width:13%">Subtotal (Rp)</th>
                <th style="width:11%">Stok</th>
                <th style="width:4%"></th>
              </tr>
            </thead>
            <tbody>
              {{-- ==== ROW TEMPLATE ==== --}}
              <tr>
                <td class="text-center">1</td>

                {{-- Jenis --}}
                <td>
                  <select name="tipe_item[]" class="form-select jenis">
                    <option value="barang">Barang</option>
                    <option value="jasa">Jasa</option>
                  </select>
                </td>

                {{-- Barang / Jasa --}}
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
                      <option value="{{ $j->id }}"
                              data-harga="{{ $j->harga_per_satuan }}"
                              data-satuan="{{ $j->satuan }}">
                        {{ $j->nama }}
                      </option>
                    @endforeach
                  </select>
                </td>

                {{-- Unit --}}
                <td>
                  <select name="unit_id[]" class="form-select select-unit"></select>
                  <span class="unit-show d-none"></span>
                </td>

                {{-- Qty --}}
                <td><input type="number" min="1" value="1" name="jumlah[]" class="form-control qty"></td>

                {{-- Harga & Subtotal --}}
                <td><input name="harga_satuan[]" class="form-control harga text-end" value="0" readonly></td>
                <td><input name="subtotal[]"     class="form-control subtotal text-end" value="0" readonly></td>

                {{-- Stok --}}
                <td class="stok-show text-end">0</td>

                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-danger btn-remove">X</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ================= TOTAL ================= --}}
    <div class="row g-3 mb-3">
      <div class="col-md-4 ms-auto">
        <label class="form-label fw-bold">Total (Rp)</label>
        <input id="total-view" class="form-control fw-bold text-end" value="0" readonly>
      </div>
    </div>

    {{-- ================= PEMBAYARAN ================= --}}
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Metode Bayar</label>
        <select name="metode_bayar" class="form-select">
          <option value="cash">Cash</option>
          <option value="debit" disabled>Debit</option>
          <option value="dana"  disabled>Dana</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Dibayar (Rp)</label>
        <input id="dibayar_view" class="form-control text-end">
        <input type="hidden" id="dibayar" name="dibayar">
      </div>
      <div class="col-md-3">
        <label class="form-label">Kembalian (Rp)</label>
        <input id="kembalian-view" class="form-control fw-bold text-end" value="0" readonly>
      </div>
    </div>

    {{-- QUICK CASH --}}
    <div class="my-3">
      <label class="form-label">Pilihan cepat</label>
      <div id="quick-cash" class="d-flex flex-wrap gap-2 mb-2"></div>
      <button type="button" id="btn-pas" class="btn btn-success btn-sm me-2">Uang Pas</button>
      <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-sm">Reset</button>
    </div>

    {{-- ACTION --}}
    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary">← Kembali</a>
      <button class="btn btn-primary">Simpan Transaksi</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
/* ===== Helper ===== */
const rupiah = n => n.toLocaleString('id-ID');
const bersih  = s => +String(s||'').replaceAll('.','') || 0;

/* Units map untuk JS */
const unitsMap = @json($jsonUnits);

/* ====== Table ====== */
const tbody = document.querySelector('#tbl-items tbody');

function reindex() {
  [...tbody.rows].forEach((tr,i)=>tr.cells[0].textContent=i+1);
}

function loadUnitDropdown(row, barangId){
  const sel = row.querySelector('.select-unit');
  sel.innerHTML = '';
  (unitsMap[barangId]||[]).forEach(u=>{
    const o=document.createElement('option');
    o.value=u.id; o.textContent=u.kode;
    o.dataset.harga=u.harga;
    o.dataset.stok=u.stok;
    sel.appendChild(o);
  });
}

function refreshRow(row){
  const isBarang = row.querySelector('.jenis').value === 'barang';
  const qtyEl    = row.querySelector('.qty');
  const qty      = +qtyEl.value || 1;

  let harga=0, stok=0;

  if(isBarang){
    const barangId = row.querySelector('.select-barang').value;
    const unitSel  = row.querySelector('.select-unit');
    if(barangId && unitSel.selectedOptions.length){
      const opt = unitSel.selectedOptions[0];
      harga = +opt.dataset.harga || 0;
      stok  = +opt.dataset.stok  || 0;
      row.querySelector('.stok-show').textContent = rupiah(stok)+' '+opt.textContent;
    } else {
      row.querySelector('.stok-show').textContent = '0';
    }
  }else{
    const jasaOpt = row.querySelector('.select-jasa').selectedOptions[0] || {};
    harga = +jasaOpt.dataset.harga || 0;
    row.querySelector('.stok-show').textContent = '—';
  }

  row.querySelector('.harga').value    = rupiah(harga);
  row.querySelector('.subtotal').value = rupiah(harga*qty);

  if(isBarang && qty>stok) qtyEl.classList.add('is-invalid');
  else qtyEl.classList.remove('is-invalid');

  refreshTotal();
}

function refreshTotal(){
  let total=0;
  document.querySelectorAll('.subtotal').forEach(i=> total += bersih(i.value));
  document.getElementById('total-view').value = rupiah(total);

  const paid  = bersih(document.getElementById('dibayar_view').value);
  document.getElementById('dibayar').value = paid;
  document.getElementById('kembalian-view').value = rupiah(Math.max(0, paid - total));
}

/* ===== Event handler ===== */
tbody.addEventListener('change', e=>{
  const row = e.target.closest('tr');

  if(e.target.classList.contains('jenis')){
    const isB = e.target.value==='barang';
    row.querySelector('.select-barang').classList.toggle('d-none',!isB);
    row.querySelector('.select-unit' ).classList.toggle('d-none',!isB);
    row.querySelector('.select-jasa').classList.toggle('d-none', isB);
  }
  if(e.target.classList.contains('select-barang')){
    loadUnitDropdown(row, e.target.value);
  }
  if(e.target.matches('.jenis,.select-barang,.select-jasa,.select-unit')){
    refreshRow(row);
  }
});

tbody.addEventListener('input', e=>{
  if(e.target.classList.contains('qty')) refreshRow(e.target.closest('tr'));
});

/* Tambah baris */
document.getElementById('btn-add').onclick = () => {
  const tr = tbody.rows[0].cloneNode(true);
  tr.querySelectorAll('input').forEach(i=>{
    i.value = i.classList.contains('qty') ? 1 : 0;
    i.classList.remove('is-invalid');
  });
  tr.querySelectorAll('select').forEach(s=>s.selectedIndex = 0);
  tr.querySelector('.select-jasa').classList.add('d-none');
  tbody.appendChild(tr);
  reindex(); refreshRow(tr);
};

/* Hapus baris */
tbody.addEventListener('click', e=>{
  if(e.target.classList.contains('btn-remove') && tbody.rows.length>1){
    e.target.closest('tr').remove();
    reindex(); refreshTotal();
  }
});

/* Quick cash setup */
const LS='quick_cash_vals';
let quick=JSON.parse(localStorage.getItem(LS)||'[1000,2000,5000,10000,20000,50000]');
const qcWrap=document.getElementById('quick-cash');
function renderQuick(){
  qcWrap.innerHTML='';
  quick.slice(0,8).sort((a,b)=>a-b).forEach(v=>{
    const b=document.createElement('button');
    b.type='button'; b.className='btn btn-outline-secondary btn-sm quick-btn';
    b.textContent=rupiah(v);
    b.onclick=()=>{
      const inp=document.getElementById('dibayar_view');
      inp.value = rupiah(bersih(inp.value)+v); refreshTotal();
      quick=[v,...quick.filter(x=>x!==v)];
      localStorage.setItem(LS,JSON.stringify(quick.slice(0,12)));
    };
    qcWrap.appendChild(b);
  });
}
renderQuick();

/* Uang pas & reset */
document.getElementById('btn-pas').onclick = () => {
  document.getElementById('dibayar_view').value = document.getElementById('total-view').value;
  refreshTotal();
};
document.getElementById('btn-reset').onclick = () => {
  document.getElementById('dibayar_view').value = '';
  refreshTotal();
};

/* Blok submit jika qty invalid */
document.getElementById('frmTransaksi').addEventListener('submit', e=>{
  if(tbody.querySelector('.is-invalid')){
    alert('Qty melebihi stok tersedia.'); e.preventDefault();
  }
});

/* Init baris pertama */
loadUnitDropdown(tbody.rows[0], tbody.rows[0].querySelector('.select-barang').value);
refreshRow(tbody.rows[0]);
</script>
@endpush
