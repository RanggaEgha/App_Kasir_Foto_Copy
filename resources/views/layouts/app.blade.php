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

      {{-- Welcome Card modern (glass, animasi, auto-close) --}}
      @if (session('welcome'))
        <div class="position-fixed top-0 start-50 translate-middle-x p-3 welcome-wrap" style="z-index:1080">
          <div id="welcome-toast" class="welcome-card shadow-lg border-0">
            <div class="d-flex align-items-start gap-3">
              <div class="welcome-icon rounded-2 d-flex align-items-center justify-content-center">
                <i class="bi bi-emoji-smile"></i>
              </div>
              <div class="flex-grow-1">
                <div class="fw-semibold">Selamat datang 👋</div>
                <div class="small opacity-85">{{ session('welcome') }}</div>
              </div>
              <button id="welcome-close" class="btn-close btn-close-white ms-2" type="button" aria-label="Close"></button>
            </div>
            <div class="welcome-progress"></div>
          </div>
        </div>
      @endif
      {{-- /Welcome Card --}}

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

      // Inisialisasi Welcome Card
      document.addEventListener('DOMContentLoaded', () => {
        const card = document.getElementById('welcome-toast');
        const closeBtn = document.getElementById('welcome-close');
        if (!card) return;

        const show = () => requestAnimationFrame(() => card.classList.add('is-show'));
        const hide = () => {
          card.classList.remove('is-show');
          card.classList.add('is-hide');
          setTimeout(() => card.parentElement?.remove(), 350);
        };

        // tampil & auto-hide 5 detik
        show();
        let timer = setTimeout(hide, 5000);

        // klik tutup
        closeBtn?.addEventListener('click', () => {
          clearTimeout(timer);
          hide();
        });
      });
    </script>

    @stack('scripts')

    <!-- ───── Override CSS + Welcome styles ───── -->
    <style>
      /* Welcome modern styles */
      .welcome-card{
        --glass-bg: linear-gradient(135deg, rgba(99,102,241,.95), rgba(168,85,247,.92));
        --glass-border: rgba(255,255,255,.25);
        --progress: linear-gradient(90deg, #22d3ee, #a78bfa, #f472b6);
        position: relative;
        min-width: 320px;
        max-width: 560px;
        color: #fff;
        padding: .9rem 1rem .55rem;
        border-radius: 16px;
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        box-shadow: 0 10px 30px rgba(43,55,84,.25);
        transform: translateY(-14px) scale(.98);
        opacity: 0;
        transition: transform .35s ease, opacity .35s ease;
      }
      .welcome-card.is-show{ transform: translateY(0) scale(1); opacity: 1; }
      .welcome-card.is-hide{ transform: translateY(-8px) scale(.98); opacity: 0; }

      .welcome-icon{
        width: 42px; height: 42px;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.25);
      }
      .welcome-icon i{ font-size: 1.25rem; }

      .welcome-progress{
        position:absolute; left:0; right:0; bottom:0;
        height: 3px; border-bottom-left-radius:16px; border-bottom-right-radius:16px;
        background: var(--progress);
        background-size: 200% 100%;
        animation: welcome-progress 5s linear forwards, welcome-sheen 3s linear infinite;
      }
      @keyframes welcome-progress{ from{ width:100%; } to{ width:0%; } }
      @keyframes welcome-sheen{
        0%{ background-position: 0% 50%; }
        100%{ background-position: 200% 50%; }
      }

      /* CoreUI small overrides (yang sudah ada) */
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

      .pagination svg.w-5.h-5{width:1rem!important;height:1rem!important;vertical-align:middle}
      .pagination .page-link{padding:0.25rem 0.5rem;line-height:1.2}

      #app-barang-index svg.w-5.h-5{display:none!important;}

      .dataTables_wrapper .pagination,
      .pagination {
        margin: .25rem 0;
        justify-content: center;
      }
      .pagination .page-item { margin: 0 .125rem; }
      .pagination .page-link{
        padding: .25rem .6rem !important;
        font-size: .875rem;
        border: 1px solid #dee2e6;
        border-radius: .25rem;
        background: #fff;
        line-height: 1.2;
      }
      .pagination .page-item.disabled .page-link{
        background:#f8f9fa; color:#adb5bd; border-color:#dee2e6;
      }

      /* Keep button text color stable on hover/active for outline variants */
      .btn-outline-primary:hover,
      .btn-outline-primary:focus,
      .btn-outline-primary:active { color: var(--bs-primary) !important; }
      .btn-outline-secondary:hover,
      .btn-outline-secondary:focus,
      .btn-outline-secondary:active { color: var(--bs-secondary) !important; }
      .btn-outline-danger:hover,
      .btn-outline-danger:focus,
      .btn-outline-danger:active { color: var(--bs-danger) !important; }
      .btn-outline-success:hover,
      .btn-outline-success:focus,
      .btn-outline-success:active { color: var(--bs-success) !important; }
      .btn-outline-warning:hover,
      .btn-outline-warning:focus,
      .btn-outline-warning:active { color: var(--bs-warning) !important; }
      .btn-outline-info:hover,
      .btn-outline-info:focus,
      .btn-outline-info:active { color: var(--bs-info) !important; }

      /* For custom themed buttons ensure color stays */
      .btn-brand:hover,
      .btn-brand:focus,
      .btn-brand:active { color:#fff !important; }
      .btn-soft:hover,
      .btn-soft:focus,
      .btn-soft:active { color: inherit !important; }
    </style>
  </body>
</html>
