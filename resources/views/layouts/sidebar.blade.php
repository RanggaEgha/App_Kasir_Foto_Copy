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
    <li class="nav-item">
      <a class="nav-link" href="#">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-speedometer') }}"></use>
        </svg> Dashboard
      </a>
    </li>

    <li class="nav-title">Kasir</li>

    <li class="nav-item">
      <a class="nav-link" href="{{ route('barang.index') }}">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-cart') }}"></use>
        </svg> Data Barang
      </a>
    </li>

<li class="nav-item">
  <a class="nav-link" href="{{ route('jasa.index') }}">
    <svg class="nav-icon">
      <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-clipboard') }}"></use>
    </svg> Data Jasa
  </a>
</li>


   <li class="nav-item">
  <a class="nav-link" href="{{ route('transaksi.index') }}">
    <svg class="nav-icon">
      <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-cash') }}"></use>
    </svg> Transaksi
  </a>
</li>

    <li class="nav-title">Lainnya</li>

    <li class="nav-item">
      <a class="nav-link" href="#">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-settings') }}"></use>
        </svg> Pengaturan
      </a>
    </li>

    <li class="nav-item mt-auto">
      <a class="nav-link" href="#">
        <svg class="nav-icon">
          <use xlink:href="{{ asset('coreui/vendors/@coreui/icons/svg/free.svg#cil-account-logout') }}"></use>
        </svg> Keluar
      </a>
    </li>
  </ul>

  <div class="sidebar-footer border-top d-none d-md-flex">
    <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
  </div>
</div>
