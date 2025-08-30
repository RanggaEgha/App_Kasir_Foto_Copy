<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>{{ $subject ?? ($title ?? (config('app.name').' Notification')) }}</title>
    <style>
        /* Basic email-safe styles (no external CSS, table-based layout) */
        body { margin:0; padding:0; background:#f5f7fb; color:#111827; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }
        table { border-spacing:0; }
        img { border:0; }
        .container { width:100%; background:#f5f7fb; }
        .wrapper { max-width:640px; margin:0 auto; }
        .box { background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; }
        .pad { padding:24px; }
        .brand { font-family:Segoe UI,Arial,Helvetica,sans-serif; font-weight:700; font-size:22px; color:#22c55e; letter-spacing:0.2px; }
        .greet { margin:12px 0 0; font-family:Segoe UI,Arial,Helvetica,sans-serif; font-size:14px; color:#374151; }
        .title { margin:12px 0 8px; font-family:Segoe UI,Arial,Helvetica,sans-serif; font-weight:800; font-size:22px; color:#111827; }
        .intro { margin:6px 0 0; font-family:Segoe UI,Arial,Helvetica,sans-serif; font-size:14px; line-height:1.6; color:#4b5563; }
        .card { margin:18px 0 0; padding:16px; border-radius:12px; background:#f3f4f6; border:1px solid #e5e7eb; }
        .card-title { font-family:Segoe UI,Arial,Helvetica,sans-serif; font-weight:700; font-size:14px; color:#111827; margin:0 0 8px; }
        .row { width:100%; margin:6px 0; }
        .label { width:40%; color:#6b7280; font-size:13px; font-family:Segoe UI,Arial,Helvetica,sans-serif; }
        .value { width:60%; color:#111827; font-size:14px; font-family:Segoe UI,Arial,Helvetica,sans-serif; font-weight:600; }
        .accent { color: {{ $accent ?? '#22c55e' }}; font-weight:700; }
        .danger { color:#f97316; font-weight:700; }
        .btn-wrap { margin:18px 0 0; }
        .btn { display:inline-block; padding:10px 16px; background:{{ $button_bg ?? ($accent ?? '#22c55e') }}; color:#ffffff !important; text-decoration:none; border-radius:8px; font-weight:700; font-family:Segoe UI,Arial,Helvetica,sans-serif; }
        .foot { margin:18px 0 0; font-family:Segoe UI,Arial,Helvetica,sans-serif; font-size:12px; color:#6b7280; }
        .small { font-size:12px; color:#6b7280; }
        .hr { height:1px; background:#e5e7eb; border:0; margin:18px 0; }
        @media (max-width: 520px){ .label,.value{ display:block; width:100%; } }

        /* Dark mode overrides for clients that support it */
        @media (prefers-color-scheme: dark) {
            body { background:#0f172a !important; color:#e2e8f0 !important; }
            .container { background:#0f172a !important; }
            .box { background:#111827 !important; border-color:#1f2937 !important; }
            .greet { color:#cbd5e1 !important; }
            .title { color:#f8fafc !important; }
            .intro { color:#cbd5e1 !important; }
            .card { background:#0b1220 !important; border-color:#1f2937 !important; }
            .card-title { color:#e5e7eb !important; }
            .label { color:#a1a1aa !important; }
            .value { color:#e5e7eb !important; }
            .btn { color:#0b1220 !important; }
            .foot, .small { color:#9ca3af !important; }
            .hr { background:#1f2937 !important; }
        }
    </style>
</head>
<body>
    <table role="presentation" class="container" width="100%">
        <tr>
            <td align="center" style="padding:24px;">
                <table role="presentation" class="wrapper" width="100%">
                    <tr>
                        <td class="brand">
                            @php($logo = env('STORE_LOGO_URL'))
                            @if(!empty($logo))
                                <img src="{{ $logo }}" alt="{{ config('app.name') }}" style="max-height:28px; vertical-align:middle;">
                            @else
                                {{ config('app.name') }}
                            @endif
                        </td>
                    </tr>
                    <tr><td height="10"></td></tr>
                    <tr>
                        <td>
                            <table role="presentation" width="100%" class="box">
                                <tr>
                                    <td class="pad">
                                        <div class="greet">Halo{{ isset($greeting_name) ? ' '.$greeting_name : '' }},</div>
                                        @if(!empty($title))
                                            <div class="title">{{ $title }}</div>
                                        @endif
                                        @if(!empty($intro))
                                            <div class="intro">{!! nl2br(e($intro)) !!}</div>
                                        @elseif(!empty($intro_lines))
                                            @foreach($intro_lines as $line)
                                                <div class="intro">{!! nl2br(e($line)) !!}</div>
                                            @endforeach
                                        @endif

                                        @if(!empty($details) && is_array($details))
                                            <div class="card">
                                                @if(!empty($details_title))
                                                    <div class="card-title">{{ $details_title }}</div>
                                                @endif
                                                @foreach($details as $row)
                                                    <table role="presentation" class="row" width="100%">
                                                        <tr>
                                                            <td class="label">{{ $row['label'] ?? '' }}</td>
                                                            <td class="value">{!! $row['value'] ?? '' !!}</td>
                                                        </tr>
                                                    </table>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($action_url) && !empty($action_text))
                                            <div class="btn-wrap">
                                                <a href="{{ $action_url }}" class="btn" target="_blank" rel="noopener">{{ $action_text }}</a>
                                            </div>
                                        @endif

                                        @if(!empty($outro))
                                            <hr class="hr"/>
                                            <div class="small">{!! nl2br(e($outro)) !!}</div>
                                        @elseif(!empty($outro_lines))
                                            <hr class="hr"/>
                                            @foreach($outro_lines as $line)
                                                <div class="small">{!! nl2br(e($line)) !!}</div>
                                            @endforeach
                                        @endif

                                        @if(!empty($footer_note))
                                            <div class="foot">{!! nl2br(e($footer_note)) !!}</div>
                                        @else
                                            <div class="foot">E-mail ini dibuat otomatis, mohon tidak membalas. Jika butuh bantuan, silakan hubungi admin.</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
