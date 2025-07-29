@php $s = $supplier ?? new \App\Models\Supplier; @endphp

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control rounded-pill"
           value="{{ old('name',$s->name) }}" required>
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Contact Person</label>
    <input type="text" name="contact_person" class="form-control rounded-pill"
           value="{{ old('contact_person',$s->contact_person) }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">No. HP / Telp</label>
    <input type="text" name="phone" class="form-control rounded-pill"
           value="{{ old('phone',$s->phone) }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">Email</label>
    <input type="email" name="email" class="form-control rounded-pill"
           value="{{ old('email',$s->email) }}">
  </div>

  <div class="col-12">
    <label class="form-label fw-semibold">Alamat</label>
    <textarea name="address" rows="2" class="form-control rounded-4">{{ old('address',$s->address) }}</textarea>
  </div>

  <div class="col-12">
    <label class="form-label fw-semibold">Catatan</label>
    <textarea name="notes" rows="2" class="form-control rounded-4">{{ old('notes',$s->notes) }}</textarea>
  </div>
</div>
