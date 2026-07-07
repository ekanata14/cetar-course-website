{{--
    Modal standar Cetar: panel putih rounded-2xl di atas overlay navy.
    Visibility di-entangle dengan properti boolean Livewire:
        <x-ui.modal wire:model="showForm" title="...">...</x-ui.modal>
    Alpine menangani buka/tutup + transisi tanpa round-trip server.
--}}
@props([
    'title' => null,
    'maxWidth' => 'max-w-2xl',
])

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-show="show" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
    @keydown.escape.window="show = false">

    {{-- Overlay: klik untuk menutup --}}
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-secondary-dark/60" @click="show = false"></div>

    {{-- Panel --}}
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div x-show="show" x-transition.scale.origin.center
            class="relative w-full {{ $maxWidth }} bg-surface rounded-2xl shadow-hover p-6 md:p-8">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 mb-6">
                @if ($title)
                    <h3 class="text-lg font-extrabold tracking-tight text-secondary">{{ $title }}</h3>
                @endif
                <button type="button" @click="show = false"
                    class="p-1.5 rounded-lg text-ink-muted hover:bg-surface-soft hover:text-secondary transition-all cursor-pointer ms-auto"
                    aria-label="Close">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
