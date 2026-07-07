{{--
    Tombol standar Cetar (lihat design_system.md).
    - variant: primary (brand-grad) | secondary (navy) | ghost (teks saja)
    - href: jika diisi, dirender sebagai <a> alih-alih <button>
--}}
@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-bold text-sm px-5 py-3 rounded-xl transition-all cursor-pointer';

    $variants = [
        // Aksi utama: gradient orange + lift saat hover
        'primary' => 'brand-grad text-white shadow-card hover:shadow-hover hover:-translate-y-0.5',
        // Aksi sekunder: navy solid
        'secondary' => 'bg-secondary text-white shadow-sm hover:bg-secondary-light hover:shadow-md',
        // Aksi tersier: tanpa background
        'ghost' => 'text-secondary hover:bg-surface-soft',
    ];

    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
