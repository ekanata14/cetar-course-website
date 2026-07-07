<x-auth.shell heading="{{ __('Lupa password?') }}"
    subheading="{{ __('Masukkan email terdaftar, kami kirimkan link untuk reset password.') }}">

    {{-- Notifikasi sukses kirim link --}}
    @if (session('status'))
        <div class="flex items-center gap-3 p-3 rounded-xl bg-ok-soft mb-5">
            <x-lucide-mail-check class="w-5 h-5 text-ok shrink-0" />
            <p class="text-sm text-ink/90">{{ session('status') }}</p>
        </div>
    @endif

    <form wire:submit="sendLink" class="space-y-5">

        <x-ui.input label="{{ __('Email') }}" name="email" type="email" wire:model="email"
            placeholder="nama@email.com" autocomplete="email" required autofocus />

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="sendLink">{{ __('Kirim Link Reset') }}</span>
            <span wire:loading wire:target="sendLink">{{ __('Mengirim...') }}</span>
        </x-ui.button>
    </form>

    <p class="text-sm text-ink-muted text-center mt-8">
        <a href="{{ route('login') }}"
            class="inline-flex items-center gap-1.5 font-semibold text-primary hover:text-primary-dark transition-colors">
            <x-lucide-arrow-left class="w-4 h-4" /> {{ __('Kembali ke halaman masuk') }}
        </a>
    </p>
</x-auth.shell>
