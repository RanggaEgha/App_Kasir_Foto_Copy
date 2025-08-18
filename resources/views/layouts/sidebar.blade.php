<div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
  <div class="sidebar-header border-bottom">
    <div class="sidebar-brand">
      <svg class="sidebar-brand-full" width="88" height="32" alt="CoreUI Logo">
        <use xlink:href="{{ asset('coreui/assets/brand/coreui.svg#full') }}"></use>
      </svg>
      <svg class="sidebar-brand-narrow" width="32" height="32" alt="CoreUI Logo">
        <use xlink:href="{{ asset('coreui/assets/brand/coreui.svg#signet') }}"></use>
      </svg>
    </div>
    <button class="btn-close d-lg-none" type="button" aria-label="Close"
      onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()">
    </button>
  </div>

  <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">

    {{-- DASHBOARD (Admin only) --}}
    @can('admin')
    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
        </svg> Dashboard
      </a>
    </li>
    @endcan

    {{-- PRODUK & JASA (Kasir & Admin) --}}
    @can('kasir')
    <li class="nav-title mt-3">Produk &amp; Jasa</li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('barang.*') ? 'active' : '' }}"
         href="{{ route('barang.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
        </svg> Data Barang
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('jasa.*') ? 'active' : '' }}"
         href="{{ route('jasa.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-clipboard') }}"></use>
        </svg> Data Jasa
      </a>
    </li>
    @endcan

    {{-- TRANSAKSI (Kasir & Admin) --}}
    @can('kasir')
    <li class="nav-title mt-3">Transaksi</li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('pembayaran.*') ? 'active' : '' }}"
         href="{{ route('pembayaran.create') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-calculator') }}"></use>
        </svg> Pembayaran
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}"
         href="{{ route('history.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-history') }}"></use>
        </svg> History Transaksi
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('shift.*') ? 'active' : '' }}"
         href="{{ route('shift.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-cash') }}"></use>
        </svg> Shift Kasir
      </a>
    </li>
    @endcan

    {{-- PEMBELIAN (Admin only) --}}
    @can('admin')
    <li class="nav-title mt-3">Pembelian</li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"
         href="{{ route('suppliers.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-truck') }}"></use>
        </svg> Supplier
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}"
         href="{{ route('purchases.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-basket') }}"></use>
        </svg> Purchase Order
      </a>
    </li>
    @endcan

    {{-- LAINNYA (Admin only) --}}
    @can('admin')
    <li class="nav-title mt-3">Lainnya</li>

    @if (Route::has('users.index'))
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
           href="{{ route('users.index') }}">
          <svg class="nav-icon">
            <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-user') }}"></use>
          </svg> Pengguna
        </a>
      </li>
    @endif

    <li class="nav-item">
      <a class="nav-link" href="#">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
        </svg> Pengaturan
      </a>
    </li>
    @endcan

    {{-- LOGOUT (semua role) --}}
    <li class="nav-item mt-auto">
      <a href="#"
         class="nav-link text-danger"
         onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
        </svg> Logout
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
      </form>
    </li>

  </ul>

  <div class="sidebar-footer border-top d-none d-md-flex">
    <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
  </div>
</div>
