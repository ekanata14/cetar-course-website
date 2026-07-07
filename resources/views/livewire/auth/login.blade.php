<x-auth.shell heading="{{ __('Selamat datang kembali!') }}"
    subheading="{{ __('Masuk untuk melanjutkan persiapanmu.') }}">

    <form wire:submit="login" class="space-y-5">

        <x-ui.input label="{{ __('Email') }}" name="email" type="email" wire:model="email"
            placeholder="nama@email.com" autocomplete="email" required autofocus />

        <div>
            <x-ui.input label="{{ __('Password') }}" name="password" type="password" wire:model="password"
                placeholder="••••••••" autocomplete="current-password" required />

            <div class="flex items-center justify-between mt-3">
                {{-- Remember me --}}
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded accent-[#F5872A]">
                    <span class="text-sm text-ink-muted">{{ __('Ingat saya') }}</span>
                </label>

                <a href="{{ route('password.request') }}"
                    class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors">
                    {{ __('Lupa password?') }}
                </a>
            </div>
        </div>

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">{{ __('Masuk') }}</span>
            <span wire:loading wire:target="login">{{ __('Memproses...') }}</span>
        </x-ui.button>
    </form>

    <x-auth.google-button />

    <p class="text-sm text-ink-muted text-center mt-8">
        {{ __('Belum punya akun?') }}
        <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-primary-dark transition-colors">
            {{ __('Daftar sekarang') }}
        </a>
    </p>
</x-auth.shell>
