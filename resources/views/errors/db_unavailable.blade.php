<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Layanan Tidak Tersedia – Database Offline</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', Arial, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #e2e8f0; }
        .wrap { max-width: 720px; margin: 6rem auto; padding: 2rem; background: #111827; border-radius: 12px; border: 1px solid #1f2937; }
        h1 { margin: 0 0 0.5rem; font-size: 1.5rem; }
        code { background: #0b1220; border: 1px solid #1f2937; padding: 0.15rem 0.35rem; border-radius: 6px; }
        .muted { color: #9ca3af; font-size: 0.95rem; }
        ul { margin-top: 0.5rem; }
        li { margin: 0.25rem 0; }
        .box { background: #0b1220; border: 1px solid #1f2937; padding: 0.75rem; border-radius: 8px; margin-top: 0.75rem; }
    </style>
    <meta http-equiv="refresh" content="10"> <!-- auto-refresh to re-check health -->
    <meta name="robots" content="noindex">
</head>
<body>
    <div class="wrap">
        <h1>Database tidak dapat dihubungi (503)</h1>
        <p class="muted">Aplikasi tidak bisa terhubung ke database saat ini.</p>
        <div class="box">
            <div>Target: <code>{{ $host ?? '127.0.0.1' }}:{{ $port ?? '3306' }}</code></div>
            @if(!empty($database))
                <div>Database: <code>{{ $database }}</code></div>
            @endif
        </div>

        <h3>Yang perlu dicek</h3>
        <ul>
            <li>Pastikan layanan MySQL/MariaDB aktif (di Laragon: Start All).</li>
            <li>Pastikan port dan host sesuai di <code>.env</code> (variabel <code>DB_HOST</code> dan <code>DB_PORT</code>).</li>
            <li>Pastikan database <code>{{ $database ?? '...' }}</code> sudah dibuat dan dapat diakses oleh user <code>{{ env('DB_USERNAME') }}</code>.</li>
            <li>Setelah mengubah <code>.env</code>: jalankan <code>php artisan config:clear</code>.</li>
        </ul>

        <p class="muted">Halaman akan mencoba lagi otomatis setiap 10 detik.</p>
    </div>
</body>
</html>

