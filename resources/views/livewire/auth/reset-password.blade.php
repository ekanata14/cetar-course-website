<x-auth.shell heading="{{ __('Atur password baru') }}"
    subheading="{{ __('Gunakan password yang kuat dan mudah kamu ingat.') }}">

    <form wire:submit="resetPassword" class="space-y-5">

        <x-ui.input label="{{ __('Email') }}" name="email" type="email" wire:model="email"
            autocomplete="email" required />

        <x-ui.input label="{{ __('Password Baru') }}" name="password" type="password" wire:model="password"
            placeholder="{{ __('Minimal 6 karakter') }}" autocomplete="new-password" required autofocus />

        <x-ui.input label="{{ __('Konfirmasi Password Baru') }}" name="password_confirmation" type="password"
            wire:model="password_confirmation" placeholder="{{ __('Ulangi password baru') }}"
            autocomplete="new-password" required />

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="resetPassword">{{ __('Reset Password') }}</span>
            <span wire:loading wire:target="resetPassword">{{ __('Memproses...') }}</span>
        </x-ui.button>
    </form>

    <p class="text-sm text-ink-muted text-center mt-8">
        <a href="{{ route('login') }}"
            class="inline-flex items-center gap-1.5 font-semibold text-primary hover:text-primary-dark transition-colors">
            <x-lucide-arrow-left class="w-4 h-4" /> {{ __('Kembali ke halaman masuk') }}
        </a>
    </p>
</x-auth.shell>
