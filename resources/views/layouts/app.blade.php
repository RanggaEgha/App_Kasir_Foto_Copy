<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="/">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="CoreUI - Open Source Bootstrap Admin Template">
    <meta name="author" content="Łukasz Holeczek">
    <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,Dashboard">
    <title>@yield('title', 'Kasir Fotokopi')</title>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('coreui/assets/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"  href="{{ asset('coreui/assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"  href="{{ asset('coreui/assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('coreui/assets/favicon/manifest.json') }}">

    <!-- CoreUI & Vendor Styles -->
    <style>[x-cloak]{display:none!important}</style>
    <link rel="stylesheet" href="{{ asset('coreui/vendors/simplebar/css/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('coreui/css/vendors/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('coreui/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('coreui/css/examples.css') }}">
    <link rel="stylesheet" href="{{ asset('coreui/vendors/@coreui/chartjs/css/coreui-chartjs.css') }}">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @stack('styles')

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <script src="{{ asset('coreui/js/config.js') }}"></script>
    <script src="{{ asset('coreui/js/color-modes.js') }}"></script>
  </head>

  <body>
    @include('layouts.sidebar')

    <div class="wrapper d-flex flex-column min-vh-100">
      @include('layouts.header')

      <div class="body flex-grow-1">
        <div class="container-lg px-4">
          @yield('content')
        </div>
      </div>

      <footer class="footer px-4">
        <div><a href="https://coreui.io">CoreUI</a> &copy; 2025 creativeLabs.</div>
        <div class="ms-auto">Powered by&nbsp;<a href="https://coreui.io/docs/">CoreUI UI Components</a></div>
      </footer>
    </div>

    <!-- CoreUI Scripts -->
    <script src="{{ asset('coreui/vendors/@coreui/coreui/js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('coreui/vendors/simplebar/js/simplebar.min.js') }}"></script>
    <script src="{{ asset('coreui/vendors/chart.js/js/chart.umd.js') }}"></script>
    <script src="{{ asset('coreui/vendors/@coreui/chartjs/js/coreui-chartjs.js') }}"></script>
    <script src="{{ asset('coreui/vendors/@coreui/utils/js/index.js') }}"></script>
    <script src="{{ asset('coreui/js/main.js') }}"></script>

    <!-- DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
      const header = document.querySelector('header.header');
      document.addEventListener('scroll', () => {
        if (header) header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
      });
    </script>

    @stack('scripts')

    <!-- ───── Override CSS ───── -->
    <style>
      /* 1. Hilangkan chevron CoreUI */
      .table-responsive::before,
      .table-responsive::after,
      .table::before,
      .table::after,
      .dataTables_scroll::before,
      .dataTables_scroll::after { content:none!important;display:none!important; }
      .table-responsive i[class*="cil-chevron"],
      .table-responsive svg[class*="cil-chevron"],
      .dataTables_scroll i[class*="cil-chevron"],
      .dataTables_scroll svg[class*="cil-chevron"]{ display:none!important; }

      /* 3. Perkecil ikon panah pagination */
      .pagination svg.w-5.h-5{width:1rem!important;height:1rem!important;vertical-align:middle}
      .pagination .page-link{padding:0.25rem 0.5rem;line-height:1.2}

      /* 4. Sembunyikan SVG w-5 h-5 “liar” di halaman Barang */
      #app-barang-index svg.w-5.h-5{display:none!important;}
      /* 6. penyempuraan tata letak */
      /* 6. Rapikan pagination angka DataTables & Laravel */
.dataTables_wrapper .pagination,          /* DataTables */
.pagination {                             /* Laravel default */
  margin: .25rem 0;
  justify-content: center;                /* pusatkan; bisa diganti flex-start/end */
}

.pagination .page-item {
  margin: 0 .125rem;                      /* jarak antar kotak */
}

.pagination .page-link{
  padding: .25rem .6rem !important;       /* ramping */
  font-size: .875rem;                     /* 14px */
  border: 1px solid #dee2e6;              /* garis tipis */
  border-radius: .25rem;
  background: #fff;
  line-height: 1.2;
}

/* hilangkan garis saat disabled (Prev di halaman pertama, dst) */
.pagination .page-item.disabled .page-link{
  background:#f8f9fa;
  color:#adb5bd;
  border-color:#dee2e6;
}

    </style>
  </body>
</html>
