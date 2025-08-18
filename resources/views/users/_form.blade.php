@php($editing = isset($user) && $user->exists)

<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Nama</label>
    <input type="text" name="name" class="form-control" value="{{ old('name',$user->name ?? '') }}" required>
    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email',$user->email ?? '') }}" required>
    @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Role</label>
    <select name="role" class="form-select" required>
      @php($roleVal = old('role', $user->role ?? 'kasir'))
      <option value="kasir" {{ $roleVal==='kasir' ? 'selected' : '' }}>Kasir</option>
      <option value="admin" {{ $roleVal==='admin' ? 'selected' : '' }}>Admin</option>
    </select>
    @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Status</label>
    <select name="is_active" class="form-select" required>
      @php($activeVal = (string) old('is_active', isset($user) ? (int)$user->is_active : 1))
      <option value="1" {{ $activeVal==='1' ? 'selected' : '' }}>Aktif</option>
      <option value="0" {{ $activeVal==='0' ? 'selected' : '' }}>Nonaktif</option>
    </select>
    @error('is_active')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-4">
    <label class="form-label">Password {{ $editing ? '(opsional, isi jika ingin ganti)' : '' }}</label>
    <input type="password" name="password" class="form-control" {{ $editing ? '' : 'required' }}>
    @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
</div>
