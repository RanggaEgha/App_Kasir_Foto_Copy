@extends('layouts.app')
@section('title','Tambah Transaksi')

@section('content')
<style>
  td.text-end, .stok-show { white-space: nowrap; }
  input[readonly]{background:#f7f7f7}
  .quick-btn{min-width:70px}
</style>

<div class="container-fluid px-4">
  <h1 class="mt-4">Tambah Transaksi</h1>

  {{-- flash error --}}
  @if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">
      @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul></div>
  @endif

  <form id="frmTransaksi" method="POST" action="{{ route('transaksi.store') }}">
    @csrf

    {{-- ========== TABEL ITEM ========== --}}
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
                <th style="width:9%">Tipe&nbsp;Qty</th>
                <th style="width:8%">Qty</th>
                <th style="width:8%">Unit</th>
                <th style="width:11%">Harga (Rp)</th>
                <th style="width:13%">Subtotal (Rp)</th>
                <th style="width:11%">Stok</th>
                <th style="width:4%"></th>
              </tr>
            </thead>
            <tbody>
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
                      <option value="{{ $b->id }}"
                        data-harga_satuan="{{ $b->harga_satuan }}"
                        data-harga_paket ="{{ $b->harga_paket }}"
                        data-stok_satuan ="{{ $b->stok_satuan }}"
                        data-stok_paket  ="{{ $b->stok_paket }}"
                        data-isi         ="{{ $b->isi_per_paket }}"
                        data-satuan      ="{{ $b->satuan }}">
                        {{ $b->nama }}
                      </option>
                    @endforeach
                  </select>

                  <select name="jasa_id[]" class="form-select d-none select-jasa">
                    <option value="">— pilih jasa —</option>
                    @foreach ($jasas as $j)
                      <option value="{{ $j->id }}"
                              data-harga ="{{ $j->harga_per_satuan }}"
                              data-satuan="{{ $j->satuan }}">
                        {{ $j->nama }}
                      </option>
                    @endforeach
                  </select>
                </td>

                {{-- Tipe Qty --}}
                <td class="td-tipe">
                  <select name="tipe_qty[]" class="form-select tipe-qty">
                    <option value="satuan">Satuan</option>
                    <option value="paket">Paket</option>
                  </select>
                </td>

                {{-- Qty --}}
                <td><input type="number" min="1" value="1" name="jumlah[]" class="form-control qty"></td>

                {{-- Unit --}}
                <td class="unit-show">pcs</td>

                {{-- Harga & Subtotal --}}
                <td><input name="harga_satuan[]" class="form-control harga text-end"    value="0" readonly></td>
                <td><input name="subtotal[]"     class="form-control subtotal text-end" value="0" readonly></td>

                {{-- Stok --}}
                <td class="stok-show text-end">0 pcs</td>

                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-danger btn-remove">X</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ========== TOTAL & PEMBAYARAN ========== --}}
    <div class="row g-3 mb-3">
      <div class="col-md-4 ms-auto">
        <label class="form-label fw-bold">Total (Rp)</label>
        <input id="total-view" class="form-control fw-bold text-end" value="0" readonly>
      </div>
    </div>

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

    <div class="my-3">
      <label class="form-label">Pilihan cepat</label>
      <div id="quick-cash" class="d-flex flex-wrap gap-2 mb-2"></div>
      <button type="button" id="btn-pas"   class="btn btn-success btn-sm me-2">Uang&nbsp;Pas</button>
      <button type="button" id="btn-reset" class="btn btn-outline-secondary btn-sm">Reset</button>
    </div>

    {{-- ========== TOMBOL AKSI ========== --}}
    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary">← Kembali</a>
      <button class="btn btn-primary">Simpan Transaksi</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
/* ===== helper format ===== */
const rupiah = n => n.toLocaleString('id-ID');
const bersih = s => +s.replaceAll('.','') || 0;
const SELECT_HTML =
`<select name="tipe_qty[]" class="form-select tipe-qty">
     <option value="satuan">Satuan</option>
     <option value="paket">Paket</option>
 </select>`;

/* ===== refresh baris & total ===== */
function refreshRow(row){
  const jenis = row.querySelector('.jenis').value;
  const qtyEl = row.querySelector('.qty');
  const qty   = +qtyEl.value || 1;
  let harga=0, stok=0, unit='-';

  if(jenis==='barang'){
    const tipe=row.querySelector('.tipe-qty').value;
    const opt =row.querySelector('.select-barang').selectedOptions[0]||{};
    harga=+opt.dataset['harga_'+tipe]||0;
    stok =+opt.dataset['stok_'+tipe] ||0;
    unit =tipe==='paket'?'paket':(opt.dataset.satuan||'-');
    row.querySelector('.stok-show').textContent=`${rupiah(stok)} ${unit}`;
  }else{
    const opt=row.querySelector('.select-jasa').selectedOptions[0]||{};
    harga=+opt.dataset.harga||0;
    unit =opt.dataset.satuan||'-';
    row.querySelector('.stok-show').textContent='—';
  }

  row.querySelector('.unit-show').textContent=unit;
  row.querySelector('.harga').value   = rupiah(harga);
  row.querySelector('.subtotal').value= rupiah(harga*qty);

  if(jenis==='barang' && qty>stok) qtyEl.classList.add('is-invalid');
  else qtyEl.classList.remove('is-invalid');

  refreshTotal();
}

function refreshTotal(){
  let total=0;
  document.querySelectorAll('.subtotal').forEach(i=> total+=bersih(i.value));
  document.getElementById('total-view').value=rupiah(total);

  const paid=bersih(document.getElementById('dibayar_view').value);
  document.getElementById('dibayar').value=paid;
  document.getElementById('kembalian-view').value=rupiah(Math.max(0,paid-total));
}

/* ===== quick cash ===== */
const LS_KEY='quick_cash_vals';
let quickVals=JSON.parse(localStorage.getItem(LS_KEY)||'[1000,2000,5000,10000,20000,50000]');
const quickWrap=document.getElementById('quick-cash');
function renderQuick(){
  quickWrap.innerHTML='';
  quickVals.slice(0,8).sort((a,b)=>a-b).forEach(v=>{
    const b=document.createElement('button');
    b.type='button'; b.className='btn btn-outline-secondary btn-sm quick-btn';
    b.textContent=rupiah(v);
    b.onclick=()=>{
      const view=document.getElementById('dibayar_view');
      view.value=rupiah(bersih(view.value)+v);
      refreshTotal();
      quickVals=[v,...quickVals.filter(x=>x!==v)];
      localStorage.setItem(LS_KEY,JSON.stringify(quickVals.slice(0,12)));
    };
    quickWrap.appendChild(b);
  });
}
renderQuick();

/* uang pas / reset */
document.getElementById('btn-pas').onclick=()=>{
  const tot=bersih(document.getElementById('total-view').value);
  document.getElementById('dibayar_view').value=rupiah(tot);
  refreshTotal();
};
document.getElementById('btn-reset').onclick=()=>{
  document.getElementById('dibayar_view').value='';
  refreshTotal();
};

/* ===== tabel dinamis ===== */
const tbody=document.querySelector('#tbl-items tbody');
function reindex(){[...tbody.rows].forEach((tr,i)=>tr.cells[0].textContent=i+1);}

function setTipeCell(row,isBarang){
  const td=row.querySelector('.td-tipe');
  td.innerHTML = isBarang ? SELECT_HTML : '—';
}

tbody.addEventListener('change',e=>{
  const row=e.target.closest('tr');
  if(e.target.classList.contains('jenis')){
    const isB=e.target.value==='barang';
    row.querySelector('.select-barang').classList.toggle('d-none',!isB);
    row.querySelector('.select-jasa').classList.toggle('d-none', isB);
    setTipeCell(row,isB);
  }
  if(e.target.matches('.jenis,.select-barang,.select-jasa,.tipe-qty')) refreshRow(row);
});

tbody.addEventListener('input',e=>{
  if(e.target.classList.contains('qty')) refreshRow(e.target.closest('tr'));
});

/* tambah baris */
document.getElementById('btn-add').onclick=()=>{
  const tr=tbody.rows[0].cloneNode(true);
  tr.querySelectorAll('input').forEach(i=>{
    i.value=i.classList.contains('qty')?1:0;
    i.classList.remove('is-invalid');
  });
  tr.querySelectorAll('select').forEach(s=>s.selectedIndex=0);
  tr.querySelector('.select-jasa').classList.add('d-none');
  tbody.appendChild(tr); reindex(); setTipeCell(tr,true); refreshRow(tr);
};

/* hapus baris */
tbody.addEventListener('click',e=>{
  if(e.target.classList.contains('btn-remove')&&tbody.rows.length>1){
    e.target.closest('tr').remove(); reindex(); refreshTotal();
  }
});

/* format rupiah saat ketik dibayar */
const viewPay=document.getElementById('dibayar_view');
viewPay.addEventListener('input',()=>{
  const pos=viewPay.selectionStart;
  const raw=bersih(viewPay.value);
  viewPay.value=raw?rupiah(raw):'';
  viewPay.setSelectionRange(pos,pos);
  refreshTotal();
});

/* blok submit jika qty invalid */
document.getElementById('frmTransaksi').addEventListener('submit',e=>{
  if(tbody.querySelector('.is-invalid')){
    alert('Qty melebihi stok tersedia.'); e.preventDefault();
  }
});

/* init */
setTipeCell(tbody.rows[0],true);
refreshRow(tbody.rows[0]);
</script>
@endpush
