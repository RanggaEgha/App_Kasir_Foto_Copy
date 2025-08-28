@extends('layouts.app')

@section('title', 'Daftar Jasa')

@section('content')
@include('partials.neo-theme')
@include('partials.flash-neo')

<style>
  #jasa-index{ margin-top:14px; margin-bottom:18px; }
  .muted{ color:#8a94a6 }
  .badge-peach{ background: var(--peach); color: var(--brand); border:1px solid rgba(164,25,61,.25); font-weight:700; border-radius:999px; }

  /* Toggle + filter */
  .mode-switch .btn{border-radius:12px 0 0 12px}
  .mode-switch .btn:last-child{border-radius:0 12px 12px 0}
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

  /* Grid */
  .grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}
  .item-card{display:flex;flex-direction:column;gap:10px;border:1px solid #e6ebf2;border-radius:16px;background:#fff;padding:14px;transition:transform .15s, box-shadow .15s, border-color .15s}
  .item-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(164,25,61,.08);border-color:rgba(164,25,61,.18)}
  .card-head{display:flex;justify-content:space-between;align-items:center;gap:8px}
  .name{font-weight:700;font-size:1.04rem;line-height:1.2}

  .info-chip{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:.35rem .6rem;border-radius:12px;border:1px solid #e6ebf2;background:#f9fbfd;font-size:.86rem}
  .info-left{display:flex;align-items:center;gap:6px}
  .info-code{font-weight:700;text-transform:uppercase;letter-spacing:.2px;font-size:.78rem}
  .info-sep{color:#b9c2d0}
  .price{white-space:nowrap}

  .actions{display:flex;gap:8px;justify-content:flex-end;align-items:center;margin-top:auto;flex-wrap:nowrap}
  .actions .btn{border-radius:10px;white-space:nowrap}
  #jasa-index .btn-unit{background:rgba(255,223,185,.45);border:1px solid rgba(164,25,61,.25);color:var(--brand);padding-inline:10px 12px;display:inline-flex;align-items:center;gap:6px;max-width:150px}
  #jasa-index .btn-unit:hover{background:rgba(255,223,185,.65);border-color:rgba(164,25,61,.35);color:var(--brand)}

  /* Table */
  #tableWrap{display:none}
  .table th{white-space:nowrap}

  /* Brand buttons */
  .card .btn-primary{ background: linear-gradient(135deg, var(--brand), var(--brand-2)); border-color: var(--brand-2); box-shadow: 0 6px 18px rgba(164,25,61,.28); }
  .card .btn-primary:hover{ filter:brightness(1.05); }
  .card .btn-outline-primary{ color: var(--brand); border-color: rgba(164,25,61,.45); }
  .card .btn-outline-primary:hover{ background: rgba(255,223,185,.65); color: var(--brand); border-color: rgba(164,25,61,.55); }
</style>

<div id="jasa-index" class="card shadow-sm mt-3 mb-4">
  <div class="card-header d-flex flex-wrap justify-content-between gap-2 align-items-center">
    <div class="d-flex align-items-center gap-2">
      <div class="mode-switch btn-group" role="group" aria-label="View switch">
        <button id="btnGrid" type="button" class="btn btn-primary btn-sm">Grid</button>
        <button id="btnTable" type="button" class="btn btn-outline-primary btn-sm">Tabel</button>
      </div>

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
              <input id="q" type="text" class="form-control" placeholder="Nama / jenis / keterangan…">
            </div>
          </div>
          <div class="filter-row">
            <label for="fJenis">Jenis</label>
            <select id="fJenis" class="form-select">
              <option value="">Semua</option>
              @php $jenisOpts = collect($jasas)->pluck('jenis')->filter()->unique()->values(); @endphp
              @foreach($jenisOpts as $j)
                <option value="{{ strtolower($j) }}">{{ $j }}</option>
              @endforeach
            </select>
          </div>
          <div class="filter-row">
            <label for="sortBy">Urutkan</label>
            <select id="sortBy" class="form-select">
              <option value="name_asc">Nama (A–Z)</option>
              <option value="harga_desc">Harga Tertinggi</option>
              <option value="newest">Terbaru</option>
            </select>
          </div>
          <div class="filter-actions">
            <button id="btnReset" type="button" class="btn btn-light">Reset</button>
            <button id="btnApply" type="button" class="btn btn-primary">Terapkan</button>
          </div>
        </div>
      </div>
    </div>

    <a href="{{ route('jasa.create') }}" class="btn btn-primary" style="border-radius:12px">+ Tambah Jasa</a>
  </div>

  <div class="card-body">
    {{-- GRID MODE --}}
    <div id="gridWrap" class="grid">
      @forelse($jasas as $j)
        @php
          $img = $j->image_url ?: 'https://dummyimage.com/800x600/e9eef6/7a869a&text=No+Image';
          $createdTs = optional($j->created_at)->getTimestamp() ?? 0;
          $harga = (int)($j->harga_per_satuan ?? 0);
        @endphp
        <div class="item-card"
             data-row
             data-name="{{ strtolower($j->nama) }}"
             data-jenis="{{ strtolower($j->jenis ?? '') }}"
             data-price="{{ $harga }}"
             data-created="{{ $createdTs }}"
             data-text="{{ strtolower(($j->nama ?? '').' '.($j->jenis ?? '').' '.($j->keterangan ?? '')) }}">

          <div style="width:100%;aspect-ratio:4/3;border-radius:12px;overflow:hidden;background:#f2f5fa">
            <img src="{{ $img }}" alt="{{ $j->nama }}" style="width:100%;height:100%;object-fit:cover">
          </div>

          <div class="card-head">
            <div class="name">{{ $j->nama }}</div>
            @if($j->jenis)
              <span class="badge rounded-pill badge-peach">{{ $j->jenis }}</span>
            @endif
          </div>

          <div class="info-chip" title="{{ strtoupper($j->satuan) }} • Rp{{ number_format($harga,0,',','.') }}">
            <div class="info-left"><span class="info-code">{{ strtoupper($j->satuan) }}</span><span class="info-sep">•</span><span class="muted">Harga:</span><span>Rp{{ number_format($harga,0,',','.') }}</span></div>
            <div class="price"></div>
          </div>

          @if($j->keterangan)
            <div class="muted" title="{{ $j->keterangan }}">{{ \Illuminate\Support\Str::limit($j->keterangan, 80) }}</div>
          @endif

          <div class="actions">
            <a href="{{ route('jasa.edit',$j->id) }}" class="btn btn-warning btn-sm">Edit</a>
            <form action="{{ route('jasa.destroy',$j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus {{ $j->nama }}?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
          </div>
        </div>
      @empty
        <div class="text-center text-muted">Belum ada data jasa</div>
      @endforelse
    </div>

    {{-- TABLE MODE --}}
    <div id="tableWrap" class="table-responsive mt-2">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Gambar</th>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Satuan</th>
            <th>Harga</th>
            <th class="text-nowrap">Keterangan</th>
            <th style="width:220px">Aksi</th>
          </tr>
        </thead>
        <tbody id="tblBody">
          @forelse ($jasas as $i => $j)
            @php
              $img = $j->image_url ?: 'https://dummyimage.com/160x120/e9eef6/7a869a&text=No+Image';
              $createdTs = optional($j->created_at)->getTimestamp() ?? 0;
            @endphp
            <tr data-row data-jasaid="{{ $j->id }}"
                data-name="{{ strtolower($j->nama) }}"
                data-jenis="{{ strtolower($j->jenis ?? '') }}"
                data-price="{{ (int)($j->harga_per_satuan ?? 0) }}"
                data-created="{{ $createdTs }}"
                data-text="{{ strtolower(($j->nama ?? '').' '.($j->jenis ?? '').' '.($j->keterangan ?? '')) }}">
              <td>{{ $i + 1 }}</td>
              <td>
                @if($j->image_url)
                  <img src="{{ $img }}" alt="{{ $j->nama }}" style="width:64px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #e6ebf2;">
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="fw-semibold">{{ $j->nama }}</td>
              <td>@if($j->jenis)<span class="badge badge-peach">{{ $j->jenis }}</span>@else <span class="text-muted">-</span> @endif</td>
              <td><span class="badge badge-peach">{{ $j->satuan }}</span></td>
              <td>Rp{{ number_format((int)($j->harga_per_satuan ?? 0),0,',','.') }}</td>
              <td class="text-truncate" style="max-width:280px;">{!! $j->keterangan ? e(\Illuminate\Support\Str::limit($j->keterangan,80)) : '<span class="muted">-</span>' !!}</td>
              <td>
                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('jasa.edit',$j->id) }}" class="btn btn-warning btn-sm">Edit</a>
                  <form action="{{ route('jasa.destroy',$j->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus {{ $j->nama }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">Belum ada data</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const btnFilter=document.getElementById('btnFilter'),dd=document.getElementById('filterDropdown');
  const q=document.getElementById('q'),fJenis=document.getElementById('fJenis'),sortBy=document.getElementById('sortBy'),
        btnApply=document.getElementById('btnApply'),btnReset=document.getElementById('btnReset');
  const btnGrid=document.getElementById('btnGrid'),btnTable=document.getElementById('btnTable'),
        gridWrap=document.getElementById('gridWrap'),tableWrap=document.getElementById('tableWrap');

  const mainRows=()=>Array.from(document.querySelectorAll('tr[data-row]'));
  const cards=()=>Array.from(document.querySelectorAll('#gridWrap [data-row]'));

  function applyFilter(){
    const v=(q?.value||'').toLowerCase().trim(),jenis=(fJenis?.value||'').toLowerCase().trim();
    cards().forEach(el=>{
      const text=el.dataset.text||'',j=el.dataset.jenis||'';
      el.style.display=(!v||text.includes(v))&&(!jenis||j===jenis)?'':'none';
    });
    mainRows().forEach(row=>{
      const text=row.dataset.text||'',j=row.dataset.jenis||'';
      const show=(!v||text.includes(v))&&(!jenis||j===jenis);
      row.style.display=show?'':'none';
    });
  }

  function applySort(){
    const mode=sortBy?.value,parentGrid=gridWrap,parentTable=document.getElementById('tblBody');
    const sortFn={
      name_asc:(a,b)=>(a.dataset.name||'').localeCompare(b.dataset.name||''),
      harga_desc:(a,b)=>parseInt(b.dataset.price||'0')-parseInt(a.dataset.price||'0'),
      newest:(a,b)=>parseInt(b.dataset.created||'0')-parseInt(a.dataset.created||'0'),
    }[mode]||((a,b)=>0);
    cards().sort(sortFn).forEach(el=>parentGrid.appendChild(el));
    const rows=mainRows();
    rows.sort(sortFn).forEach(row=>parentTable.appendChild(row));
  }

  function switchMode(toGrid){
    if(toGrid){ gridWrap.style.display='grid'; tableWrap.style.display='none'; btnGrid.classList.replace('btn-outline-primary','btn-primary'); btnTable.classList.replace('btn-primary','btn-outline-primary'); }
    else { gridWrap.style.display='none'; tableWrap.style.display='block'; btnTable.classList.replace('btn-outline-primary','btn-primary'); btnGrid.classList.replace('btn-primary','btn-outline-primary'); }
  }

  btnFilter?.addEventListener('click',e=>{e.stopPropagation();dd.classList.toggle('open');if(dd.classList.contains('open')) q?.focus();});
  btnApply?.addEventListener('click',()=>dd.classList.remove('open'));
  btnReset?.addEventListener('click',()=>{q.value='';fJenis.value='';sortBy.value='name_asc';applyFilter();applySort();});
  document.addEventListener('click',e=>{if(!dd.contains(e.target)&&!btnFilter.contains(e.target)) dd.classList.remove('open');});
  document.addEventListener('keydown',e=>{if(e.key==='Escape') dd.classList.remove('open');});

  q?.addEventListener('input',applyFilter);
  fJenis?.addEventListener('change',applyFilter);
  sortBy?.addEventListener('change',applySort);
  btnGrid?.addEventListener('click',()=>switchMode(true));
  btnTable?.addEventListener('click',()=>switchMode(false));

  switchMode(true); applyFilter(); applySort();
})();
</script>
@endpush
@endsection
