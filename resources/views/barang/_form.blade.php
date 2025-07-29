@props(['barang' => null, 'units'])

@php
    $selected = collect(old('units', $barang?->units->pluck('id')->toArray() ?? []))
               ->map(fn($v)=>strval($v))->toArray();
    $stokOld  = collect(old('stok',  $barang?->units
                       ->mapWithKeys(fn($u)=>[$u->id=>$u->pivot->stok])->toArray() ?? []))
               ->mapWithKeys(fn($v,$k)=>[strval($k)=>$v])->toArray();
    $hargaOld = collect(old('harga', $barang?->units
                       ->mapWithKeys(fn($u)=>[$u->id=>$u->pivot->harga])->toArray() ?? []))
               ->mapWithKeys(fn($v,$k)=>[strval($k)=>$v])->toArray();
@endphp

<div x-data="stokForm()"
     x-init="init({{ json_encode($units) }}, {{ json_encode($stokOld) }}, {{ json_encode($hargaOld) }}, {{ json_encode($selected) }})">

  {{-- ── Nama & Kategori ─────────────────────────────────────── --}}
  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control"
             placeholder="cth: Pulpen A4"
             value="{{ old('nama', $barang->nama ?? '') }}" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Kategori</label>
      <input type="text" name="kategori" class="form-control"
             placeholder="mis. ATK"
             value="{{ old('kategori', $barang->kategori ?? '') }}">
    </div>
  </div>

  {{-- ── Pilih Unit ──────────────────────────────────────────── --}}
  <label class="form-label">Unit yang dipakai</label>
  <div class="row">
    @foreach ($units as $u)
      @php $uid = (string)$u->id; @endphp
      <div class="col-md-4 mb-2">

        {{-- Checkbox unit --}}
        <label class="form-check">
          <input type="checkbox" class="form-check-input"
                 name="units[]" value="{{ $uid }}"
                 x-model="unitsSel"
                 @change="toggleUnit('{{ $uid }}')"
                 {{ in_array($uid, $selected) ? 'checked' : '' }}>
          {{ strtoupper($u->kode) }}
        </label>

        {{-- Panel stok + harga – hanya muncul bila dicentang --}}
        <div x-show="unitsSel.includes('{{ $uid }}')" x-cloak x-transition
             class="border rounded p-2 mt-2">

          {{-- Stok --}}
          <label class="form-label mb-1">Stok ({{ $u->kode }})</label>
          <input type="number" min="0" class="form-control mb-2"
                 placeholder="cth: 240"
                 :name="`stok[{{ $uid }}]`"
                 x-model.number="stok['{{ $uid }}']"
                 @input="recalc('{{ $uid }}')">

          {{-- Harga --}}
          <label class="form-label mb-1">Harga ({{ $u->kode }})</label>
          <input  type="text" class="form-control mb-2"
                  placeholder="cth: Rp 12.000"
                  id="harga_show_{{ $uid }}"
                  @input="formatHarga($event, '{{ $uid }}')"
                  :value="formatDisplay(harga['{{ $uid }}'] ?? '')">

          <input type="hidden"
                 :name="`harga[{{ $uid }}]`"
                 :value="harga['{{ $uid }}'] ?? ''">
        </div>
      </div>
    @endforeach
  </div>

  {{-- ── Keterangan ─────────────────────────────────────────── --}}
  <div class="mb-3">
    <label class="form-label">Keterangan</label>
    <textarea name="keterangan" rows="3" class="form-control"
              placeholder="warna hitam, tinta tahan air">{{ old('keterangan', $barang->keterangan ?? '') }}</textarea>
  </div>
</div>

@push('scripts')
<script>
function stokForm(){
  return {
    /* konversi unit→pcs (key = string id) */
    konv:{ @foreach($units as $u) '{{ $u->id }}':{{ $u->konversi }}, @endforeach },

    unitsSel: [], stok:{}, harga:{},

    init(allUnits, stokOld, hargaOld, selected){
      this.unitsSel = selected;
      this.stok     = stokOld;
      this.harga    = hargaOld;
    },

    toggleUnit(id){
      id = String(id);
      if(!this.unitsSel.includes(id)){
        delete this.stok[id];
        delete this.harga[id];
        const el = document.getElementById(`harga_show_${id}`);
        if(el) el.value = '';
      }
    },

    recalc(changed){
      changed = String(changed);
      if(!this.stok[changed]) return;
      const pcsBase = this.stok[changed]*this.konv[changed];
      for(const id of this.unitsSel){
        if(id !== changed){
          this.stok[id] = Math.floor(pcsBase / this.konv[id]);
        }
      }
    },

    formatHarga(evt,id){
      id = String(id);
      const digits = evt.target.value.replace(/\D/g,'');
      this.harga[id] = digits ? parseInt(digits,10) : '';
      evt.target.value = this.formatDisplay(this.harga[id]);
    },

    formatDisplay(v){
      if(!v) return '';
      return 'Rp ' + v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
  }
}
</script>
@endpush
