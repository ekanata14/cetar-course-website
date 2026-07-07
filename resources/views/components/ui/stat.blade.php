{{--
    Kartu statistik dashboard: icon chip orange yang menyala (brand-grad) saat kartu di-hover.
    - title: label kecil di atas nilai
    - value: angka utama (dirender font-mono tabular)
    - icon slot: isi dengan <x-lucide-* /> ukuran w-5 h-5
--}}
@props([
    'title',
    'value',
])

<div {{ $attributes->merge(['class' => 'group bg-surface rounded-xl shadow-card hover:shadow-hover hover:-translate-y-0.5 transition-all p-5']) }}>
    <div class="flex items-center gap-4">
        <div
            class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0 group-hover:brand-grad group-hover:text-white transition-all">
            {{ $icon ?? '' }}
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint truncate">{{ $title }}</p>
            <p class="text-2xl font-mono font-extrabold tabular-nums text-secondary leading-tight">{{ $value }}</p>
        </div>
    </div>
</div>
