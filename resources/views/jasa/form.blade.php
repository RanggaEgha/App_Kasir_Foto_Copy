{{-- resources/views/jasa/form.blade.php --}}
@include('partials.neo-theme')
@include('partials.flash-neo')

@php
  $tempImage = old('temp_image', session('temp_image_path'));
  $previewUrl = $tempImage
    ? asset('storage/'.$tempImage)
    : ($jasa->image_url ?? null);
@endphp

<style>
  .is-invalid{ border-color:#dc3545 !important; box-shadow:0 0 0 .15rem rgba(220,53,69,.15); }
  @keyframes shake { 10%, 90% { transform: translateX(-1px); } 20%, 80% { transform: translateX(2px);} 30%, 50%, 70% { transform: translateX(-4px);} 40%, 60% { transform: translateX(4px);} }
  .shake{ animation: shake .28s ease-in-out 0s 1; }
</style>
<div class="mb-3">
    <label for="nama" class="form-label">Nama Jasa <span class="text-danger">*</span></label>
    <input type="text"
           class="form-control @error('nama') is-invalid @enderror"
           id="nama" name="nama"
           value="{{ old('nama', $jasa->nama ?? '') }}" required>
    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="mb-3">
    <label for="jenis" class="form-label">Jenis Jasa</label>
    <input type="text"
           class="form-control @error('jenis') is-invalid @enderror"
           id="jenis" name="jenis"
           value="{{ old('jenis', $jasa->jenis ?? '') }}"
           placeholder="Contoh: Print, Scan">
    @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="row">
    <div class="col-md-4 mb-3">
      <label class="form-label">Gambar Jasa</label>
      <div style="position:relative; width:100%; max-width:200px; aspect-ratio:1/1; border:1px solid #e6e6ef; border-radius:12px; overflow:hidden; background:#fafbff;">
        <img id="jasaPreview" src="{{ $previewUrl ?: 'https://dummyimage.com/600x600/e9eef6/7a869a&text=No+Image' }}" alt="Preview" style="width:100%; height:100%; object-fit:cover;">
      </div>
      <input type="file" name="image" id="jasaImageInput" class="form-control mt-2" accept="image/*">
      <input type="hidden" name="temp_image" id="jasaTempImage" value="{{ $tempImage }}">
      @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
      <div class="field-note mt-1">Biarkan kosong jika tidak ingin mengubah gambar.</div>
    </div>
    <div class="col-md-6 mb-3">
      <label for="satuan" class="form-label">Satuan <span class="text-danger">*</span></label>
      <input type="text"
             class="form-control @error('satuan') is-invalid @enderror"
             id="satuan" name="satuan"
             value="{{ old('satuan', $jasa->satuan ?? 'lembar') }}"
             placeholder="lembar / halaman" required>
      @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
      <label for="harga_per_satuan" class="form-label">Harga per Satuan (Rp) <span class="text-danger">*</span></label>
      <input type="number"
             class="form-control @error('harga_per_satuan') is-invalid shake @enderror"
             id="harga_per_satuan" name="harga_per_satuan"
             value="{{ old('harga_per_satuan', $jasa->harga_per_satuan ?? 0) }}"
             placeholder="500" required>
      @error('harga_per_satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="mb-3">
    <label for="keterangan" class="form-label">Keterangan</label>
    <textarea class="form-control @error('keterangan') is-invalid @enderror"
              id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $jasa->keterangan ?? '') }}</textarea>
    @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

@push('scripts')
<script>
  (function(){
    const input=document.getElementById('jasaImageInput');
    const img=document.getElementById('jasaPreview');
    const hidden=document.getElementById('jasaTempImage');
    if(!input||!img||!hidden) return;
    input.addEventListener('change', async (e)=>{
      const f=e.target.files?.[0]; if(!f) return;
      const r=new FileReader();
      r.onload=()=>{ img.src=r.result; };
      r.readAsDataURL(f);
    });
  })();
</script>
@endpush
