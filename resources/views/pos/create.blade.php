@extends('layouts.app')
@section('title','Pembayaran')

@section('content')
@include('partials.neo-theme')
<style>
  :root{
    --bg:#f6f8fb; --card:#ffffff; --ink:#0f172a; --muted:#64748b;
    --brand:#A4193D; --brand2:#8C1433; --accent:#22c55e; --warm:#f59e0b;
    --chip:rgba(255,223,185,.45); --chip-ink:#7A1029; --chip-active:rgba(255,223,185,.65); --chip-border:rgba(164,25,61,.28);
    --border:#e5e7eb; --hover:#fff7f0;
  }
  body{background:var(--bg); color:var(--ink)}
  .glass{background:var(--card);border:1px solid rgba(2,6,23,.07);box-shadow:0 8px 26px rgba(2,6,23,.08);border-radius:16px}
  .header-gradient{background:linear-gradient(135deg,rgba(255,223,185,.55) 0%,#ffffff 55%,rgba(255,223,185,.35) 100%);border:1px solid rgba(164,25,61,.25);border-radius:16px;padding:14px 18px}
  .muted{color:var(--muted)} .pill{border-radius:999px}
  .amount-lg{font-size:1.7rem;font-weight:800} .amount-md{font-size:1.05rem;font-weight:800}

  /* Cart table */
  .table-cart thead{background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%)}
  .table-cart td,.table-cart th{vertical-align:middle}
  .cart-table-wrap{max-height:360px; overflow:auto}

  /* Tabs & buttons */
  .nav-modern .nav-link{border:0;color:#475569}
  .nav-modern .nav-link.active{color:#111827;font-weight:700;border-bottom:3px solid var(--brand);border-radius:0}
  .btn-brand{background:var(--brand);border-color:var(--brand);color:#fff}
  .btn-brand:hover{background:var(--brand2);border-color:var(--brand2)}
  .btn-soft{background:rgba(255,223,185,.45);color:var(--brand);border:1px solid rgba(164,25,61,.28)}
  .btn-soft:hover{background:rgba(255,223,185,.65)}

  /* Stepper qty (panel bawah) */
  .qty-stepper{display:flex;align-items:center}
  .qty-stepper .btn{width:40px;height:40px;font-size:1rem;border-color:#e5e7eb}
  .qty-stepper .form-control{height:40px;font-size:1rem;font-weight:800;text-align:center;flex:1 1 84px;min-width:84px;background:#fff}

  /* Qty di TABEL KERANJANG – FIX */
  .qty-row{display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap}
  .qty-row .btn{width:36px;height:36px;padding:0}
  .qty-row .form-control{height:36px;font-weight:700;text-align:end;width:72px;min-width:72px;flex:0 0 72px}

  /* Hilangkan spinner bawaan number */
  input[type=number]::-webkit-outer-spin-button,
  input[type=number]::-webkit-inner-spin-button{ -webkit-appearance: none; margin: 0; }
  input[type=number]{ -moz-appearance:textfield; }

  /* Sticky totals */
  .checkout-bar{position:sticky;bottom:-1px;background:#fff;border-top:1px solid rgba(164,25,61,.15);padding:.75rem 1rem;border-radius:0 0 16px 16px}

  /* Command palette (Cari) */
  .picker-wrap{position:relative}
  .picker-input{
    height:46px;font-size:1rem;border-radius:12px;padding-left:44px;
    background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242.656a5 5 0 1 1 0-10 5 5 0 0 1 0 10z'/%3E%3C/svg%3E") no-repeat 14px center;background-size:18px
  }
  .picker-hint{font-size:.85rem;color:var(--muted);margin-top:4px}
  .picker-results{position:absolute;z-index:1050;left:0;right:0;top:50px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 12px 28px rgba(2,6,23,.18);max-height:320px;overflow:auto}
  .picker-item{padding:.6rem .9rem;display:flex;align-items:center;gap:.5rem;cursor:pointer}
  .picker-item .title{font-weight:600}
  .picker-item:hover,.picker-item.active{background:rgba(255,223,185,.25)}
  .picker-empty{padding:.7rem .9rem;color:var(--muted)}
  .selected-pill{display:inline-flex;align-items:center;gap:.45rem;background:var(--chip);color:var(--chip-ink);padding:.25rem .6rem;border-radius:999px;font-weight:700;border:1px solid var(--chip-border)}

  /* Unit chips */
  .unit-chip{border:1px solid #e5e7eb;background:linear-gradient(135deg,#ffffff 0%,#f8fafc 100%);border-radius:999px;padding:.22rem .46rem;cursor:pointer}
  .unit-chip strong{font-weight:500; font-size:.78rem;}
  .unit-chip small{color:#64748b; font-size:.78rem;}
  .unit-chip.active{border-color:var(--chip-border);background:linear-gradient(135deg,var(--chip-active) 0%,#ffffff 100%);box-shadow:0 6px 16px rgba(99,102,241,.15)}

  /* DAFTAR grid 3 kolom */
  .mode-switch .btn{border-radius:999px}
  .list-box{border:1px solid var(--border);border-radius:12px;overflow:hidden}
  .list-toolbar{position:sticky;top:0;z-index:1;background:#fff;border-bottom:1px solid var(--border);padding:.5rem;display:flex;gap:.5rem;flex-wrap:wrap}
  .alpha-btn{border:1px solid var(--border);background:#fff;padding:.25rem .6rem;border-radius:999px;cursor:pointer;font-weight:600}
  .alpha-btn.active{background:rgba(255,223,185,.55);border-color:rgba(164,25,61,.28)}

  .grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0,1fr));
    gap:.6rem; padding:.6rem; max-height:260px; overflow:auto; background:#fcfdff;
    scrollbar-width: thin; scrollbar-color: #D9A4B3 transparent;
  }
  .grid::-webkit-scrollbar{ width:8px; height:8px; }
  .grid::-webkit-scrollbar-thumb{ background:#D9A4B3; border-radius:999px; border:3px solid transparent; background-clip:content-box; }
  .grid::-webkit-scrollbar-track{ background:transparent; }
  .tile{
    display:flex;align-items:flex-start;gap:.6rem;border:1px solid var(--border);border-radius:12px;
    padding:.5rem .65rem;background:#fff;cursor:pointer;transition:all .15s ease; min-height:54px;
  }
  .tile:hover{border-color:rgba(164,25,61,.28);background:var(--hover);transform:translateY(-1px)}
  .tile.active{border-color:var(--brand);box-shadow:0 8px 18px rgba(164,25,61,.16)}
  .tile .ico{ width:42px;height:42px;display:grid;place-items:center;border-radius:10px;background:rgba(255,223,185,.55);color:#7A1029;flex:0 0 42px; overflow:hidden }
  .tile .ico img{ width:100%; height:100%; object-fit:cover; display:block; border-radius:10px }
  .tile .name{
    font-weight:600;line-height:1.2;display:-webkit-box;-webkit-box-orient:vertical;
    -webkit-line-clamp:3;overflow:hidden;
  }
  .tile-content{display:flex;flex-direction:column;gap:.1rem;flex:1 1 auto;min-width:0}
  .tile .price{color:var(--muted);font-weight:600;font-size:.88rem}
  .unit-chip strong{font-weight:700}
  .picker-item .title{font-weight:600}
</style>

<div class="container-fluid py-3">
  <div class="header-gradient mb-3 d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-3 bg-white p-2 border">
        <i class="bi bi-calculator" style="font-size:1.35rem;color:var(--brand)"></i>
      </div>
      <div>
        <div class="fw-bold" style="font-size:1.15rem">Pembayaran</div>
        <div class="small muted">F2 Barang • F3 Jasa • F4 Pembayaran • F9 Simpan</div>
      </div>
    </div>
  </div>

  <form action="{{ route('pembayaran.store') }}" method="POST" id="posForm" autocomplete="off">
    @csrf
    @if ($errors->any())
      <div class="alert alert-danger glass">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
      {{-- KIRI: Keranjang + Tambah Item --}}
      <div class="col-12 col-lg-8">
        {{-- Keranjang --}}
        <div class="glass mb-3">
          <div class="p-3 border-bottom bg-white rounded-top-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Keranjang</h5>
            <button type="button" class="btn btn-sm btn-soft pill" onclick="kosongkanKeranjang()">
              <i class="bi bi-trash me-1"></i> Kosongkan
            </button>
          </div>

          <div class="p-0">
            <div class="table-responsive cart-table-wrap">
              <table class="table table-cart align-middle mb-0">
                <thead>
                  <tr>
                    <th style="width:84px">Tipe</th>
                    <th>Nama</th>
                    <th style="width:110px">Unit</th>
                    <th class="text-end" style="width:180px">Qty</th>
                    <th class="text-end" style="width:160px">Harga</th>
                    <th class="text-end" style="width:160px">Subtotal</th>
                    <th style="width:60px"></th>
                  </tr>
                </thead>
                <tbody id="cartBody">
                  <tr class="text-muted"><td colspan="7" class="text-center py-4">Belum ada item</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="checkout-bar">
            <div class="d-flex justify-content-between align-items-center">
              <div class="muted">Total</div>
              <div id="grandTotal" class="amount-lg">Rp0</div>
            </div>
            <div id="itemsHidden"></div>
          </div>
        </div>

        {{-- Tambah Item --}}
        <div class="glass">
          <div class="p-3 border-bottom bg-white rounded-top-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <ul class="nav nav-tabs nav-modern card-header-tabs" id="tabItem" role="tablist">
              <li class="nav-item"><a class="nav-link active" id="barang-tab" data-coreui-toggle="tab" href="#tab-barang" role="tab">Barang</a></li>
              <li class="nav-item"><a class="nav-link" id="jasa-tab" data-coreui-toggle="tab" href="#tab-jasa" role="tab">Jasa</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 ms-auto">
              <div class="mode-switch btn-group" role="group" aria-label="View switch">
                <button type="button" class="btn btn-soft active" id="btnModeDaftarHeader">Daftar</button>
                <button type="button" class="btn btn-soft" id="btnModeCariHeader">Cari</button>
              </div>
              <button id="btnToggleAlpha" type="button" class="btn btn-outline-secondary btn-sm">A–Z</button>
            </div>
          </div>

          <div class="p-3">
            <div class="tab-content">
              {{-- BARANG --}}
              <div class="tab-pane fade show active" id="tab-barang" role="tabpanel" aria-labelledby="barang-tab" tabindex="0">
                <div class="d-flex justify-content-end align-items-center mb-2"><div class="small muted">Pilih dari daftar, atau gunakan Cari</div></div>

                {{-- CARI BARANG --}}
                <div id="modeCariBarang" class="d-none">
                  <div class="picker-wrap mb-2">
                    <label class="form-label">Cari Barang</label>
                    <input id="barangSearch" type="text" class="form-control picker-input" placeholder="Ketik minimal 2 huruf…" autocomplete="off" autocapitalize="none" spellcheck="false">
                    <div class="picker-hint">Gunakan ↑/↓ lalu Enter untuk memilih.</div>
                    <div id="barangResults" class="picker-results d-none"></div>

                    <div id="barangSelectedInfo" class="mt-2 d-none">
                      <span class="selected-pill" id="barangSelectedPill">
                        <i class="bi bi-box-seam"></i> <span id="barangSelectedName"></span>
                      </span>
                      <button type="button" class="btn btn-sm btn-link text-decoration-none" onclick="resetBarangPick()">Ganti</button>
                    </div>
                  </div>
                </div>

                {{-- DAFTAR BARANG --}}
                <div id="modeDaftarBarang" class="list-box">
                  <div class="list-toolbar d-none" id="toolbarBarang">
                    <span class="alpha-btn active" data-letter="*">Semua</span>
                    @foreach(range('A','Z') as $L)
                      <span class="alpha-btn" data-letter="{{ $L }}">{{ $L }}</span>
                    @endforeach
                  </div>
                  <div id="barangGrid" class="grid"></div>
                </div>

                {{-- Unit chips --}}
                <div id="unitChipsWrap" class="mb-2 d-none">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Unit</label>
                    <div class="small muted">Pilih satu</div>
                  </div>
                  <div id="unitChips" class="d-flex flex-wrap gap-2"></div>
                </div>

                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label">Stok</label>
                    <input type="text" id="stokView" class="form-control" value="0" disabled>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Qty</label>
                    <div class="qty-stepper">
                      <button type="button" class="btn btn-outline-secondary" onclick="stepQty('qtyBarang',-1)" aria-label="Kurangi">−</button>
                      <input type="number" id="qtyBarang" class="form-control" min="1" value="1" inputmode="numeric" pattern="[0-9]*">
                      <button type="button" class="btn btn-outline-secondary" onclick="stepQty('qtyBarang',1)" aria-label="Tambah">+</button>
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="text" id="hargaBarang" class="form-control money" value="0">
                  </div>
                </div>

                <div class="mt-3 d-grid">
                  <button type="button" class="btn btn-brand pill" onclick="tambahBarang()"><i class="bi bi-plus-circle me-1"></i> Tambah</button>
                </div>
              </div>

              {{-- JASA --}}
              <div class="tab-pane fade" id="tab-jasa" role="tabpanel" aria-labelledby="jasa-tab" tabindex="0">
                <div class="d-flex justify-content-end align-items-center mb-2"><div class="small muted">Pilih dari daftar, atau gunakan Cari</div></div>

                {{-- CARI JASA --}}
                <div id="modeCariJasa" class="d-none">
                  <div class="picker-wrap mb-2">
                    <label class="form-label">Cari Jasa</label>
                    <input id="jasaSearch" type="text" class="form-control picker-input" placeholder="Ketik minimal 2 huruf…" autocomplete="off" autocapitalize="none" spellcheck="false">
                    <div class="picker-hint">Gunakan ↑/↓ lalu Enter untuk memilih.</div>
                    <div id="jasaResults" class="picker-results d-none"></div>

                    <div id="jasaSelectedInfo" class="mt-2 d-none">
                      <span class="selected-pill" id="jasaSelectedPill">
                        <i class="bi bi-wrench-adjustable"></i> <span id="jasaSelectedName"></span>
                      </span>
                      <button type="button" class="btn btn-sm btn-link text-decoration-none" onclick="resetJasaPick()">Ganti</button>
                    </div>
                  </div>
                </div>

                {{-- DAFTAR JASA --}}
                <div id="modeDaftarJasa" class="list-box">
                  <div class="list-toolbar d-none" id="toolbarJasa">
                    <span class="alpha-btn alpha-j active" data-letter="*">Semua</span>
                    @foreach(range('A','Z') as $L)
                      <span class="alpha-btn alpha-j" data-letter="{{ $L }}">{{ $L }}</span>
                    @endforeach
                  </div>
                  <div id="jasaGrid" class="grid"></div>
                </div>

                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label">Qty</label>
                    <div class="qty-stepper">
                      <button type="button" class="btn btn-outline-secondary" onclick="stepQty('qtyJasa',-1)" aria-label="Kurangi">−</button>
                      <input type="number" id="qtyJasa" class="form-control" min="1" value="1" inputmode="numeric" pattern="[0-9]*">
                      <button type="button" class="btn btn-outline-secondary" onclick="stepQty('qtyJasa',1)" aria-label="Tambah">+</button>
                    </div>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="text" id="hargaJasa" class="form-control money" value="0">
                  </div>
                </div>

                <div class="mt-3 d-grid">
                  <button type="button" class="btn btn-brand pill" onclick="tambahJasa()"><i class="bi bi-plus-circle me-1"></i> Tambah</button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- KANAN: Pembayaran --}}
      <div class="col-12 col-lg-4">
        <div class="glass">
          <div class="p-3 border-bottom bg-white rounded-top-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Pembayaran</h5>
            <div class="text-end">
              <div class="small muted">Total</div>
              <div id="totalTop" class="amount-md">Rp0</div>
            </div>
          </div>

          <div class="p-3">
            <div class="mb-3">
              <label class="form-label">Metode</label>
              <select class="form-select" name="metode_bayar" id="metodeBayar">
                <option value="cash">Cash</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
              </select>
            </div>

            <div id="qrisHint" class="alert alert-info d-none">
              Jika memilih <b>QRIS</b>, biarkan <i>Nominal Dibayar</i> = Rp0. Setelah scan & sukses, sistem akan menandai pembayaran otomatis.
            </div>

            <div class="mb-3">
              <label class="form-label">Nominal Dibayar (Rp)</label>
              <input id="dibayar_view" type="text" class="form-control money" value="0">
              <input id="dibayar" name="dibayar" type="hidden" value="0">
            </div>

            <div class="mb-3">
              <label class="form-label">Referensi (opsional)</label>
              <input type="text" name="reference" class="form-control" placeholder="No. transfer / QR ref">
            </div>

            <div class="mb-2 muted">Quick Cash</div>
            <div id="quickCash" class="d-flex flex-wrap gap-2 mb-3"></div>

            <div class="d-flex justify-content-between align-items-center">
              <div class="muted">Kembalian</div>
              <div id="kembalianView" class="amount-md">Rp0</div>
            </div>
          </div>

          <div class="p-3 border-top d-grid gap-2">
            <button type="button" class="btn btn-soft pill" id="btnUangPas"><i class="bi bi-cash-stack me-1"></i> Uang Pas</button>
            <button type="submit" class="btn btn-brand pill"><i class="bi bi-check2-circle me-1"></i> Simpan (F9)</button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

@php
  $map = ($unitPricesByBarang ?? collect())->mapWithKeys(function($rows, $barangId){
    return [$barangId => $rows->toArray()];
  });
  $barangsSimple = $barangs->map(fn($b)=>[
    'id'        => $b->id,
    'nama'      => $b->nama,
    'image_url' => $b->image_url,
  ])->values();
  $jasasSimple   = $jasas->map(fn($j)=>[
    'id'        => $j->id,
    'nama'      => $j->nama,
    'harga'     => (int) $j->harga_per_satuan,
    'image_url' => $j->image_url,
  ])->values();
@endphp

<script>
/* data */
const unitMap = @json($map);
const barangs = @json($barangsSimple);
const jasas   = @json($jasasSimple);

/* state (GLOBAL, satu sumber) */
let cart = [];
let pickedBarang = null;
let pickedUnit   = null;
let pickedJasa   = null;

/* helpers */
const rupiah   = n => 'Rp'+(Number(n)||0).toLocaleString('id-ID');
const idFormat = n => (Number(n)||0).toLocaleString('id-ID');
const clean    = s => +(String(s||'').replace(/[^0-9]/g,''))||0;
const $id      = id => document.getElementById(id);

/* format uang */
function bindMoneyInput(viewEl, onChange){
  const fmt = () => { const raw = clean(viewEl.value); viewEl.value = idFormat(raw); onChange?.(raw); };
  viewEl.addEventListener('input', fmt); viewEl.addEventListener('blur', fmt); fmt();
}

/* quick cash + header modes */
const QC_DEFAULT = [1000,2000,5000,10000,20000,50000,100000,200000];
(function(){
  const btnDaftarH=document.getElementById('btnModeDaftarHeader');
  const btnCariH=document.getElementById('btnModeCariHeader');
  const modeCariBarang=document.getElementById('modeCariBarang');
  const modeDaftarBarang=document.getElementById('modeDaftarBarang');
  const modeCariJasa=document.getElementById('modeCariJasa');
  const modeDaftarJasa=document.getElementById('modeDaftarJasa');
  const btnToggleAlpha=document.getElementById('btnToggleAlpha');
  const toolbarBarang=document.getElementById('toolbarBarang');
  const toolbarJasa=document.getElementById('toolbarJasa');

  function setHeaderMode(toList){
    if(modeDaftarBarang) modeDaftarBarang.classList.toggle('d-none', !toList);
    if(modeCariBarang)   modeCariBarang.classList.toggle('d-none',   toList);
    if(modeDaftarJasa)   modeDaftarJasa.classList.toggle('d-none',   !toList);
    if(modeCariJasa)     modeCariJasa.classList.toggle('d-none',     toList);
    if(btnDaftarH) btnDaftarH.classList.toggle('active', toList);
    if(btnCariH)   btnCariH.classList.toggle('active', !toList);
    if(toList){
      try{ if(typeof renderBarangGrid==='function') renderBarangGrid('*'); }catch(e){}
      try{ if(typeof renderJasaGrid==='function') renderJasaGrid('*'); }catch(e){}
    } else {
      try{ document.getElementById('unitChipsWrap')?.classList.add('d-none'); }catch(e){}
      pickedBarang=null; pickedUnit=null; pickedJasa=null;
    }
  }

  btnDaftarH?.addEventListener('click',()=>setHeaderMode(true));
  btnCariH?.addEventListener('click',()=>setHeaderMode(false));

  btnToggleAlpha?.addEventListener('click',()=>{
    const isBarang = document.getElementById('barang-tab').classList.contains('active');
    const el = isBarang ? toolbarBarang : toolbarJasa;
    if(el) el.classList.toggle('d-none');
  });

  setHeaderMode(true);
  try{
    document.getElementById('modeDaftarBarang')?.classList.remove('d-none');
    document.getElementById('modeCariBarang')?.classList.add('d-none');
    document.getElementById('modeDaftarJasa')?.classList.remove('d-none');
    document.getElementById('modeCariJasa')?.classList.add('d-none');
    if(typeof renderBarangGrid==='function') renderBarangGrid('*');
  }catch(e){}

  document.getElementById('tabItem')?.addEventListener('shown.coreui.tab', ()=>{
    setHeaderMode(btnDaftarH?.classList.contains('active'));
  });
  document.getElementById('tabItem')?.addEventListener('shown.bs.tab', ()=>{
    setHeaderMode(btnDaftarH?.classList.contains('active'));
  });

  // ======================== GRID RENDERERS (scope IIFE) ========================
  const BARANGS = @json($barangs ?? []);
  const JASAS   = @json($jasas ?? []);
  const UNITMAP = @json(($unitPricesByBarang ?? collect())->toArray());

  function h(el, html){ el.innerHTML = html; }
  function byId(id){ return document.getElementById(id); }

  function renderBarangGrid(alpha='*'){
    const wrap = byId('barangGrid'); if(!wrap) return;
    const letter = (alpha||'*').toString().toUpperCase();
    const list = (BARANGS||[]).filter(it=> letter==='*' || (it.nama||'').toUpperCase().startsWith(letter));
    if(list.length===0){ h(wrap,'<div class="text-center text-muted py-3">Tidak ada barang</div>'); return; }
    let html='';
    list.forEach(it=>{
      const name = (it.nama||'').replace(/</g,'&lt;');
      const thumb = it.image_url ? `<img src="${it.image_url}" alt="${name}" onerror=\"this.outerHTML='B'\">` : 'B';
      html += `<div class="tile" data-id="${it.id}" data-name="${name}">
        <div class="ico">${thumb}</div>
        <div class="flex-1"><div class="name">${name}</div></div>
      </div>`;
    });
    h(wrap, html);
    wrap.querySelectorAll('.tile').forEach(t=> t.addEventListener('click', ()=>{
      const id = t.getAttribute('data-id'); const name=t.getAttribute('data-name');
      pickedBarang = { id: parseInt(id,10), nama: name };
      const chipsWrap = byId('unitChipsWrap'); const chips = byId('unitChips');
      const rows = (UNITMAP[id]||[]);
      if(rows.length){
        let cHtml=''; rows.forEach((r,i)=>{
          cHtml += `<button type="button" class="unit-chip" data-unit="${r.unit_id}" data-kode="${r.unit_kode}" data-harga="${r.harga}" data-stok="${r.stok}">
            <strong>${(r.unit_kode||'').toUpperCase()}</strong><small class="ms-1 text-muted">Rp${(r.harga||0).toLocaleString('id-ID')}</small>
          </button>`;
        });
        h(chips, cHtml); chipsWrap.classList.remove('d-none');
        chips.querySelectorAll('.unit-chip').forEach(btn=> btn.addEventListener('click', ()=>{
          chips.querySelectorAll('.unit-chip').forEach(b=>b.classList.remove('active'));
          btn.classList.add('active');
          pickedBarang = {
            ...(pickedBarang||{}),
            unit_id : parseInt(btn.getAttribute('data-unit'),10),
            unit_kode: btn.getAttribute('data-kode'),
            harga   : parseInt(btn.getAttribute('data-harga')||'0',10),
            stok    : parseInt(btn.getAttribute('data-stok')||'0',10)
          };
          byId('stokView').value    = idFormat(pickedBarang.stok||0);
          byId('hargaBarang').value = idFormat(pickedBarang.harga||0); // <-- auto format
        }));
        const first = chips.querySelector('.unit-chip'); if(first){ first.click(); }
      } else {
        byId('unitChipsWrap')?.classList.add('d-none');
      }
    }));
  }

  function renderJasaGrid(alpha='*'){
    const wrap = byId('jasaGrid'); if(!wrap) return;
    const letter = (alpha||'*').toString().toUpperCase();
    const list = (JASAS||[]).filter(it=> letter==='*' || (it.nama||'').toUpperCase().startsWith(letter));
    if(list.length===0){ h(wrap,'<div class="text-center text-muted py-3">Tidak ada jasa</div>'); return; }
    let html='';
    list.forEach(it=>{
      const name = (it.nama||'').replace(/</g,'&lt;');
      const harga = parseInt(it.harga_per_satuan||0,10);
      const thumb = it.image_url ? `<img src="${it.image_url}" alt="${name}" onerror=\"this.outerHTML='J'\">` : 'J';
      html += `<div class="tile" data-id="${it.id}" data-name="${name}" data-harga="${harga}">
        <div class="ico">${thumb}</div>
        <div class="tile-content">
          <div class="name">${name}</div>
          <div class="price">Rp${harga.toLocaleString('id-ID')}</div>
        </div>
      </div>`;
    });
    h(wrap, html);
    wrap.querySelectorAll('.tile').forEach(t=> t.addEventListener('click', ()=>{
      const id = parseInt(t.getAttribute('data-id'),10);
      const name = t.getAttribute('data-name');
      const harga = parseInt(t.getAttribute('data-harga')||'0',10);
      pickedJasa = { id, nama: name, harga };
      byId('hargaJasa').value = idFormat(harga); // <-- auto format
    }));
  }

  // ====================== SEARCH (CARI) ======================
  function resetBarangPick(){
    pickedBarang=null; pickedUnit=null;
    byId('unitChipsWrap')?.classList.add('d-none');
    if(byId('stokView')) byId('stokView').value=0;
    if(byId('hargaBarang')) byId('hargaBarang').value=0;
    byId('barangSelectedInfo')?.classList.add('d-none');
  }
  function resetJasaPick(){
    pickedJasa=null; if(byId('hargaJasa')) byId('hargaJasa').value=0;
    byId('jasaSelectedInfo')?.classList.add('d-none');
  }

  const bInput=document.getElementById('barangSearch'), bResults=document.getElementById('barangResults');
  const bSelInfo=document.getElementById('barangSelectedInfo'), bSelName=document.getElementById('barangSelectedName');
  function renderBarangResults(){
    const q=(bInput?.value||'').toLowerCase().trim(); if(!bResults) return;
    if(!q){ bResults.classList.add('d-none'); bResults.innerHTML=''; return; }
    const list=(BARANGS||[]).filter(it=> (it.nama||'').toLowerCase().includes(q));
    if(list.length===0){ bResults.classList.remove('d-none'); bResults.innerHTML='<div class="picker-empty">Tidak ada hasil</div>'; return; }
    let html='';
    list.forEach(it=>{
      const name=(it.nama||'').replace(/</g,'&lt;');
      html+=`<div class="picker-item" data-id="${it.id}" data-name="${name}"><div class="title">${name}</div></div>`;
    });
    bResults.classList.remove('d-none'); bResults.innerHTML=html;
    bResults.querySelectorAll('.picker-item').forEach(el=> el.addEventListener('click',()=>{
      const id=el.getAttribute('data-id'); const name=el.getAttribute('data-name');
      pickedBarang={ id:parseInt(id,10), nama:name }; bSelName.textContent=name; bSelInfo.classList.remove('d-none');
      const rows=(UNITMAP[id]||[]); const chips=document.getElementById('unitChips'); const wrap=document.getElementById('unitChipsWrap');
      if(rows.length){
        let cHtml=''; rows.forEach(r=> cHtml+=`<button type="button" class="unit-chip" data-unit="${r.unit_id}" data-kode="${r.unit_kode}" data-harga="${r.harga}" data-stok="${r.stok}"><strong>${(r.unit_kode||'').toUpperCase()}</strong><small class="ms-1 text-muted">Rp${(r.harga||0).toLocaleString('id-ID')}</small></button>`);
        chips.innerHTML=cHtml; wrap.classList.remove('d-none');
        chips.querySelectorAll('.unit-chip').forEach(btn=> btn.addEventListener('click',()=>{
          chips.querySelectorAll('.unit-chip').forEach(b=>b.classList.remove('active'));
          btn.classList.add('active');
          pickedBarang = {
            ...(pickedBarang||{}),
            unit_id : parseInt(btn.getAttribute('data-unit'),10),
            unit_kode: btn.getAttribute('data-kode'),
            harga   : parseInt(btn.getAttribute('data-harga')||'0',10),
            stok    : parseInt(btn.getAttribute('data-stok')||'0',10)
          };
          document.getElementById('stokView').value  = idFormat(pickedBarang.stok||0);
          document.getElementById('hargaBarang').value = idFormat(pickedBarang.harga||0); // <-- auto format
        }));
        const first=chips.querySelector('.unit-chip'); if(first){ first.click(); }
      } else { wrap.classList.add('d-none'); }
      bResults.classList.add('d-none');
    }));
  }
  bInput?.addEventListener('input', renderBarangResults);
  document.getElementById('btnModeCariHeader')?.addEventListener('click',()=>{ setTimeout(()=> bInput?.focus(), 50); });

  const jInput=document.getElementById('jasaSearch'), jResults=document.getElementById('jasaResults');
  const jSelInfo=document.getElementById('jasaSelectedInfo'), jSelName=document.getElementById('jasaSelectedName');
  function renderJasaResults(){
    const q=(jInput?.value||'').toLowerCase().trim(); if(!jResults) return;
    if(!q){ jResults.classList.add('d-none'); jResults.innerHTML=''; return; }
    const list=(JASAS||[]).filter(it=> (it.nama||'').toLowerCase().includes(q));
    if(list.length===0){ jResults.classList.remove('d-none'); jResults.innerHTML='<div class="picker-empty">Tidak ada hasil</div>'; return; }
    let html=''; list.forEach(it=>{ const name=(it.nama||'').replace(/</g,'&lt;'); html+=`<div class="picker-item" data-id="${it.id}" data-name="${name}" data-harga="${parseInt(it.harga_per_satuan||0,10)}"><div class="title">${name}</div></div>`; });
    jResults.classList.remove('d-none'); jResults.innerHTML=html;
    jResults.querySelectorAll('.picker-item').forEach(el=> el.addEventListener('click',()=>{
      const id=parseInt(el.getAttribute('data-id'),10); const name=el.getAttribute('data-name'); const harga=parseInt(el.getAttribute('data-harga')||'0',10);
      pickedJasa={ id, nama:name, harga }; jSelName.textContent=name; jSelInfo.classList.remove('d-none'); document.getElementById('hargaJasa').value=idFormat(harga); // <-- auto format
      jResults.classList.add('d-none');
    }));
  }
  jInput?.addEventListener('input', renderJasaResults);

  // Force Daftar once after wire-up
  setTimeout(()=>{ try{ btnDaftarH?.click(); }catch(e){} }, 0);

  const wrap = $id('quickCash'); wrap.innerHTML='';
  QC_DEFAULT.forEach(v=>{
    const b=document.createElement('button');
    b.type='button'; b.className='btn btn-sm btn-soft pill';
    b.textContent=rupiah(v);
    b.onclick=()=>{
      if ($id('dibayar_view').disabled) return; // jika QRIS, nonaktif
      const now=clean($id('dibayar').value); const next=now+v;
      $id('dibayar').value=next; $id('dibayar_view').value=idFormat(next); hitungKembalian();
    };
    wrap.appendChild(b);
  });
})();

/* mode Barang (global) */
const btnCariB = $id('btnModeCariBarang'), btnDaftarB = $id('btnModeDaftarBarang');
const modeCariB = $id('modeCariBarang'), modeDaftarB = $id('modeDaftarBarang');
if(btnCariB && btnDaftarB){
  btnCariB.onclick   = ()=>{ btnCariB.classList.add('active'); btnDaftarB.classList.remove('active'); modeCariB.classList.remove('d-none'); modeDaftarB.classList.add('d-none'); };
  btnDaftarB.onclick = ()=>{ btnDaftarB.classList.add('active'); btnCariB.classList.remove('active'); modeDaftarB.classList.remove('d-none'); modeCariB.classList.add('d-none'); renderBarangGrid('*'); };
}

/* cari Barang (global) */
const bSearch=$id('barangSearch'), bResults=$id('barangResults'),
      bSelInfo=$id('barangSelectedInfo'), bSelName=$id('barangSelectedName'),
      unitChipsWrap=$id('unitChipsWrap'), unitChipsEl=$id('unitChips');

let bCursor=-1, bList=[];
function openResultsB(){ bResults.classList.remove('d-none'); }
function closeResultsB(){ bResults.classList.add('d-none'); bCursor=-1; }
function renderResultsB(list){
  bResults.innerHTML=''; if(!list.length){ bResults.innerHTML='<div class="picker-empty">Tidak ada hasil.</div>'; return; }
  list.forEach((it,i)=>{ const div=document.createElement('div'); div.className='picker-item'+(i===bCursor?' active':''); div.innerHTML=`<div class="title">${it.nama}</div>`; div.onclick=()=>selectBarang(it); bResults.appendChild(div); });
}
function filterBarang(q){
  q=(q||'').toLowerCase();
  if(q.length < 2){ closeResultsB(); return; }
  bList = barangs.filter(b=>b.nama.toLowerCase().includes(q)).slice(0,80);
  bCursor=-1; renderResultsB(bList); openResultsB();
}
function selectBarang(it){
  pickedBarang = { id:it.id, nama:it.nama, unit_id:null, unit_kode:null, stok:0, harga:0 };
  bSelName.textContent=it.nama; bSelInfo.classList.remove('d-none');
  bSearch.value=''; closeResultsB(); renderUnitChips(it.id);
  $id('stokView').value=0; $id('hargaBarang').value=idFormat(0);
}
function resetBarangPick(){
  pickedBarang=null; bSelInfo.classList.add('d-none');
  unitChipsWrap.classList.add('d-none'); unitChipsEl.innerHTML='';
  $id('stokView').value=0; $id('hargaBarang').value=idFormat(0);
}
bSearch?.addEventListener('input',()=>filterBarang(bSearch.value));
bSearch?.addEventListener('keydown',(e)=>{
  if(bResults.classList.contains('d-none')) return;
  if(e.key==='ArrowDown'){e.preventDefault(); bCursor=Math.min(bCursor+1,bList.length-1); renderResultsB(bList);}
  if(e.key==='ArrowUp'){e.preventDefault();   bCursor=Math.max(bCursor-1,0);               renderResultsB(bList);}
  if(e.key==='Enter'){e.preventDefault(); if(bCursor>=0) selectBarang(bList[bCursor]);}
  if(e.key==='Escape'){e.preventDefault(); closeResultsB();}
});
document.addEventListener('click',(e)=>{ if(!bResults?.contains(e.target) && e.target!==bSearch) closeResultsB(); });

/* daftar Barang (global) */
const bGrid = $id('barangGrid');
document.querySelectorAll('.alpha-btn').forEach(btn=>{
  btn.addEventListener('click',()=>{ document.querySelectorAll('.alpha-btn').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); renderBarangGrid(btn.dataset.letter); });
});
function renderBarangGrid(letter='*'){
  if(!bGrid) return;
  bGrid.innerHTML='';
  const list = barangs.filter(b=>{
    if(letter==='*') return true;
    const ch=(b.nama||'').trim().charAt(0).toUpperCase(); return ch===letter;
  }).slice(0,300);
  if(!list.length){ bGrid.innerHTML='<div class="muted p-2">Tidak ada barang.</div>'; return; }
  list.forEach(b=>{
    const div=document.createElement('div'); div.className='tile';
    const thumb = b.image_url ? `<img src="${b.image_url}" alt="${(b.nama||'').replace(/</g,'&lt;')}" onerror=\"this.outerHTML='B'\">` : 'B';
    div.innerHTML=`<div class="ico">${thumb}</div><div class="name">${b.nama}</div>`;
    div.onclick=()=>{
      bGrid.querySelectorAll('.tile').forEach(t=>t.classList.remove('active'));
      div.classList.add('active');
      selectBarang(b);
    };
    bGrid.appendChild(div);
  });
}

/* Unit chips (global) */
function renderUnitChips(barangId){
  const rows=unitMap[barangId]||[]; unitChipsEl.innerHTML='';
  rows.forEach(u=>{
    const b=document.createElement('button'); b.type='button'; b.className='unit-chip';
    b.dataset.unitId=u.unit_id; b.dataset.k=u.unit_kode; b.dataset.h=u.harga; b.dataset.s=u.stok;
    b.innerHTML=`${u.unit_kode} <small>(stok ${idFormat(u.stok)})</small>`;
    b.onclick=()=>selectUnitChip(b); unitChipsEl.appendChild(b);
  });
  unitChipsWrap.classList.toggle('d-none', rows.length===0);
}
function selectUnitChip(btn){
  unitChipsEl.querySelectorAll('.unit-chip').forEach(el=>el.classList.remove('active'));
  btn.classList.add('active');
  pickedBarang = {...(pickedBarang||{}), unit_id:+btn.dataset.unitId, unit_kode:btn.dataset.k, stok:+btn.dataset.s||0, harga:+btn.dataset.h||0 };
  $id('stokView').value=idFormat(pickedBarang.stok||0);
  $id('hargaBarang').value=idFormat(pickedBarang.harga||0);
}

/* mode Jasa (global) */
const btnCariJ = $id('btnModeCariJasa'), btnDaftarJ = $id('btnModeDaftarJasa');
const modeCariJ = $id('modeCariJasa'), modeDaftarJ = $id('modeDaftarJasa');
if(btnCariJ && btnDaftarJ){
  btnCariJ.onclick   = ()=>{ btnCariJ.classList.add('active'); btnDaftarJ.classList.remove('active'); modeCariJ.classList.remove('d-none'); modeDaftarJ.classList.add('d-none'); };
  btnDaftarJ.onclick = ()=>{ btnDaftarJ.classList.add('active'); btnCariJ.classList.remove('active'); modeDaftarJ.classList.remove('d-none'); modeCariJ.classList.add('d-none'); renderJasaGrid('*'); };
}

/* cari Jasa (global) */
const jSearch=$id('jasaSearch'), jResults=$id('jasaResults'),
      jSelInfo=$id('jasaSelectedInfo'), jSelName=$id('jasaSelectedName');

let jCursor=-1, jList=[];
function openResultsJ(){ jResults.classList.remove('d-none'); }
function closeResultsJ(){ jResults.classList.add('d-none'); jCursor=-1; }
function renderResultsJ(list){
  jResults.innerHTML=''; if(!list.length){ jResults.innerHTML='<div class="picker-empty">Tidak ada hasil.</div>'; return; }
  list.forEach((it,i)=>{ const div=document.createElement('div'); div.className='picker-item'+(i===jCursor?' active':''); div.innerHTML=`<div class="title">${it.nama}</div>`; div.onclick=()=>selectJasa(it); jResults.appendChild(div); });
}
function filterJasa(q){
  q=(q||'').toLowerCase();
  if(q.length < 2){ closeResultsJ(); return; }
  jList = jasas.filter(x=>x.nama.toLowerCase().includes(q)).slice(0,80);
  jCursor=-1; renderResultsJ(jList); openResultsJ();
}
function selectJasa(it){
  pickedJasa = { id:it.id, nama:it.nama, harga:it.harga||0 };
  jSelName.textContent=it.nama; jSelInfo.classList.remove('d-none');
  jSearch.value=''; closeResultsJ();
  $id('hargaJasa').value = idFormat(pickedJasa.harga||0);
}
function resetJasaPick(){
  pickedJasa=null; jSelInfo.classList.add('d-none');
  $id('hargaJasa').value = idFormat(0);
}
jSearch?.addEventListener('input',()=>filterJasa(jSearch.value));
jSearch?.addEventListener('keydown',(e)=>{
  if(jResults.classList.contains('d-none')) return;
  if(e.key==='ArrowDown'){e.preventDefault(); jCursor=Math.min(jCursor+1,jList.length-1); renderResultsJ(jList);}
  if(e.key==='ArrowUp'){e.preventDefault();   jCursor=Math.max(jCursor-1,0);               renderResultsJ(jList);}
  if(e.key==='Enter'){e.preventDefault(); if(jCursor>=0) selectJasa(jList[jCursor]);}
  if(e.key==='Escape'){e.preventDefault(); closeResultsJ();}
});
document.addEventListener('click',(e)=>{ if(!jResults?.contains(e.target) && e.target!==jSearch) closeResultsJ(); });

/* daftar Jasa (global) */
const jGrid = $id('jasaGrid');
document.querySelectorAll('.alpha-j').forEach(btn=>{
  btn.addEventListener('click',()=>{ document.querySelectorAll('.alpha-j').forEach(b=>b.classList.remove('active')); btn.classList.add('active'); renderJasaGrid(btn.dataset.letter); });
});
function renderJasaGrid(letter='*'){
  if(!jGrid) return;
  jGrid.innerHTML='';
  const list = jasas.filter(j=>{
    if(letter==='*') return true;
    const ch=(j.nama||'').trim().charAt(0).toUpperCase(); return ch===letter;
  }).slice(0,300);
  if(!list.length){ jGrid.innerHTML='<div class="muted p-2">Tidak ada jasa.</div>'; return; }
  list.forEach(j=>{
    const div=document.createElement('div'); div.className='tile';
    const thumb = j.image_url ? `<img src="${j.image_url}" alt="${(j.nama||'').replace(/</g,'&lt;')}" onerror=\"this.outerHTML='J'\">` : 'J';
    div.innerHTML=`<div class="ico">${thumb}</div><div class="tile-content"><div class="name">${j.nama}</div>${j.harga?`<div class=\"price\">Rp${(j.harga||0).toLocaleString('id-ID')}</div>`:''}</div>`;
    div.onclick=()=>{
      jGrid.querySelectorAll('.tile').forEach(t=>t.classList.remove('active'));
      div.classList.add('active');
      selectJasa(j);
    };
    jGrid.appendChild(div);
  });
}

/* money (bind sekali) */
bindMoneyInput($id('dibayar_view'), raw => { $id('dibayar').value=raw; hitungKembalian(); });
bindMoneyInput($id('hargaBarang'));
bindMoneyInput($id('hargaJasa'));

/* qty helper */
function stepQty(id,delta){ const el=$id(id); el.value=Math.max(1,(+el.value||1)+delta); }

/* tambah ke cart */
function tambahBarang(){
  if(!pickedBarang || !pickedBarang.id){ alert('Pilih barang.'); return; }
  if(!pickedBarang.unit_id){ alert('Pilih unit.'); return; }
  const qty=+$id('qtyBarang').value||1, harga=clean($id('hargaBarang').value);
  if(qty>+(pickedBarang.stok||0)){ alert('Qty melebihi stok.'); return; }
  cart.push({tipe:'barang', barang_id:pickedBarang.id, jasa_id:null, unit_id:pickedBarang.unit_id, unit_kode:pickedBarang.unit_kode, nama:pickedBarang.nama, qty, harga});
  renderCart(); $id('qtyBarang').value=1;
}
function tambahJasa(){
  if(!pickedJasa || !pickedJasa.id){ alert('Pilih jasa.'); return; }
  const qty=+$id('qtyJasa').value||1, harga=clean($id('hargaJasa').value);
  cart.push({tipe:'jasa', barang_id:null, jasa_id:pickedJasa.id, unit_id:null, unit_kode:null, nama:pickedJasa.nama, qty, harga});
  renderCart(); $id('qtyJasa').value=1;
}

/* render cart */
function renderCart(){
  const body=$id('cartBody'), hidden=$id('itemsHidden');
  body.innerHTML=''; hidden.innerHTML='';
  if(cart.length===0){ body.innerHTML='<tr class="text-muted"><td colspan="7" class="text-center py-4">Belum ada item</td></tr>'; updateTotals(); return; }
  let grand=0;
  cart.forEach((it,i)=>{
    const sub=it.qty*it.harga; grand+=sub;
    const tr=document.createElement('tr');
    tr.innerHTML=`
      <td><span class="badge ${it.tipe==='barang'?'text-bg-primary':'text-bg-success'} pill text-capitalize">${it.tipe}</span></td>
      <td>${it.nama}</td>
      <td>${it.tipe==='barang'?(it.unit_kode||'-'):'-'}</td>
      <td class="text-end">
        <div class="qty-row">
          <button type="button" class="btn btn-outline-secondary" onclick="editQty(${i},-1)">−</button>
          <input type="number" value="${it.qty}" min="1" class="form-control" onchange="onCellEdit(${i},'qty',this)">
          <button type="button" class="btn btn-outline-secondary" onclick="editQty(${i},1)">+</button>
        </div>
      </td>
      <td class="text-end">
        <input type="text" value="${idFormat(it.harga)}" class="form-control text-end"
               oninput="onMoneyEdit(${i}, this)" onblur="onMoneyEdit(${i}, this)">
      </td>
      <td class="text-end fw-semibold">${rupiah(sub)}</td>
      <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusItem(${i})"><i class="bi bi-x-lg"></i></button></td>
    `;
    body.appendChild(tr);

    hidden.insertAdjacentHTML('beforeend',`
      <input type="hidden" name="items[${i}][tipe_item]" value="${it.tipe}">
      <input type="hidden" name="items[${i}][barang_id]" value="${it.barang_id ?? ''}">
      <input type="hidden" name="items[${i}][jasa_id]" value="${it.jasa_id ?? ''}">
      <input type="hidden" name="items[${i}][unit_id]" value="${it.unit_id ?? ''}">
      <input type="hidden" name="items[${i}][jumlah]" value="${it.qty}">
      <input type="hidden" name="items[${i}][harga_satuan]" value="${it.harga}">
    `);
  });
  $id('grandTotal').textContent = rupiah(grand);
  $id('totalTop').textContent   = rupiah(grand);
  hitungKembalian();
}
function onMoneyEdit(i, el){ const v=clean(el.value); cart[i].harga=v; el.value=idFormat(v); renderCart(); }
function editQty(i,d){ cart[i].qty=Math.max(1,(cart[i].qty||1)+d); renderCart(); }
function onCellEdit(i,field,el){ let v=+el.value||0; if(field==='qty') v=Math.max(1,v); cart[i][field]=v; renderCart(); }
function hapusItem(i){ cart.splice(i,1); renderCart(); }

function kosongkanKeranjang(){ if(!confirm('Kosongkan keranjang?')) return; cart=[]; renderCart(); }
function updateTotals(){ let g=0; cart.forEach(it=>g+=it.qty*it.harga); $id('grandTotal').textContent=rupiah(g); $id('totalTop').textContent=rupiah(g); hitungKembalian(); }

/* pembayaran */
function hitungKembalian(){
  let total=0; cart.forEach(it=> total+=it.qty*it.harga);
  const dibayar = clean($id('dibayar').value);
  const kembali = Math.max(0, dibayar - total);
  $id('kembalianView').textContent = rupiah(kembali);
}
$id('btnUangPas')?.addEventListener('click', ()=>{
  if ($id('dibayar_view').disabled) return; // QRIS → nonaktif
  let total=0; cart.forEach(it=> total+=it.harga*it.qty);
  $id('dibayar').value = total; $id('dibayar_view').value = idFormat(total); hitungKembalian();
});

/* Metode bayar → UX QRIS */
const metodeSel = $id('metodeBayar');
function onMetodeChange(){
  const isQris = metodeSel.value === 'qris';
  $id('dibayar_view').disabled = isQris;
  $id('btnUangPas').disabled   = isQris;
  document.querySelectorAll('#quickCash button').forEach(b=> b.disabled = isQris);
  $id('qrisHint').classList.toggle('d-none', !isQris);
  if (isQris){
    $id('dibayar').value = 0; $id('dibayar_view').value = idFormat(0); hitungKembalian();
  }
}
metodeSel?.addEventListener('change', onMetodeChange);

/* shortcuts */
document.addEventListener('keydown',(e)=>{
  if(e.key==='F2'){ e.preventDefault(); document.querySelector('#barang-tab')?.click(); document.getElementById('btnModeDaftarBarang')?.click?.(); }
  if(e.key==='F3'){ e.preventDefault(); document.querySelector('#jasa-tab')?.click(); document.getElementById('btnModeDaftarJasa')?.click?.(); }
  if(e.key==='F4'){ e.preventDefault(); if(!$id('dibayar_view').disabled) $id('dibayar_view')?.focus(); }
  if(e.key==='F9'){ e.preventDefault(); document.getElementById('posForm').requestSubmit(); }
});

/* init */
bindMoneyInput($id('dibayar_view'), raw => { $id('dibayar').value=raw; hitungKembalian(); });
bindMoneyInput($id('hargaBarang'));
bindMoneyInput($id('hargaJasa'));
renderBarangGrid('*'); renderJasaGrid('*'); renderCart(); onMetodeChange();
</script>
@endsection
