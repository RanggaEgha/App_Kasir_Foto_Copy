<div class="row">
  <div class="col-md-6 mb-3">
    <label for="nama" class="form-label">Nama Barang</label>
    <input type="text" name="nama" id="nama" value="{{ old('nama', $barang->nama ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-6 mb-3">
    <label for="kategori" class="form-label">Kategori</label>
    <input type="text" name="kategori" id="kategori" value="{{ old('kategori', $barang->kategori ?? '') }}" class="form-control">
  </div>

  <div class="col-md-4 mb-3">
    <label for="satuan" class="form-label">Satuan Barang</label>
    <input type="text" name="satuan" id="satuan" value="{{ old('satuan', $barang->satuan ?? '') }}" class="form-control" placeholder="Contoh: pcs, kotak" required>
  </div>
  <div class="col-md-4 mb-3">
    <label for="stok_satuan" class="form-label">Stok Satuan</label>
    <input type="number" name="stok_satuan" id="stok_satuan" value="{{ old('stok_satuan', $barang->stok_satuan ?? 0) }}" class="form-control" required>
  </div>
  <div class="col-md-4 mb-3">
    <label for="stok_paket" class="form-label">Stok Paket</label>
    <input type="number" name="stok_paket" id="stok_paket" value="{{ old('stok_paket', $barang->stok_paket ?? 0) }}" class="form-control">
  </div>

  <div class="col-md-4 mb-3">
    <label for="isi_per_paket" class="form-label">Isi per Paket</label>
    <input type="number" name="isi_per_paket" id="isi_per_paket" value="{{ old('isi_per_paket', $barang->isi_per_paket ?? '') }}" class="form-control" placeholder="Contoh: 12 isi">
  </div>
  <div class="col-md-4 mb-3">
    <label for="harga_satuan" class="form-label">Harga Satuan</label>
    <input type="number" name="harga_satuan" id="harga_satuan" value="{{ old('harga_satuan', $barang->harga_satuan ?? '') }}" class="form-control" required>
  </div>
  <div class="col-md-4 mb-3">
    <label for="harga_paket" class="form-label">Harga Paket</label>
    <input type="number" name="harga_paket" id="harga_paket" value="{{ old('harga_paket', $barang->harga_paket ?? '') }}" class="form-control">
  </div>

  <div class="col-md-12 mb-3">
    <label for="keterangan" class="form-label">Keterangan</label>
    <textarea name="keterangan" id="keterangan" rows="3" class="form-control">{{ old('keterangan', $barang->keterangan ?? '') }}</textarea>
  </div>
</div>

<div class="d-flex justify-content-between mt-4">
  <a href="{{ route('barang.index') }}" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
  <button type="submit" class="btn btn-primary">
    <i class="bi bi-save"></i> {{ isset($barang) ? 'Update' : 'Simpan' }}
  </button>
</div>
