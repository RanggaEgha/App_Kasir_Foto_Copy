{{-- resources/views/jasa/form.blade.php --}}
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
             class="form-control @error('harga_per_satuan') is-invalid @enderror"
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
