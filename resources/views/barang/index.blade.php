@extends('layouts.app')
@section('title','Daftar Barang')

@section('content')
@include('partials.neo-theme')
<style>
  /* Spacing dari header halaman */
  #barang-index{ margin-top:14px; }
  #barang-index .card{border:0;border-radius:16px}
  #barang-index .card-header{
    background:linear-gradient(135deg,#f7f9fc,#eef2f7);
    border-bottom:1px solid #e9edf3;
    position:relative; padding:14px 16px 18px; margin-bottom:6px;
  }
  #barang-index .card-body{ padding-top:14px; }
  .muted{color:#8a94a6}
  .hidden,.hide{display:none !important}

  /* ===== View switch ===== */
  .mode-switch .btn{border-radius:12px 0 0 12px}
  .mode-switch .btn:last-child{border-radius:0 12px 12px 0}

  /* ===== Filter ===== */
  .filter-wrap{position:relative}
  .btn-filter{display:inline-flex;align-items:center;gap:8px;border-radius:12px;padding:.5rem .75rem;background:rgba(255,223,185,.45);border:1px solid rgba(164,25,61,.25);color:var(--brand);font-weight:600}
  .btn-filter:hover{background:rgba(255,223,185,.65);border-color:rgba(164,25,61,.35);color:var(--brand)}
  .btn-filter .ic{width:18px;height:18px}
  .filter-dropdown{position:absolute;z-index:30;top:110%;left:0;min-width:320px;max-width:min(92vw,560px);background:#fff;border:1px solid #e6ebf2;border-radius:14px;box-shadow:0 12px 28px rgba(13,110,253,.08);padding:14px;display:none}
  .filter-dropdown.open{display:block}
  .filter-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
  .filter-row label{min-width:120px;font-weight:600;color:#4a5568}
  .filter-dropdown .form-control,.filter-dropdown .form-select{border-radius:12px}
  .filter-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:6px}

  /* Brand buttons (scoped) */
  #barang-index .btn-primary{
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    border-color: var(--brand-2);
    box-shadow: 0 6px 18px rgba(164,25,61,.28);
  }
  #barang-index .btn-primary:hover{ filter:brightness(1.05); }
  #barang-index .btn-outline-primary{
    color: var(--brand);
    border-color: rgba(164,25,61,.45);
  }
  #barang-index .btn-outline-primary:hover{
    background: rgba(255,223,185,.65);
    color: var(--brand);
    border-color: rgba(164,25,61,.55);
  }

  /* ===== Grid (kartu) ===== */
  .grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}
  .item-card{display:flex;flex-direction:column;gap:10px;border:1px solid #e6ebf2;border-radius:16px;background:#fff;padding:14px;transition:transform .15s, box-shadow .15s, border-color .15s}
  .item-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(164,25,61,.08);border-color:rgba(164,25,61,.18)}
  .card-head{display:flex;justify-content:space-between;align-items:center;gap:8px}
  .name{font-weight:700;font-size:1.04rem;line-height:1.2}
  .badge-cat{background:var(--peach);color:var(--brand);border:1px solid rgba(164,25,61,.25);font-weight:600}

  /* Unit chips */
  .unit-chip{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:.35rem .6rem;border-radius:12px;border:1px solid #e6ebf2;background:#f9fbfd;font-size:.86rem}
  .unit-left{display:flex;align-items:center;gap:6px}
  .unit-code{font-weight:700;text-transform:uppercase;letter-spacing:.2px;font-size:.78rem}
  .unit-sep{color:#b9c2d0}
  .price{white-space:nowrap}

  /* Aksen per unit */
  .u-pcs{background:#eef5ff;border-color:#d9e6ff}.u-pcs .unit-code{color:#0d6efd}
  .u-paket{background:#f3e8ff;border-color:#e6d5ff}.u-paket .unit-code{color:#7c3aed}
  .u-box{background:#e8f7ef;border-color:#cfeedd}.u-box .unit-code{color:#16a34a}
  .u-lembar{background:#e6f8fb;border-color:#cdeef3}.u-lembar .unit-code{color:#0ea5b7}
  .u-lusin{background:#fff4e5;border-color:#ffe1bd}.u-lusin .unit-code{color:#d97706}

  /* Jarak antar unit di grid */
  .unit-list{display:flex;flex-direction:column;gap:8px;}

  /* Total stok */
  .total-line{display:flex;align-items:center;gap:8px;color:#6b7686;font-weight:600;font-size:.88rem}

  /* Actions (grid) */
  .actions{display:flex;gap:8px;justify-content:flex-end;align-items:center;margin-top:auto;flex-wrap:nowrap}
  .actions .btn{border-radius:10px;white-space:nowrap}
  #barang-index .btn-unit{background:rgba(255,223,185,.45);border:1px solid rgba(164,25,61,.25);color:var(--brand);padding-inline:10px 12px;display:inline-flex;align-items:center;gap:6px;max-width:150px}
  #barang-index .btn-unit:hover{background:rgba(255,223,185,.65);border-color:rgba(164,25,61,.35);color:var(--brand)}
  .btn-detail .ic{width:16px;height:16px;margin-right:2px}
  .btn-detail .label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;max-width:110px}
  #barang-index .btn-warning{background:#f7c948;border-color:#f7c948;color:#3b2f00}
  #barang-index .btn-warning:hover{background:#f4b53d;border-color:#f4b53d}
  #barang-index .btn-danger{background:#e6707c;border-color:#e6707c}
  #barang-index .btn-danger:hover{background:#da5a68;border-color:#da5a68}

  /* Swap Edit <-> Hapus */
  .swap{display:inline-flex;align-items:center;gap:6px}
  .swap-pad{position:relative;overflow:hidden;border-radius:10px;display:inline-block;width:96px;height:30px}
  .swap-pad .btn-main{position:absolute;inset:0;transition:transform .2s ease,opacity .2s ease;z-index:1;pointer-events:auto}
  .swap-pad .del-form{position:absolute;inset:0;margin:0;z-index:2;transition:transform .2s ease,opacity .2s ease;pointer-events:none}
  .swap-pad .btn-alt{width:100%;height:100%}
  .swap-pad .del-form{transform:translateX(120%);opacity:0}
  .swap-pad.show-delete .btn-main{transform:translateX(-120%);opacity:0;pointer-events:none}
  .swap-pad.show-delete .del-form{transform:translateX(0);opacity:1;pointer-events:auto}
  .swap .btn-toggle{border:1px solid #e6ebf2;background:#f7f9fc;border-radius:10px;padding:6px 8px;font-weight:700;line-height:1}

  /* ===== Tabel ===== */
  #tableWrap{display:none}
  .table th{white-space:nowrap}
  #barang-index .tbl-total{display:inline-flex;align-items:center;gap:6px;background:#fff8ef;border:1px solid #ffe2c2;color:#a45510;font-weight:700;border-radius:12px;padding:.25rem .55rem;white-space:nowrap}
  tr.unit-detail-row{background:#fcfdff}
  tr.unit-detail-row td{border-top:0;border-bottom:1px dashed #e6ebf2}
  tr.unit-detail-row td:first-child{color:#95a1b3}
  td .tbl-actions{display:flex;justify-content:flex-end;align-items:center;gap:8px;flex-wrap:nowrap;white-space:nowrap}
  td .tbl-actions .btn{border-radius:10px}
  td .tbl-actions .btn-unit{max-width:120px;padding:.25rem .5rem}
  td .tbl-actions .swap-pad{height:30px}
  #barang-index th.col-actions-th{text-align:left;padding-left:12px}
  #barang-index td.col-actions-td{text-align:right}
</style>

<div id="barang-index" class="card shadow-sm mt-3">
  <div class="card-header">
    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
      <div class="d-flex align-items-center gap-2">
        {{-- Switch Grid/Tabel --}}
        <div class="mode-switch btn-group" role="group" aria-label="View switch">
          <button id="btnGrid" type="button" class="btn btn-primary btn-sm">Grid</button>
          <button id="btnTable" type="button" class="btn btn-outline-primary btn-sm">Tabel</button>
        </div>

        {{-- Filter --}}
        <div class="filter-wrap">
          <button id="btnFilter" type="button" class="btn btn-filter">
            <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12 10 19 14 21 14 12 22 3"/></svg>
            Filter Berdasarkan
          </button>
          <div id="filterDropdown" class="filter-dropdown">
            <div class="filter-row">
              <label for="q">Cari</label>
              <div class="input-group">
                <span class="input-group-text" aria-hidden="true">
                  <svg class="ic" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <input id="q" type="text" class="form-control" placeholder="Nama / kategori / keterangan…">
              </div>
            </div>
            <div class="filter-row">
              <label for="fCat">Kategori</label>
              <select id="fCat" class="form-select">
                <option value="">Semua</option>
                @php $kategoriOpts = $barangs->pluck('kategori')->filter()->unique()->values(); @endphp
                @foreach($kategoriOpts as $kat)
                  <option value="{{ strtolower($kat) }}">{{ $kat }}</option>
                @endforeach
              </select>
            </div>
            <div class="filter-row">
              <label for="sortBy">Urutkan</label>
              <select id="sortBy" class="form-select">
                <option value="name_asc">Nama (A–Z)</option>
                <option value="stok_desc">Stok (pcs) Tertinggi</option>
                <option value="newest">Terbaru</option>
              </select>
            </div>
            <div class="filter-row">
              <label for="fLow">Stok sedikit</label>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="form-check form-switch m-0">
                  <input class="form-check-input" type="checkbox" id="fLow">
                </div>
                <span class="muted">Ambang:</span>
                <input id="fThresh" type="number" class="form-control" value="10" min="0" style="width:100px">
                <span class="muted">pcs</span>
              </div>
            </div>
            <div class="filter-actions">
              <button id="btnReset" type="button" class="btn btn-light">Reset</button>
              <button id="btnApply" type="button" class="btn btn-primary">Terapkan</button>
            </div>
          </div>
        </div>
      </div>

      <a href="{{ route('barang.create') }}" class="btn btn-primary" style="border-radius:12px">+ Tambah</a>
    </div>
  </div>

  <div class="card-body">
    @include('partials.flash-neo')
    {{-- ====================== GRID MODE ====================== --}}
    <div id="gridWrap" class="grid">
      @forelse($barangs as $b)
        @php
          $rpFull  = fn($n)=> is_null($n)? null : 'Rp'.number_format($n,0,',','.');
          $rpShort = function($n){ if(is_null($n)) return '—'; $a=abs($n);
            if($a<1_000) return 'Rp'.number_format($n,0,',','.');
            if($a<1_000_000) return 'Rp'.rtrim(rtrim(number_format($n/1_000,1,',','.'),'0'),',').'K';
            if($a<1_000_000_000) return 'Rp'.rtrim(rtrim(number_format($n/1_000_000,1,',','.'),'0'),',').'Jt';
            return 'Rp'.rtrim(rtrim(number_format($n/1_000_000_000,1,',','.'),'0'),',').'M'; };
          $rpSmart = function($n) use ($rpFull,$rpShort){ if(is_null($n)) return '—'; $f=$rpFull($n); return (mb_strlen($f)>10)?$rpShort($n):$f; };

          $byKode    = $b->units->keyBy(fn($u)=>strtolower($u->kode));
          $totalPcs  = $b->units->sum(fn($u)=> (int)($u->pivot?->stok ?? 0) * max(1,(int)$u->konversi));
          $createdTs = optional($b->created_at)->getTimestamp() ?? 0;
          $img       = $b->image_url ?: 'https://dummyimage.com/800x600/e9eef6/7a869a&text=No+Image';

          $primary      = $byKode->get('pcs') ?: $b->units->first();
          $primaryCode  = strtolower($primary?->kode ?? '');
          $uid          = 'u-'.$b->id;
          $swapId       = 'swap-'.$b->id;

          $detailParts=[];
          foreach($b->units as $u){
            $stok=(int)($u->pivot?->stok ?? 0); if($stok<=0) continue;
            $part=$stok.' '.$u->kode;
            if(($u->konversi??1)>1 && strtolower($u->kode)!=='pcs') $part.=' × '.$u->konversi;
            $detailParts[]=$part;
          }
          $rumus=$detailParts?implode(' + ',$detailParts).' = '.number_format($totalPcs,0,',','.').' pcs':'';
        @endphp

        <div class="item-card"
             data-row
             data-name="{{ strtolower($b->nama) }}"
             data-kategori="{{ strtolower($b->kategori ?? '') }}"
             data-text="{{ strtolower(($b->nama ?? '').' '.($b->kategori ?? '').' '.($b->keterangan ?? '')) }}"
             data-stokpcs="{{ $totalPcs }}"
             data-created="{{ $createdTs }}">

          <div style="width:100%;aspect-ratio:4/3;border-radius:12px;overflow:hidden;background:#f2f5fa">
            <img src="{{ $img }}" alt="{{ $b->nama }}" style="width:100%;height:100%;object-fit:cover">
          </div>

          <div class="card-head">
            <div class="name">{{ $b->nama }}</div>
            <span class="badge rounded-pill badge-cat">{{ $b->kategori ?: '—' }}</span>
          </div>

          <div class="unit-list" id="{{ $uid }}">
            @if($primary)
              @php $stok=(int)($primary->pivot?->stok ?? 0); $hFull=$rpFull($primary->pivot?->harga); $hShow=$rpSmart($primary->pivot?->harga); @endphp
              <div class="unit-chip u-{{ strtolower($primary->kode) }}"
                   title="{{ strtoupper($primary->kode) }} • {{ $stok }} • {{ $hFull ?? '—' }}">
                <div class="unit-left"><span class="unit-code">{{ strtoupper($primary->kode) }}</span><span class="unit-sep">•</span><span class="muted">Stok:</span><span>{{ $stok }}</span></div>
                <div class="price">{{ $hShow }}</div>
              </div>
            @endif
            @foreach($b->units as $u)
              @continue(strtolower($u->kode) === $primaryCode)
              @php $stok=(int)($u->pivot?->stok ?? 0); $hFull=$rpFull($u->pivot?->harga); $hShow=$rpSmart($u->pivot?->harga); @endphp
              <div class="unit-chip hidden u-{{ strtolower($u->kode) }} chip-extra"
                   title="{{ $u->kode }} • {{ $stok }} • {{ $hFull ?? '—' }}">
                <div class="unit-left"><span class="unit-code">{{ $u->kode }}</span><span class="unit-sep">•</span><span class="muted">Stok:</span><span>{{ $stok }}</span></div>
                <div class="price">{{ $hShow }}</div>
              </div>
            @endforeach
          </div>

          <div class="total-line" @if($rumus) title="{{ $rumus }}" @endif>
            <span>📦</span><span>Total Stok ≈ {{ number_format($totalPcs,0,',','.') }} pcs</span>
          </div>

          <div class="actions">
            @if($b->units->count() > 1)
              <button type="button" class="btn btn-sm btn-unit btn-detail"
                      data-toggle-target="{{ $uid }}" data-state="closed">
                <svg class="ic ic-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="ic ic-eye-off hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"/><path d="M1 1l22 22"/><path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/><path d="M14.12 14.12 9.88 9.88"/></svg>
                <span class="label">Info Detail</span>
              </button>
            @endif
            <div class="swap">
              <div id="{{ $swapId }}" class="swap-pad">
                <a href="{{ route('barang.edit',$b) }}" class="btn btn-warning btn-sm btn-main">Edit</a>
                <form action="{{ route('barang.destroy',$b) }}" method="POST" class="del-form" onsubmit="return confirm('Hapus {{ $b->nama }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm btn-alt">Hapus</button>
                </form>
              </div>
              <button type="button" class="btn btn-toggle" data-swap-target="{{ $swapId }}">&gt;</button>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted">Belum ada data</div>
      @endforelse
    </div>

    {{-- ====================== TABLE MODE ====================== --}}
    <div id="tableWrap" class="table-responsive mt-2">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th><th>Gambar</th><th>Nama</th><th>Kategori</th>
            <th>Unit (PCS) & Harga</th><th>Total (pcs)</th><th class="text-nowrap">Keterangan</th>
            <th class="col-actions-th" style="width:300px">Aksi</th>
          </tr>
        </thead>
        <tbody id="tblBody">
          @php
            $rpFullT  = fn($n)=> is_null($n)? null : 'Rp'.number_format($n,0,',','.');
            $rpShortT = function($n){ if(is_null($n)) return '—'; $a=abs($n);
              if($a<1_000) return 'Rp'.number_format($n,0,',','.');
              if($a<1_000_000) return 'Rp'.rtrim(rtrim(number_format($n/1_000,1,',','.'),'0'),',').'K';
              if($a<1_000_000_000) return 'Rp'.rtrim(rtrim(number_format($n/1_000_000,1,',','.'),'0'),',').'Jt';
              return 'Rp'.rtrim(rtrim(number_format($n/1_000_000_000,1,',','.'),'0'),',').'M'; };
            $rpSmartT = function($n) use ($rpFullT,$rpShortT){ if(is_null($n)) return '—'; $f=$rpFullT($n); return (mb_strlen($f)>10)?$rpShortT($n):$f; };
          @endphp

          @forelse ($barangs as $i => $b)
            @php
              $byKode = $b->units->keyBy(fn($u)=>strtolower($u->kode));
              $primary = $byKode->get('pcs') ?: $b->units->first();
              $primaryCode = strtolower($primary?->kode ?? '');
              $totalPcs = $b->units->sum(fn($u)=> (int)($u->pivot?->stok ?? 0) * max(1,(int)$u->konversi));
              $createdTs = optional($b->created_at)->getTimestamp() ?? 0;
              $img = $b->image_url ?: 'https://dummyimage.com/160x120/e9eef6/7a869a&text=No+Image';
              $swapId = 'tswap-'.$b->id;
            @endphp

            {{-- BARIS UTAMA --}}
            <tr data-row data-barangid="{{ $b->id }}"
                data-name="{{ strtolower($b->nama) }}"
                data-kategori="{{ strtolower($b->kategori ?? '') }}"
                data-text="{{ strtolower(($b->nama ?? '').' '.($b->kategori ?? '').' '.($b->keterangan ?? '')) }}"
                data-stokpcs="{{ $totalPcs }}"
                data-created="{{ $createdTs }}">
              <td>{{ $barangs->firstItem() + $i }}</td>
              <td><img src="{{ $img }}" alt="{{ $b->nama }}" style="width:64px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e6ebf2;"></td>
              <td class="fw-semibold">{{ $b->nama }}</td>
              <td><span class="badge rounded-pill badge-cat">{{ $b->kategori ?: '—' }}</span></td>
              <td>
                @if($primary)
                  @php $stok=(int)($primary->pivot?->stok ?? 0); $hS=$rpSmartT($primary->pivot?->harga); @endphp
                  <span class="unit-chip u-{{ strtolower($primary->kode) }}">
                    <span class="unit-left"><span class="unit-code">{{ strtoupper($primary->kode) }}</span><span class="unit-sep">•</span><span class="muted">Stok:</span><span>{{ $stok }}</span></span>
                    <span class="price">{{ $hS }}</span>
                  </span>
                @endif
              </td>
              <td><span class="tbl-total" title="Total stok dalam PCS">{{ number_format($totalPcs,0,',','.') }} pcs</span></td>
              <td class="text-truncate" style="max-width:280px;">{!! $b->keterangan ? e(\Illuminate\Support\Str::limit($b->keterangan,80)) : '<span class="muted">—</span>' !!}</td>
              <td class="col-actions-td">
                <div class="tbl-actions">
                  @if($b->units->count() > 1)
                    <button type="button" class="btn btn-sm btn-unit btn-detail"
                            data-toggle-parent="{{ $b->id }}" data-state="closed">
                      <svg class="ic ic-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                      <svg class="ic ic-eye-off hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"/><path d="M1 1l22 22"/><path d="M9.88 9.88A3 3 0 0 0 12 15a3 3 0 0 0 2.12-.88"/><path d="M14.12 14.12 9.88 9.88"/></svg>
                      <span class="label">Info Detail</span>
                    </button>
                  @endif
                  <div class="swap">
                    <div id="tswap-{{ $b->id }}" class="swap-pad">
                      <a href="{{ route('barang.edit',$b) }}" class="btn btn-warning btn-sm btn-main">Edit</a>
                      <form action="{{ route('barang.destroy',$b) }}" method="POST" class="del-form" onsubmit="return confirm('Hapus {{ $b->nama }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-alt">Hapus</button>
                      </form>
                    </div>
                    <button type="button" class="btn btn-toggle" data-swap-target="tswap-{{ $b->id }}">&gt;</button>
                  </div>
                </div>
              </td>
            </tr>

            {{-- BARIS DETAIL --}}
            @foreach($b->units as $u)
              @continue(strtolower($u->kode) === $primaryCode)
              @php
                $stok = (int)($u->pivot?->stok ?? 0);
                $hargaSmart = $rpSmartT($u->pivot?->harga);
                $asPcs = $stok * max(1,(int)$u->konversi);
              @endphp
              <tr class="unit-detail-row hidden" data-parent="{{ $b->id }}">
                <td class="text-end">↳</td>
                <td></td><td class="text-muted">—</td><td class="text-muted">—</td>
                <td>
                  <span class="unit-chip u-{{ strtolower($u->kode) }}">
                    <span class="unit-left"><span class="unit-code">{{ strtoupper($u->kode) }}</span><span class="unit-sep">•</span><span class="muted">Stok:</span><span>{{ $stok }}</span></span>
                    <span class="price">{{ $hargaSmart }}</span>
                  </span>
                </td>
                <td class="text-muted">≈ {{ number_format($asPcs,0,',','.') }} pcs</td>
                <td class="text-muted">—</td>
                <td class="col-actions-td text-muted"><div class="tbl-actions"><span class="muted">—</span></div></td>
              </tr>
            @endforeach
          @empty
            <tr><td colspan="8" class="text-center text-muted">Belum ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card-footer py-3">
    <div class="d-flex justify-content-center">
      {{ $barangs->withQueryString()->onEachSide(1)->links('components.pagination.pill-clean') }}
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  // refs
  const btnFilter=document.getElementById('btnFilter'),dd=document.getElementById('filterDropdown');
  const q=document.getElementById('q'),fCat=document.getElementById('fCat'),sortBy=document.getElementById('sortBy'),
        fLow=document.getElementById('fLow'),fThresh=document.getElementById('fThresh'),
        btnApply=document.getElementById('btnApply'),btnReset=document.getElementById('btnReset');

  const btnGrid=document.getElementById('btnGrid'),btnTable=document.getElementById('btnTable'),
        gridWrap=document.getElementById('gridWrap'),tableWrap=document.getElementById('tableWrap');

  const mainRows=()=>Array.from(document.querySelectorAll('tr[data-row]'));
  const cards=()=>Array.from(document.querySelectorAll('#gridWrap [data-row]'));

  // filter
  function applyFilter(){
    const v=(q?.value||'').toLowerCase().trim(),k=(fCat?.value||'').toLowerCase().trim(),
          low=fLow?.checked||false,th=parseInt(fThresh?.value||'0',10)||0;

    cards().forEach(el=>{
      const text=el.dataset.text||'',kat=el.dataset.kategori||'',total=parseInt(el.dataset.stokpcs||'0',10);
      el.style.display=(!v||text.includes(v))&&(!k||kat===k)&&(!low||total<=th)?'':'none';
    });

    mainRows().forEach(row=>{
      const text=row.dataset.text||'',kat=row.dataset.kategori||'',total=parseInt(row.dataset.stokpcs||'0',10),id=row.dataset.barangid;
      const show=(!v||text.includes(v))&&(!k||kat===k)&&(!low||total<=th);
      row.style.display=show?'':'none';
      document.querySelectorAll(`tr.unit-detail-row[data-parent="${id}"]`).forEach(dr=>dr.style.display=show?'':'none');
    });
  }

  // sort
  function applySort(){
    const mode=sortBy?.value,parentGrid=gridWrap,parentTable=document.getElementById('tblBody');
    const sortFn={
      name_asc:(a,b)=>(a.dataset.name||'').localeCompare(b.dataset.name||''),
      stok_desc:(a,b)=>parseInt(b.dataset.stokpcs||'0')-parseInt(a.dataset.stokpcs||'0'),
      newest:(a,b)=>parseInt(b.dataset.created||'0')-parseInt(a.dataset.created||'0'),
    }[mode]||((a,b)=>0);

    cards().sort(sortFn).forEach(el=>parentGrid.appendChild(el));

    const rows=mainRows();
    rows.sort(sortFn).forEach(row=>{
      parentTable.appendChild(row);
      const id=row.dataset.barangid;
      document.querySelectorAll(`tr.unit-detail-row[data-parent="${id}"]`).forEach(dr=>parentTable.appendChild(dr));
    });
  }

  // switch grid/tabel
  function switchMode(toGrid){
    if(toGrid){
      gridWrap.style.display='grid'; tableWrap.style.display='none';
      btnGrid.classList.replace('btn-outline-primary','btn-primary');
      btnTable.classList.replace('btn-primary','btn-outline-primary');
    }else{
      gridWrap.style.display='none'; tableWrap.style.display='block';
      btnTable.classList.replace('btn-outline-primary','btn-primary');
      btnGrid.classList.replace('btn-primary','btn-outline-primary');
    }
  }

  // dropdown handlers
  btnFilter?.addEventListener('click',e=>{e.stopPropagation();dd.classList.toggle('open');if(dd.classList.contains('open')) q?.focus();});
  btnApply?.addEventListener('click',()=>dd.classList.remove('open'));
  btnReset?.addEventListener('click',()=>{q.value='';fCat.value='';sortBy.value='name_asc';fLow.checked=false;fThresh.value='10';applyFilter();applySort();});
  document.addEventListener('click',e=>{if(!dd.contains(e.target)&&!btnFilter.contains(e.target)) dd.classList.remove('open');});
  document.addEventListener('keydown',e=>{if(e.key==='Escape') dd.classList.remove('open');});

  // listeners
  q?.addEventListener('input',applyFilter);
  fCat?.addEventListener('change',applyFilter);
  fLow?.addEventListener('change',applyFilter);
  fThresh?.addEventListener('input',applyFilter);
  sortBy?.addEventListener('change',applySort);

  btnGrid?.addEventListener('click',()=>switchMode(true));
  btnTable?.addEventListener('click',()=>switchMode(false));

  // toggle detail (grid & tabel)
  document.addEventListener('click',function(e){
    const btn=e.target.closest('[data-toggle-target],[data-toggle-parent]'); if(!btn) return;
    const open=(btn.dataset.state||'closed')==='closed';

    if(btn.hasAttribute('data-toggle-parent')){
      const parentId=btn.getAttribute('data-toggle-parent');
      document.querySelectorAll(`tr.unit-detail-row[data-parent="${parentId}"]`).forEach(dr=>dr.classList.toggle('hidden',!open));
    }else{
      const id=btn.getAttribute('data-toggle-target'); const list=document.getElementById(id);
      if(list) list.querySelectorAll('.chip-extra').forEach(el=>el.classList.toggle('hidden',!open));
    }

    const eye=btn.querySelector('.ic-eye'),off=btn.querySelector('.ic-eye-off'),label=btn.querySelector('.label');
    if(open){btn.dataset.state='open';label.textContent='Sembunyikan';eye?.classList.add('hide');off?.classList.remove('hide');}
    else    {btn.dataset.state='closed';label.textContent='Info Detail';off?.classList.add('hide');eye?.classList.remove('hide');}
  });

  // swap edit<->hapus
  function initSwapPads(){
    document.querySelectorAll('.swap-pad').forEach(pad=>{
      const w=Math.max(pad.querySelector('.btn-main')?.offsetWidth||96,pad.querySelector('.btn-alt')?.offsetWidth||96);
      const h=Math.max(pad.querySelector('.btn-main')?.offsetHeight||30,pad.querySelector('.btn-alt')?.offsetHeight||30);
      pad.style.width=w+'px'; pad.style.height=h+'px';
    });
  }
  document.addEventListener('click',function(e){
    const t=e.target.closest('[data-swap-target]'); if(!t) return;
    const id=t.getAttribute('data-swap-target'); document.getElementById(id)?.classList.toggle('show-delete');
  });

  // init
  switchMode(true);
  applyFilter(); applySort();
  initSwapPads(); window.addEventListener('resize',initSwapPads);
})();
</script>
@endpush
@endsection
