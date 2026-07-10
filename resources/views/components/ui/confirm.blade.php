{{--
    Modal konfirmasi Cetar — pengganti wire:confirm (dialog bawaan browser).
    Bungkus trigger apa pun (tombol) sebagai slot; klik trigger membuka modal.
    Aksi dijalankan lewat $wire pada komponen Livewire terdekat.

        <x-ui.confirm action="delete({{ $id }})" variant="danger"
            title="Hapus item?" message="Tindakan ini tidak bisa dibatalkan."
            confirm-label="Hapus">
            <button type="button" ...>...</button>
        </x-ui.confirm>

    - action  : ekspresi pemanggilan pada $wire, mis. "submitQuiz()" / "delete(5)"
    - variant : danger (merah, default) | primary (brand-grad)
--}}
@props([
    'action',
    'title' => __('Konfirmasi'),
    'message' => null,
    'confirmLabel' => __('Ya, Lanjutkan'),
    'cancelLabel' => __('Batal'),
    'variant' => 'danger',
])

<div x-data="{ open: false }" class="contents">
    {{-- Trigger: klik slot apa pun membuka modal --}}
    <div @click="open = true" class="contents">{{ $slot }}</div>

    {{-- Modal konfirmasi (gaya sama dengan x-ui.modal) --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] overflow-y-auto"
        role="dialog" aria-modal="true" @keydown.escape.window="open = false">

        {{-- Overlay: klik untuk batal --}}
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-secondary-dark/60" @click="open = false"></div>

        {{-- Panel --}}
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div x-show="open" x-transition.scale.origin.center
                class="relative w-full max-w-md bg-surface rounded-2xl shadow-hover p-6 md:p-7">

                <div class="flex gap-4">
                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                        {{ $variant === 'danger' ? 'bg-bad-soft text-bad' : 'bg-primary/10 text-primary-dark' }}">
                        @if ($variant === 'danger')
                            <x-lucide-alert-triangle class="w-5 h-5" />
                        @else
                            <x-lucide-circle-help class="w-5 h-5" />
                        @endif
                    </span>
                    <div class="flex-1 space-y-1.5">
                        <h3 class="text-lg font-extrabold tracking-tight text-secondary">{{ $title }}</h3>
                        @if ($message)
                            <p class="text-[15px] leading-relaxed text-ink/80">{{ $message }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 mt-6">
                    <button type="button" @click="open = false"
                        class="px-5 py-2.5 rounded-xl font-bold text-sm text-secondary hover:bg-surface-soft transition-all cursor-pointer">
                        {{ $cancelLabel }}
                    </button>
                    <button type="button" @click="$wire.{{ $action }}; open = false"
                        class="px-5 py-2.5 rounded-xl font-bold text-sm text-white transition-all cursor-pointer
                        {{ $variant === 'danger' ? 'bg-bad hover:bg-bad/90' : 'brand-grad hover:-translate-y-0.5' }}">
                        {{ $confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
