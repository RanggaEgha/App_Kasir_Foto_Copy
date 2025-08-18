@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Manajemen User</h5>
      <div class="d-flex gap-2 align-items-center">
        <form method="GET" class="d-flex" action="{{ route('users.index') }}">
          <input type="text" name="q" value="{{ $q }}" class="form-control me-2" placeholder="Cari nama/email">
          <button class="btn btn-outline-secondary" type="submit">Cari</button>
        </form>
        <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah User</a>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th style="width:180px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $i => $u)
              <tr>
                <td>{{ $users->firstItem() + $i }}</td>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>
                  <span class="badge {{ $u->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                    {{ ucfirst($u->role) }}
                  </span>
                </td>
                <td>
                  <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-danger' }}">
                    {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-warning">Edit</a>
                  <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" {{ auth()->id()===$u->id ? 'disabled' : '' }}>
                      Hapus
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-4">Belum ada data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer">
      {{ $users->onEachSide(1)->links() }}
    </div>
  </div>
</div>
@endsection
