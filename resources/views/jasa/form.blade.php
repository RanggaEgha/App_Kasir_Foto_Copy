<div class="row">
  <div class="col-md-6 mb-3">
    <label for="nama" class="form-label">Nama Jasa</label>
    <input type="text" name="nama" id="nama" value="{{ old('nama', $jasa->nama ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-6 mb-3">
    <label for="jenis" class="form-label">Jenis</label>
    <input type="text" name="jenis" id="jenis" value="{{ old('jenis', $jasa->jenis ?? '') }}" class="form-control" placeholder="Contoh: Fotocopy, Print">
  </div>
  <div class="col-md-4 mb-3">
    <label for="satuan" class="form-label">Satuan</label>
    <input type="text" name="satuan" id="satuan" value="{{ old('satuan', $jasa->satuan ?? '') }}" class="form-control" placeholder="Contoh: lembar, halaman">
  </div>
  <div class="col-md-4 mb-3">
    <label for="harga_per_satuan" class="form-label">Harga per Satuan</label>
    <input type="number" name="harga_per_satuan" id="harga_per_satuan" value="{{ old('harga_per_satuan', $jasa->harga_per_satuan ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-12 mb-3">
    <label for="keterangan" class="form-label">Keterangan</label>
    <textarea name="keterangan" id="keterangan" rows="3" class="form-control">{{ old('keterangan', $jasa->keterangan ?? '') }}</textarea>
  </div>
</div>
