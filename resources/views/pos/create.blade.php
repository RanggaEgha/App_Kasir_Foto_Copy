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
  /* Samakan scrollbar keranjang dengan grid barang/jasa */
  .cart-table-wrap::-webkit-scrollbar{ width:8px; height:8px; }
  .cart-table-wrap::-webkit-scrollbar-thumb{ background:#D9A4B3; border-radius:999px; border:3px solid transparent; background-clip:content-box; }
  .cart-table-wrap::-webkit-scrollbar-track{ background:transparent; }

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

  /* Lebarkan input Harga & Diskon agar tidak terpotong */
  .table-cart .price-input{min-width:130px;text-align:end}
  .table-cart .discount-input{min-width:150px;text-align:end}
  .is-invalid-field{border-color:#dc3545 !important; background:#fff5f5}

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
  .picker-clear{position:absolute; right:10px; top:36px; transform:translateY(-50%); border:1px solid var(--border); background:#fff; width:28px; height:28px; border-radius:999px; display:grid; place-items:center; color:#64748b}
  .picker-clear:hover{background:var(--hover)}
  .picker-hint{font-size:.85rem;color:var(--muted);margin-top:4px}
  .picker-results{position:absolute;z-index:1050;left:0;right:0;top:50px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 12px 28px rgba(2,6,23,.18);max-height:320px;overflow:auto}
  .picker-item{padding:.6rem .9rem;display:flex;align-items:center;gap:.5rem;cursor:pointer}
  .picker-item .title{font-weight:600}
  .picker-item:hover,.picker-item.active{background:rgba(255,223,185,.25)}
  /* Tile hasil pencarian */
  .result-tile{display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border-radius:10px;cursor:pointer}
  .result-tile:hover,.result-tile.active{background:rgba(255,223,185,.25)}
  .result-tile .ico{ width:36px;height:36px;display:grid;place-items:center;border-radius:8px;background:rgba(255,223,185,.55);color:#7A1029;flex:0 0 36px; overflow:hidden }
  .result-tile .ico img{ width:100%; height:100%; object-fit:cover; display:block; border-radius:8px }
  .result-tile .name{font-weight:700}
  .result-tile .meta{font-size:.82rem;color:#64748b}
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
                    <th class="text-end" style="width:190px">Diskon</th>
                    <th class="text-end" style="width:160px">Subtotal</th>
                    <th style="width:60px"></th>
                  </tr>
                </thead>
                <tbody id="cartBody">
                  <tr class="text-muted"><td colspan="8" class="text-center py-4">Belum ada item</td></tr>
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
                    <button type="button" id="barangClear" class="picker-clear d-none" aria-label="Bersihkan">&times;</button>
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
                    <button type="button" id="jasaClear" class="picker-clear d-none" aria-label="Bersihkan">&times;</button>
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
            @if(empty($shiftOpen))
              <div class="alert alert-warning d-flex justify-content-between align-items-center gap-2">
                <div>
                  Shift kasir belum dibuka. Pembayaran <strong>Cash</strong> dinonaktifkan.
                </div>
                <a href="{{ route('shift.index') }}" class="btn btn-brand btn-sm">Buka Shift</a>
              </div>
            @endif
            <div class="mb-3">
              <label class="form-label">Metode</label>
              <select class="form-select" name="metode_bayar" id="metodeBayar">
                <option value="cash" {{ !empty($shiftOpen) ? '' : 'disabled' }}>Cash</option>
                <option value="transfer" {{ empty($shiftOpen) ? 'selected' : '' }}>Transfer</option>
                <option value="qris">QRIS</option>
              </select>
            </div>

            <div id="qrisHint" class="alert alert-info d-none">
              Jika memilih <b>QRIS</b>, biarkan <i>Nominal Dibayar</i> = Rp0. Setelah scan & sukses, sistem akan menandai pembayaran otomatis.
            </div>

            <div class="mb-3">
              <label class="form-label">Diskon Nota</label>
              <div class="row g-2 align-items-center mb-2">
                <div class="col-6">
                  <select class="form-select" name="discount_type" id="discType">
                    <option value="">tidak ada</option>
                    <option value="amount">Rp (nominal)</option>
                    <option value="percent">% (persen)</option>
                  </select>
                </div>
                <div class="col-6">
                  <input id="discValue_view" type="text" class="form-control" placeholder="0">
                  <input id="discValue" name="discount_value" type="hidden" value="0">
                </div>
              </div>
              <div class="row g-2 mb-2">
                <div class="col-6"><input type="text" name="coupon_code" id="couponCode" class="form-control" placeholder="Kupon (opsional)"></div>
                <div class="col-6"><input type="text" name="discount_reason" class="form-control" placeholder="Alasan (opsional)"></div>
              </div>
              <div class="small text-muted">Potongan: <span id="discAmountPreview">Rp0</span></div>
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
            <button type="submit" id="btnSimpan" class="btn btn-brand pill"><i class="bi bi-check2-circle me-1"></i> Simpan (F9)</button>
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
  $discRules = $discountRules ?? ['barang'=>[], 'jasa'=>[]];
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
const discRules = @json($discRules);

/* state (GLOBAL, satu sumber) */
let cart = [];
let pickedBarang = null;
let pickedUnit   = null;
let pickedJasa   = null;

/* helpers */
const rupiah   = n => 'Rp' + (Number(n)||0).toLocaleString('id-ID');
const idFormat = n => (Number(n)||0).toLocaleString('id-ID');
const clean    = s => +(String(s||'').replace(/[^0-9]/g,''))||0;
const $id      = id => document.getElementById(id);

// ===== Stok helpers (client-side reservation awareness) =====
function stockTotal(barangId, unitId){
  const rows = unitMap[String(barangId)] || unitMap[barangId] || [];
  const r = rows.find(x => Number(x.unit_id) === Number(unitId));
  return Number(r?.stok || 0);
}
function reservedQty(barangId, unitId, excludeIndex=null){
  let sum = 0;
  cart.forEach((it, idx) => {
    if (excludeIndex !== null && idx === excludeIndex) return;
    if (it.tipe === 'barang' && Number(it.barang_id) === Number(barangId) && Number(it.unit_id) === Number(unitId)) {
      sum += Number(it.qty||0);
    }
  });
  return sum;
}
function availableStock(barangId, unitId, excludeIndex=null){
  const total = stockTotal(barangId, unitId);
  const reserved = reservedQty(barangId, unitId, excludeIndex);
  return Math.max(0, total - reserved);
}
function updateSelectedStockView(){
  try{
    if (pickedBarang && pickedBarang.id && pickedBarang.unit_id) {
      const sisa = availableStock(pickedBarang.id, pickedBarang.unit_id);
      $id('stokView').value = idFormat(sisa);
    }
  }catch(e){}
}

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
      try{ hardResetSelection(true); }catch(e){
        try{ document.getElementById('unitChipsWrap')?.classList.add('d-none'); }catch(e){}
        pickedBarang=null; pickedUnit=null; pickedJasa=null;
      }
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
    try{ hardResetSelection(btnCariH && btnCariH.classList.contains('active')); }catch(e){}
  });
  document.getElementById('tabItem')?.addEventListener('shown.bs.tab', ()=>{
    setHeaderMode(btnDaftarH?.classList.contains('active'));
    try{ hardResetSelection(btnCariH && btnCariH.classList.contains('active')); }catch(e){}
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
      const isActive = (typeof pickedBarang==='object' && pickedBarang && Number(pickedBarang.id) === Number(it.id));
      html += `<div class="tile${isActive?' active':''}" data-id="${it.id}" data-name="${name}">
        <div class="ico">${thumb}</div>
        <div class="flex-1"><div class="name">${name}</div></div>
      </div>`;
    });
    h(wrap, html);
    wrap.querySelectorAll('.tile').forEach(t=> t.addEventListener('click', ()=>{
      wrap.querySelectorAll('.tile').forEach(x=>x.classList.remove('active'));
      t.classList.add('active');
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
          updateSelectedStockView();
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
      const isActive = (typeof pickedJasa==='object' && pickedJasa && Number(pickedJasa.id) === Number(it.id));
      html += `<div class="tile${isActive?' active':''}" data-id="${it.id}" data-name="${name}" data-harga="${harga}">
        <div class="ico">${thumb}</div>
        <div class="tile-content">
          <div class="name">${name}</div>
          <div class="price">Rp${harga.toLocaleString('id-ID')}</div>
        </div>
      </div>`;
    });
    h(wrap, html);
    wrap.querySelectorAll('.tile').forEach(t=> t.addEventListener('click', ()=>{
      wrap.querySelectorAll('.tile').forEach(x=>x.classList.remove('active'));
      t.classList.add('active');
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
  function renderBarangResults(){ try{ filterBarang(bInput.value); }catch(e){} }
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
  list.forEach((it,i)=>{
    const rows = (unitMap[String(it.id)]||unitMap[it.id]||[]);
    let minH=0,sumS=0; rows.forEach(r=>{ sumS+=Number(r.stok||0); minH = (minH===0? Number(r.harga||0) : Math.min(minH, Number(r.harga||0))); });
    const div=document.createElement('div'); div.className='result-tile'+(i===bCursor?' active':'');
    const nameSafe=(it.nama||'').replace(/</g,'&lt;');
    const thumb = it.image_url ? `<img src="${it.image_url}" alt="${nameSafe}" onerror=\"this.outerHTML='B'\">` : 'B';
    div.innerHTML=`<div class="ico">${thumb}</div><div class="flex-1"><div class="name">${nameSafe}</div><div class="meta">${minH>0?('mulai Rp'+minH.toLocaleString('id-ID')):'—'} • stok ${sumS.toLocaleString('id-ID')}</div></div>`;
    div.onclick=()=>selectBarang(it);
    bResults.appendChild(div);
  });
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
bSearch?.addEventListener('input',()=>{
  const has = !!(bSearch.value||'').trim();
  document.getElementById('barangClear')?.classList.toggle('d-none', !has);
  filterBarang(bSearch.value);
});
bSearch?.addEventListener('keydown',(e)=>{
  if(bResults.classList.contains('d-none')) return;
  if(e.key==='ArrowDown'){e.preventDefault(); bCursor=Math.min(bCursor+1,bList.length-1); renderResultsB(bList);}
  if(e.key==='ArrowUp'){e.preventDefault();   bCursor=Math.max(bCursor-1,0);               renderResultsB(bList);}
  if(e.key==='Enter'){e.preventDefault(); if(bCursor>=0) selectBarang(bList[bCursor]);}
  if(e.altKey && e.key==='Enter'){ e.preventDefault(); if(bCursor>=0){ selectBarang(bList[bCursor]); setTimeout(()=>document.getElementById('qtyBarang')?.focus(), 0);} }
  if(e.key==='Escape'){e.preventDefault(); bSearch.value=''; document.getElementById('barangClear')?.classList.add('d-none'); closeResultsB();}
});
document.addEventListener('click',(e)=>{ if(!bResults?.contains(e.target) && e.target!==bSearch) closeResultsB(); });
document.getElementById('barangClear')?.addEventListener('click', ()=>{ bSearch.value=''; bSearch.focus(); closeResultsB(); document.getElementById('barangClear')?.classList.add('d-none'); });

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
    const div=document.createElement('div'); div.className='tile' + ((typeof pickedBarang==='object' && pickedBarang && Number(pickedBarang.id)===Number(b.id)) ? ' active' : '');
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
    const b=document.createElement('button'); b.type='button';
    const isActive = (pickedBarang && Number(pickedBarang.unit_id) === Number(u.unit_id));
    b.className='unit-chip' + (isActive ? ' active' : '');
    b.dataset.unitId=u.unit_id; b.dataset.k=u.unit_kode; b.dataset.h=u.harga; b.dataset.s=u.stok;
    const sisa = availableStock(pickedBarang?.id||barangId, u.unit_id);
    b.innerHTML=`${u.unit_kode} <small>(stok ${idFormat(sisa)})</small>`;
    b.onclick=()=>selectUnitChip(b); unitChipsEl.appendChild(b);
  });
  unitChipsWrap.classList.toggle('d-none', rows.length===0);
  if(rows.length===1){ const first = unitChipsEl.querySelector('.unit-chip'); if(first){ first.click(); setTimeout(()=> document.getElementById('qtyBarang')?.focus(), 0); } }
}
function selectUnitChip(btn){
  unitChipsEl.querySelectorAll('.unit-chip').forEach(el=>el.classList.remove('active'));
  btn.classList.add('active');
  pickedBarang = {...(pickedBarang||{}), unit_id:+btn.dataset.unitId, unit_kode:btn.dataset.k, stok:+btn.dataset.s||0, harga:+btn.dataset.h||0 };
  updateSelectedStockView();
  $id('hargaBarang').value=idFormat(pickedBarang.harga||0);
  try{ document.getElementById('qtyBarang')?.focus(); }catch(e){}
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
  const avail = availableStock(pickedBarang.id, pickedBarang.unit_id);
  if(avail <= 0){ alert('Stok habis untuk unit ini.'); return; }
  if(harga <= 0){ alert('Harga tidak boleh 0.'); return; }
  if(qty>avail){ alert('Qty melebihi stok tersedia. Disetel ke maksimum.'); }
  const finalQty = Math.min(qty, avail);
  const line={tipe:'barang', barang_id:pickedBarang.id, jasa_id:null, unit_id:pickedBarang.unit_id, unit_kode:pickedBarang.unit_kode, nama:pickedBarang.nama, qty, harga, discType:'', discVal:0, discManual:false};
  line.qty = finalQty;
  applyAutoDisc(line);
  cart.push(line);
  renderCart(); $id('qtyBarang').value=1; updateSelectedStockView(); renderUnitChips(pickedBarang.id);
}
function tambahJasa(){
  if(!pickedJasa || !pickedJasa.id){ alert('Pilih jasa.'); return; }
  const qty=+$id('qtyJasa').value||1, harga=clean($id('hargaJasa').value);
  if(harga <= 0){ alert('Harga tidak boleh 0.'); return; }
  const line={tipe:'jasa', barang_id:null, jasa_id:pickedJasa.id, unit_id:null, unit_kode:null, nama:pickedJasa.nama, qty, harga, discType:'', discVal:0, discManual:false};
  applyAutoDisc(line);
  cart.push(line);
  renderCart(); $id('qtyJasa').value=1;
}

/* render cart */
function renderCart(){
  const body=$id('cartBody'), hidden=$id('itemsHidden');
  body.innerHTML=''; hidden.innerHTML='';
  if(cart.length===0){ body.innerHTML='<tr class="text-muted"><td colspan="8" class="text-center py-4">Belum ada item</td></tr>'; updateTotals(); return; }
  let grand=0;
  cart.forEach((it,i)=>{
    // normalisasi field diskon
    it.discType = it.discType || '';
    it.discVal  = Number(it.discVal||0);
    const gross = it.qty*it.harga;
    let dAmt = 0;
    if(it.discType==='percent'){ dAmt = Math.floor(gross * Math.min(100, Math.max(0,it.discVal)) / 100); }
    else if(it.discType==='amount'){ dAmt = Math.min(gross, Math.max(0,it.discVal)); }
    const sub = Math.max(0, gross - dAmt); grand+=sub;
    const tr=document.createElement('tr');
    let plusDisabled = '';
    let maxAttr = '';
    if(it.tipe==='barang'){
      const avail = availableStock(it.barang_id, it.unit_id, i);
      if(Number(it.qty||0) >= avail){ plusDisabled = 'disabled'; }
      maxAttr = ` max="${avail}"`;
    }
    tr.innerHTML=`
      <td><span class="badge ${it.tipe==='barang'?'text-bg-primary':'text-bg-success'} pill text-capitalize">${it.tipe}</span></td>
      <td>${it.nama}</td>
      <td>${it.tipe==='barang'?(it.unit_kode||'-'):'-'}</td>
      <td class="text-end">
        <div class="qty-row">
          <button type="button" class="btn btn-outline-secondary" onclick="editQty(${i},-1)">−</button>
          <input type="number" value="${it.qty}" min="1"${maxAttr} class="form-control" onchange="onCellEdit(${i},'qty',this)">
          <button type="button" class="btn btn-outline-secondary" onclick="editQty(${i},1)" ${plusDisabled}>+</button>
        </div>
      </td>
      <td class="text-end">
        <input type="text" value="${idFormat(it.harga)}" class="form-control text-end price-input ${Number(it.harga||0)<=0?'is-invalid-field':''}"
               inputmode="numeric" title="Rp${idFormat(it.harga)}"
               oninput="onPriceTyping(${i}, this)" onblur="onPriceBlur(${i}, this)">
      </td>
      <td class="text-end">
        <div class="input-group input-group-sm">
          <input type="text" value="${it.discType==='percent' ? (Number(it.discVal)||0) : idFormat(Number(it.discVal)||0)}" class="form-control text-end discount-input"
                 inputmode="numeric" placeholder="0"
                 oninput="onDiscTyping(${i}, this)" onblur="onDiscBlur(${i}, this)">
          <select class="form-select" onchange="onDiscTypeChange(${i}, this)">
            <option value="" ${!it.discType?'selected':''}>Pilih Diskon</option>
            <option value="amount" ${it.discType==='amount'?'selected':''}>Rp</option>
            <option value="percent" ${it.discType==='percent'?'selected':''}>%</option>
          </select>
        </div>
      </td>
      <td class="text-end fw-semibold" id="rowSub_${i}" title="${rupiah(sub)}">${rupiah(sub)}</td>
      <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusItem(${i})"><i class="bi bi-x-lg"></i></button></td>
    `;
    body.appendChild(tr);

    hidden.insertAdjacentHTML('beforeend',`
      <input type="hidden" name="items[${i}][tipe_item]" value="${it.tipe}">
      <input type="hidden" name="items[${i}][barang_id]" value="${it.barang_id ?? ''}">
      <input type="hidden" name="items[${i}][jasa_id]" value="${it.jasa_id ?? ''}">
      <input type="hidden" name="items[${i}][unit_id]" value="${it.unit_id ?? ''}">
      <input type="hidden" id="hid_qty_${i}" name="items[${i}][jumlah]" value="${it.qty}">
      <input type="hidden" id="hid_harga_${i}" name="items[${i}][harga_satuan]" value="${it.harga}">
      <input type="hidden" id="hid_disc_type_${i}" name="items[${i}][discount_type]" value="${it.discType||''}">
      <input type="hidden" id="hid_disc_value_${i}" name="items[${i}][discount_value]" value="${Number(it.discVal)||0}">
    `);
  });
  $id('grandTotal').textContent = rupiah(grand);
  $id('totalTop').textContent   = rupiah(grand);
  hitungKembalian();
  try{ updateSelectedStockView(); if(pickedBarang?.id){ renderUnitChips(pickedBarang.id); } }catch(e){}
}
// ========== Input handlers dengan live update TANPA re-render penuh ==========
function updateHiddenForRow(i){
  const it = cart[i];
  const hq = document.getElementById(`hid_qty_${i}`); if(hq) hq.value = it.qty;
  const hh = document.getElementById(`hid_harga_${i}`); if(hh) hh.value = it.harga;
  const ht = document.getElementById(`hid_disc_type_${i}`); if(ht) ht.value = it.discType||'';
  const hv = document.getElementById(`hid_disc_value_${i}`); if(hv) hv.value = Number(it.discVal)||0;
}
function updateRowSubtotal(i){
  const it = cart[i];
  const gross = Number(it.qty||0) * Number(it.harga||0);
  let dAmt = 0;
  if(it.discType==='percent') dAmt = Math.floor(gross * Math.min(100, Math.max(0, Number(it.discVal||0))) / 100);
  else if(it.discType==='amount') dAmt = Math.min(gross, Math.max(0, Number(it.discVal||0)));
  const sub = Math.max(0, gross - dAmt);
  const cell = document.getElementById(`rowSub_${i}`);
  if(cell){ cell.textContent = rupiah(sub); cell.title = rupiah(sub); }
}
function onPriceTyping(i, el){
  const v = clean(el.value);
  cart[i].harga = v;
  updateHiddenForRow(i); updateRowSubtotal(i); updateTotals();
}
function onPriceBlur(i, el){
  const v = clean(el.value); cart[i].harga = v; el.value = idFormat(v);
  updateHiddenForRow(i); updateRowSubtotal(i); updateTotals();
}
function editQty(i,d){
  const it = cart[i];
  if(it.tipe==='barang'){
    const avail = availableStock(it.barang_id, it.unit_id, i);
    const next = Math.max(1, Math.min(avail, (Number(it.qty||1)+d)));
    if(d>0 && next === Number(it.qty||1)) { alert('Qty melebihi stok.'); return; }
    it.qty = next;
  } else {
    it.qty = Math.max(1,(it.qty||1)+d);
  }
  if(!cart[i].discManual) applyAutoDisc(cart[i]);
  renderCart(); updateSelectedStockView(); if(pickedBarang?.id){ renderUnitChips(pickedBarang.id); }
}
function onCellEdit(i,field,el){
  let v=+el.value||0;
  if(field==='qty'){
    v=Math.max(1,v);
    const it = cart[i];
    if(it.tipe==='barang'){
      const avail = availableStock(it.barang_id, it.unit_id, i);
      if(v>avail){ v = avail; alert('Qty melebihi stok. Disetel ke maksimum.'); }
    }
  }
  cart[i][field]=v;
  if(field==='qty' && !cart[i].discManual) applyAutoDisc(cart[i]);
  renderCart(); updateSelectedStockView(); if(pickedBarang?.id){ renderUnitChips(pickedBarang.id); }
}
function onDiscTypeChange(i, sel){
  cart[i].discType = sel.value; cart[i].discManual=true;
  if(sel.value==='percent'){
    cart[i].discVal = Math.min(100, Math.max(0, Number(cart[i].discVal||0)));
  } else if(sel.value==='amount') {
    cart[i].discVal = Number(cart[i].discVal||0);
  } else {
    cart[i].discVal = 0;
  }
  updateHiddenForRow(i); updateRowSubtotal(i); updateTotals();
}
function onDiscTyping(i, el){
  const t = cart[i].discType||''; let v = el.value||'';
  cart[i].discManual=true;
  if(t==='percent'){
    v = v.replace(/[^0-9]/g,'');
    cart[i].discVal = Math.min(100, Math.max(0, Number(v||0)));
    el.value = String(cart[i].discVal);
  } else if(t==='amount'){
    const raw = clean(v);
    cart[i].discVal = raw;
    // Format ribuan langsung agar mudah dibaca saat mengetik
    el.value = idFormat(raw);
  } else {
    cart[i].discVal = 0;
  }
  updateHiddenForRow(i); updateRowSubtotal(i); updateTotals();
}
function onDiscBlur(i, el){
  const t = cart[i].discType||'';
  if(t==='amount'){
    el.value = idFormat(clean(el.value));
  } else if(t==='percent'){
    let v = String(el.value||'').replace(/[^0-9]/g,''); v = String(Math.min(100, Math.max(0, Number(v||0))));
    el.value = v;
  }
  updateHiddenForRow(i); updateRowSubtotal(i); updateTotals();
}
function hapusItem(i){ cart.splice(i,1); renderCart(); updateSelectedStockView(); if(pickedBarang?.id){ renderUnitChips(pickedBarang.id); } }

function kosongkanKeranjang(){ if(!confirm('Kosongkan keranjang?')) return; cart=[]; renderCart(); updateSelectedStockView(); if(pickedBarang?.id){ renderUnitChips(pickedBarang.id); } }
function updateTotals(){
  const itemsTotal = cart.reduce((sum,it)=>{
    const gross=it.qty*it.harga; let d=0; if(it.discType==='percent') d=Math.floor(gross*Math.min(100,Math.max(0,Number(it.discVal||0)))/100); else if(it.discType==='amount') d=Math.min(gross, Number(it.discVal||0)); return sum + Math.max(0, gross-d);
  },0);
  const invType = ($id('discType')?.value)||'';
  const invVal  = Number(($id('discValue')?.value)||0);
  let invAmt=0; if(invType==='percent'){ invAmt=Math.floor(itemsTotal*Math.min(100,Math.max(0,invVal))/100); } else if(invType==='amount'){ invAmt=Math.min(itemsTotal, Math.max(0,invVal)); }
  const grand = Math.max(0, itemsTotal - invAmt);
  $id('discAmountPreview').textContent = rupiah(invAmt);
  $id('grandTotal').textContent=rupiah(grand);
  $id('totalTop').textContent=rupiah(grand);
  hitungKembalian();
  updateFormValidity();
}

/* pembayaran */
function hitungKembalian(){
  // total setelah diskon item + diskon nota
  const itemsTotal = cart.reduce((sum,it)=>{
    const gross=it.qty*it.harga; let d=0; if(it.discType==='percent') d=Math.floor(gross*Math.min(100,Math.max(0,Number(it.discVal||0)))/100); else if(it.discType==='amount') d=Math.min(gross, Number(it.discVal||0)); return sum + Math.max(0, gross-d);
  },0);
  const invType = ($id('discType')?.value)||'';
  const invVal  = Number(($id('discValue')?.value)||0);
  let invAmt=0; if(invType==='percent'){ invAmt=Math.floor(itemsTotal*Math.min(100,Math.max(0,invVal))/100); } else if(invType==='amount'){ invAmt=Math.min(itemsTotal, Math.max(0,invVal)); }
  const total = Math.max(0, itemsTotal - invAmt);
  const dibayar = clean($id('dibayar').value);
  const kembali = Math.max(0, dibayar - total);
  $id('kembalianView').textContent = rupiah(kembali);
  // Update validitas tombol simpan ketika nilai bayar/total berubah
  updateFormValidity();
}
$id('btnUangPas')?.addEventListener('click', ()=>{
  if ($id('dibayar_view').disabled) return; // QRIS → nonaktif
  let itemsTotal=0; cart.forEach(it=>{ const gross=it.harga*it.qty; let d=0; if(it.discType==='percent') d=Math.floor(gross*Math.min(100,Math.max(0,Number(it.discVal||0)))/100); else if(it.discType==='amount') d=Math.min(gross, Number(it.discVal||0)); itemsTotal+=Math.max(0,gross-d); });
  const invType = ($id('discType')?.value)||'';
  const invVal  = Number(($id('discValue')?.value)||0);
  let invAmt=0; if(invType==='percent') invAmt=Math.floor(itemsTotal*Math.min(100,Math.max(0,invVal))/100); else if(invType==='amount') invAmt=Math.min(itemsTotal, Math.max(0,invVal));
  const total = Math.max(0, itemsTotal - invAmt);
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
  updateFormValidity();
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
// Diskon nota input bindings
const discTypeSel = $id('discType');
const discValView = $id('discValue_view');
const discValHid  = $id('discValue');
function onDiscTypeInvoice(){
  // reset tampilan input
  const t = discTypeSel.value;
  if(t===''){
    discValView.value = '';
    discValView.disabled = true;
    discValView.placeholder = '0';
    discValHid.value = 0;
  } else {
    discValView.disabled = false;
    discValView.value = '0';
    discValHid.value = 0;
  }
  updateTotals();
}
discTypeSel?.addEventListener('change', onDiscTypeInvoice);
discValView?.addEventListener('input', ()=>{
  const t = discTypeSel.value;
  if(t==='') { discValHid.value = 0; return updateTotals(); }
  if(t==='percent'){
    let v = discValView.value.replace(/[^0-9]/g,''); v = String(Math.min(100, Math.max(0, Number(v||0))));
    discValHid.value = v; // view biarkan apa adanya untuk kenyamanan
  } else if(t==='amount'){
    const raw = clean(discValView.value); discValView.value = idFormat(raw); discValHid.value = raw;
  } else {
    discValHid.value = 0;
  }
  updateTotals();
});

// Aturan diskon otomatis (client)
function computeDiscAmount(gross, type, val){
  if(type==='percent') return Math.floor(gross * Math.min(100, Math.max(0, Number(val||0))) / 100);
  if(type==='amount') return Math.min(gross, Math.max(0, Number(val||0)));
  return 0;
}
function bestRuleFor(line){
  const tipe=line.tipe; const id = (tipe==='barang'? line.barang_id : line.jasa_id) || 0; const qty = Number(line.qty||0); const gross = Number(line.qty||0) * Number(line.harga||0);
  let candidates = [];
  const byId = (discRules[tipe]||{})[String(id)] || (discRules[tipe]||{})[id] || [];
  const wild = (discRules[tipe]||{})['*'] || [];
  candidates = [...byId, ...wild];
  let best = {type:'', value:0, amount:0};
  for(const r of candidates){ if(qty >= (r.min_qty||0)){ const amt=computeDiscAmount(gross, r.type, r.value); if(amt>best.amount){ best={type:r.type, value:r.value, amount:amt}; } } }
  return best;
}
function applyAutoDisc(line){
  const gross = Number(line.qty||0) * Number(line.harga||0);
  const auto = bestRuleFor(line); const manualAmt = computeDiscAmount(gross, line.discType||'', line.discVal||0);
  if(auto.amount > manualAmt){ line.discType = auto.type; line.discVal = auto.value; }
}

renderBarangGrid('*'); renderJasaGrid('*'); renderCart(); onMetodeChange(); onDiscTypeInvoice();
// Hard reset helper: bersihkan semua selection dan highlight
function hardResetSelection(focusSearch=false){
  pickedBarang=null; pickedUnit=null; pickedJasa=null;
  try{ document.querySelectorAll('#barangGrid .tile.active').forEach(el=>el.classList.remove('active')); }catch(e){}
  try{ document.querySelectorAll('#jasaGrid .tile.active').forEach(el=>el.classList.remove('active')); }catch(e){}
  try{ document.getElementById('unitChipsWrap')?.classList.add('d-none'); document.getElementById('unitChips').innerHTML=''; }catch(e){}
  try{ document.getElementById('barangSelectedInfo')?.classList.add('d-none'); document.getElementById('jasaSelectedInfo')?.classList.add('d-none'); }catch(e){}
  try{ document.getElementById('stokView').value = 0; document.getElementById('hargaBarang').value = idFormat(0); document.getElementById('hargaJasa').value = idFormat(0); }catch(e){}
  if (focusSearch){ try{ document.getElementById('barangSearch')?.focus(); }catch(e){} }
}

// Validasi form: cegah simpan ketika ada harga 0 atau tidak ada item
function updateFormValidity(){
  const btn = document.getElementById('btnSimpan'); if(!btn) return;
  const hasItems = cart.length > 0;
  const anyZeroPrice = cart.some(it => it.tipe==='barang' || it.tipe==='jasa' ? Number(it.harga||0) <= 0 : false);
  // Hitung total saat ini untuk aturan bayar
  const itemsTotal = cart.reduce((sum,it)=>{
    const gross=it.qty*it.harga; let d=0; if(it.discType==='percent') d=Math.floor(gross*Math.min(100,Math.max(0,Number(it.discVal||0)))/100); else if(it.discType==='amount') d=Math.min(gross, Number(it.discVal||0)); return sum + Math.max(0, gross-d);
  },0);
  const invType = ($id('discType')?.value)||'';
  const invVal  = Number(($id('discValue')?.value)||0);
  let invAmt=0; if(invType==='percent') invAmt=Math.floor(itemsTotal*Math.min(100,Math.max(0,invVal))/100); else if(invType==='amount') invAmt=Math.min(itemsTotal, Math.max(0,invVal));
  const total   = Math.max(0, itemsTotal - invAmt);
  const dibayar = clean($id('dibayar').value);
  const method  = (document.getElementById('metodeBayar')?.value)||'cash';
  let invalid = !hasItems || anyZeroPrice;
  let reason = !hasItems ? 'Tambah minimal satu item.' : (anyZeroPrice ? 'Periksa: ada harga item 0.' : '');
  if (!invalid) {
    if (method !== 'qris' && dibayar < total) { invalid = true; reason = 'Nominal dibayar kurang dari total.'; }
    if (method === 'qris' && dibayar > 0 && dibayar < total) { invalid = true; reason = 'Untuk QRIS, kosongkan Nominal Dibayar.'; }
  }
  btn.disabled = invalid; btn.title = invalid ? reason : '';
}
</script>
@endsection
