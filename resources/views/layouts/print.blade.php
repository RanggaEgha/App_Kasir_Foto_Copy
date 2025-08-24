<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
  <title>@yield('title','Receipt')</title>
  <style>
    /* Lebar 80mm kira-kira 300px – aman untuk thermal */
    :root { --w: 300px; --ink:#111; --muted:#666; }
    html,body{ padding:0; margin:0; }
    body{ color:var(--ink); font: 12px/1.4 -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, "Helvetica Neue", sans-serif; }
    .sheet{ width:var(--w); margin:0 auto; padding:12px 10px; }
    h1,h2,h3,h4,p{ margin:0; }
    .center{ text-align:center; }
    .muted{ color:var(--muted); }
    .row{ display:flex; align-items:flex-start; justify-content:space-between; gap:8px; }
    .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace; }
    .hr{ border-top:1px dashed #999; margin:8px 0; }
    .hr-solid{ border-top:1px solid #000; margin:8px 0; }
    .items .line{ margin:8px 0 6px; }
    .items .name{ font-weight:600; }
    .items .meta{ font-size:11px; color:var(--muted); display:flex; justify-content:space-between; }
    .totals .row{ margin:4px 0; }
    .totals .label{ color:#333; }
    .totals .amt{ font-weight:700; }
    .grand{ font-size:14px; font-weight:800; }
    .footer{ margin-top:10px; line-height:1.35; }
    @media print {
      @page{ margin:0 }
      body{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .noprint{ display:none !important; }
    }
  </style>
  @yield('head')
</head>
<body>
  <div class="sheet">
    @yield('content')
  </div>

  @yield('scripts')
</body>
</html>
