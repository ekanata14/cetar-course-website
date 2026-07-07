{{--
    Item menu sidebar Cetar.
    - Aktif: bg-primary/10 + teks primary-dark + icon chip brand-grad (lihat design_system.md)
    - Idle: teks secondary, chip menyala brand-grad saat group-hover
    - route: nama route tujuan; pattern (opsional): pola route untuk state aktif, mis. "admin.users*"
--}}
@props([
    'route',
    'pattern' => null,
    'label',
])

@php
    $active = request()->routeIs($pattern ?? $route);
@endphp

<a href="{{ route($route) }}"
    {{ $attributes->merge([
        'class' => 'group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all '.($active
            ? 'bg-primary/10 text-primary-dark font-semibold'
            : 'text-secondary font-medium hover:bg-surface-soft'),
    ]) }}>
    <span
        class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 transition-all {{ $active ? 'brand-grad text-white' : 'bg-surface-soft text-secondary group-hover:brand-grad group-hover:text-white' }}">
        {{ $icon ?? '' }}
    </span>
    <span class="truncate">{{ $label }}</span>
</a>
