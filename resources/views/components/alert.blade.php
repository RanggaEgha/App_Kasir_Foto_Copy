@props([
    'type'    => 'info',
    'message' => '',          // ← bisa string ATAU array
])

@php
    // Map tipe → kelas Bootstrap/CoreUI
    $classes = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ];

    // Pastikan konten sudah string
    $text = is_array($message)         // ← kunci solusi
            ? implode('<br>', $message) // gabungkan array tiap baris
            : $message;
@endphp

@if ($text)
    <div {{ $attributes->merge([
            'class' => 'alert '.$classes[$type] ?? 'alert-info'.
                       ' rounded-pill py-2 px-3 shadow-sm'
        ]) }}>
        {!! $text !!}
    </div>
@endif
