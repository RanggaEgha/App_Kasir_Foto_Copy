@php
    // This template adapts Laravel Notification (MailMessage) variables
    // into our common dark email layout for consistent branding.
    $brand = config('app.name');
    $intro_lines = $introLines ?? [];
    $outro_lines = $outroLines ?? [];
    $subject = $subject ?? ($brand.' Notification');
    $title = $greeting ?? ($subject ?? null);
    $data = [
        'subject'      => $subject,
        'title'        => $title,
        'intro_lines'  => $intro_lines,
        'outro_lines'  => $outro_lines,
        'action_text'  => $actionText ?? null,
        'action_url'   => $actionUrl ?? null,
        'accent'       => match(($level ?? 'primary')) {
            'success' => '#22c55e',
            'error'   => '#ef4444',
            default   => '#22c55e',
        },
    ];
@endphp
@include('emails.notification', $data)

