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

<style>
  #barang-form .card { border:0; border-radius:16px; }
  #barang-form .card-header { background:linear-gradient(135deg,#f8fafc,#fff); border-bottom:1px solid #e9edf3; }
  #barang-form .help { font-size:.85rem; color:#718096; }
  #barang-form .btn-danger { background:#e6707c; border-color:#e6707c; }
  #barang-form .btn-danger:hover { background:#da5a68; border-color:#da5a68; }
  #barang-form .unit-box { border:1px dashed rgba(164,25,61,.25); border-radius:14px; background:#fff7f0; padding:1rem; }
  #barang-form .unit-pill { display:inline-block; padding:.35rem .6rem; border-radius:999px; background:var(--peach); border:1px solid rgba(164,25,61,.25); font-weight:700; color:var(--brand); }
  #barang-form .field-note { font-size:.8rem; color:#8a94a6; }
  #barang-form .form-check-input { cursor:pointer; }
  /* Buttons brand inside form */
  #barang-form .btn-primary{ background: linear-gradient(135deg, var(--brand), var(--brand-2)); border-color: var(--brand-2); box-shadow: 0 6px 18px rgba(164,25,61,.28); }
  #barang-form .btn-primary:hover{ filter:brightness(1.05); }
  #barang-form .btn-outline-primary{ color: var(--brand); border-color: rgba(164,25,61,.45); }
  #barang-form .btn-outline-primary:hover{ background: rgba(255,223,185,.65); color: var(--brand); border-color: rgba(164,25,61,.55); }
  /* Invalid + shake */
  .is-invalid{ border-color:#dc3545 !important; box-shadow:0 0 0 .15rem rgba(220,53,69,.15); }
  @keyframes shake { 10%, 90% { transform: translateX(-1px); } 20%, 80% { transform: translateX(2px);} 30%, 50%, 70% { transform: translateX(-4px);} 40%, 60% { transform: translateX(4px);} }
  .shake{ animation: shake .28s ease-in-out 0s 1; }
</style>

<div id="barang-form" x-data="stokForm()" x-init="init(
  {{ json_encode($units) }},
  {{ json_encode($stokOld) }},
  {{ json_encode($hargaOld) }},
  {{ json_encode($selected) }}
)">
  {{-- ===== Kartu: Gambar Barang ===== --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Gambar Barang</h6>
      <small class="text-muted">JPG/PNG/WEBP · maks 4 MB · disarankan rasio 1:1</small>
    </div>
    <div class="card-body">
      <div class="row g-3 align-items-center">
        <div class="col-md-3">
          @php
            $tempImage = old('temp_image', session('temp_image_path'));
            $previewUrl = $tempImage
              ? asset('storage/'.$tempImage)
              : ($barang?->image_url ?: 'https://dummyimage.com/600x600/e9eef6/7a869a&text=No+Image');
          @endphp
          <div style="position:relative; width:160px; aspect-ratio:1/1; border:1px solid #e6e6ef; border-radius:12px; overflow:hidden; background:#fafbff;">
            <img id="previewImg"
                 src="{{ $previewUrl }}"
                 alt="Preview"
                 style="width:100%; height:100%; object-fit:cover;">
          </div>
        </div>
        <div class="col-md-9">
          <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
          <input type="hidden" name="temp_image" id="tempImageInput" value="{{ $tempImage }}">
          <small class="text-muted d-block mt-2">
            Biarkan kosong jika tidak ingin mengubah gambar.
          </small>
          @error('image')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Kartu 1: Informasi Barang ===== --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header">
      <h6 class="mb-0">Informasi Utama</h6>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama Barang</label>
          <input name="nama" type="text" class="form-control" placeholder="cth: Kertas HVS A4 80gsm"
                 value="{{ old('nama', $barang->nama ?? '') }}" required>
          <div class="field-note mt-1">Gunakan nama spesifik agar mudah dicari.</div>
          @error('nama')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label">Kategori</label>
          <input name="kategori" type="text" class="form-control" placeholder="cth: Kertas / Tinta / ATK"
                 value="{{ old('kategori', $barang->kategori ?? '') }}">
          <div class="field-note mt-1">Opsional, tapi membantu saat filter.</div>
        </div>
        <div class="col-12">
          <label class="form-label">Keterangan</label>
          <textarea name="keterangan" rows="3" class="form-control"
                    placeholder="cth: warna putih, isi 500 lembar">{{ old('keterangan', $barang->keterangan ?? '') }}</textarea>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Kartu 2: Unit, Stok & Harga ===== --}}
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Unit, Stok & Harga</h6>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" @click="selectCommon()">Pilih Umum</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="clearAll()">Bersihkan</button>
      </div>
    </div>

    <div class="card-body">
      <div class="row g-3">
        @foreach ($units as $u)
          @php $uid = (string)$u->id; @endphp
          <div class="col-md-6 col-lg-4">
            <div class="unit-box h-100">
              <label class="form-check d-flex align-items-center mb-2">
                <input type="checkbox" class="form-check-input me-2"
                       name="units[]" value="{{ $uid }}"
                       x-model="unitsSel"
                       @change="toggleUnit($event,'{{ $uid }}')"
                       {{ in_array($uid, $selected) ? 'checked' : '' }}>
                <span class="unit-pill">{{ strtoupper($u->kode) }}</span>
                <span class="ms-2 text-muted">= {{ $u->konversi }} pcs</span>
              </label>

              <div x-show="unitsSel.includes('{{ $uid }}')" x-cloak x-transition>
                <div class="mb-2">
                  <label class="form-label mb-1">Stok ({{ $u->kode }})</label>
                  @php $errSt = $errors->has('stok.'.$uid); @endphp
                  <input type="number" min="0" class="form-control {{ $errSt ? 'is-invalid shake' : '' }}"
                         placeholder="cth: 120"
                         :name="`stok[{{ $uid }}]`"
                         x-model="stok['{{ $uid }}']"
                         @input="onStokInput($event,'{{ $uid }}')"
                         @change="onStokInput($event,'{{ $uid }}')">
                  @error("stok.$uid")
                    <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>

                <div>
                  <label class="form-label mb-1">Harga ({{ $u->kode }})</label>
                  <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    @php $errHg = $errors->has('harga.'.$uid); @endphp
                    <input type="text" class="form-control {{ $errHg ? 'is-invalid shake' : '' }}"
                           :id="`harga_show_{{ $uid }}`"
                           :value="formatDisplay(harga['{{ $uid }}'])"
                           @input="onHargaInput($event,'{{ $uid }}')">
                    <input type="hidden"
                           :name="`harga[{{ $uid }}]`"
                           :value="harga['{{ $uid }}'] ?? ''">
                  </div>
                  <div class="field-note mt-1">Masukkan harga per <b>{{ $u->kode }}</b>.</div>
                  @error("harga.$uid")
                    <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="alert alert-info small mt-2 mb-0">
        <b>Tips:</b> Mengubah konversi (mis. <code>lusin=12</code>) akan mempengaruhi stok total (pcs) di laporan.
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Preview gambar kecil
  (function(){
    const input = document.getElementById('imageInput');
    const img   = document.getElementById('previewImg');
    if (input && img) {
      input.addEventListener('change', e => {
        const f = e.target.files?.[0];
        if (!f) return;
        const r = new FileReader();
        r.onload = () => {
          img.src = r.result;
        };
        r.readAsDataURL(f);
      });
    }
  })();

  function stokForm(){
    return {
      konv: { @foreach($units as $u) '{{ $u->id }}': {{ (int)$u->konversi }}, @endforeach },
      code2id: { @foreach($units as $u) '{{ strtolower($u->kode) }}': '{{ $u->id }}', @endforeach },

      unitsSel: [], stok: {}, harga: {},

      lastEdited: null,

      init(allUnits, stokOld, hargaOld, selected){
        this.unitsSel = selected || [];
        this.stok     = stokOld  || {};
        this.harga    = hargaOld || {};
      },

      roundSmart(x){
        if (x === '' || x === null || isNaN(x)) return '';
        return parseFloat(Number(x).toFixed(6));
      },

      onStokInput(evt, id){
        id = String(id);
        const val = parseFloat(evt.target.value || '');
        if (!isFinite(val)) return;
        this.lastEdited = id;

        // konversi ke pcs (base)
        const baseQty = val * (Number(this.konv[id]) || 1);

        // sebar ke unit lain yang aktif
        (this.unitsSel || []).forEach(u => {
          u = String(u);
          if (u === id) return;
          const f = Number(this.konv[u]) || 1;
          const qty = baseQty / f;
          this.stok[u] = this.roundSmart(qty);
        });
      },

      toggleUnit(evt, id){
        id = String(id);
        const isOn = evt && evt.target ? !!evt.target.checked : this.unitsSel.includes(id);

        if (!isOn) {
          // UNCHECK → bersihkan nilai
          delete this.stok[id];
          delete this.harga[id];
          const el = document.getElementById(`harga_show_${id}`);
          if (el) el.value = '';
          return;
        }

        // CHECK → coba prefilling dari unit terakhir yg diedit / yg punya stok
        let refId = this.lastEdited;
        if (!refId || !isFinite(parseFloat(this.stok[String(refId)] ?? ''))) {
          refId = (this.unitsSel || []).find(u => isFinite(parseFloat(this.stok[String(u)] ?? '')));
        }
        if (refId) {
          refId = String(refId);
          const v = parseFloat(this.stok[refId] ?? '');
          if (isFinite(v)) {
            const baseQty = v * (Number(this.konv[refId]) || 1);
            const f = Number(this.konv[id]) || 1;
            this.stok[id] = this.roundSmart(baseQty / f);
          }
        }
      },

      selectCommon(){
        const common = ['pcs','paket','lembar','lusin','box']
          .map(c => this.code2id[c])
          .filter(Boolean);
        this.unitsSel = Array.from(new Set([...(this.unitsSel||[]), ...common]));
      },

      clearAll(){
        (this.unitsSel||[]).forEach(id => {
          delete this.stok[String(id)];
          delete this.harga[String(id)];
          const el = document.getElementById(`harga_show_${id}`);
          if (el) el.value = '';
        });
        this.unitsSel = [];
        this.lastEdited = null;
      },

      onHargaInput(evt, id){
        id = String(id);
        const digits = (evt.target.value||'').replace(/\D/g,'');
        this.harga[id] = digits ? parseInt(digits,10) : '';
        evt.target.value = this.formatDisplay(this.harga[id]);
      },

      formatDisplay(v){
        if(!v) return '';
        return 'Rp. ' + v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      }
    }
  }
</script>
@endpush
