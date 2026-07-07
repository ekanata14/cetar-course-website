{{--
    Kartu standar Cetar: putih, rounded-xl, shadow-card, tanpa border keras.
    - hover: aktifkan efek lift + shadow-hover (untuk kartu yang bisa diklik)
--}}
@props([
    'hover' => false,
])

<div
    {{ $attributes->merge([
        'class' => 'bg-surface rounded-xl shadow-card p-5 '.($hover ? 'hover:shadow-hover hover:-translate-y-0.5 transition-all' : ''),
    ]) }}>
    {{ $slot }}
</div>
