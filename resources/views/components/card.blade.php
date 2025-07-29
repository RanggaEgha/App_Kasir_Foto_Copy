{{-- Komponen Card --}}
@props(['class' => ''])   {{-- opsional attr tambahan --}}

<div {{ $attributes->merge(['class' => 'card shadow-sm mb-4 '.$class]) }}>
    <div class="card-body">
        {{ $slot }}        {{-- konten yang dibungkus card --}}
    </div>
</div>
