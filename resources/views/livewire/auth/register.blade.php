<x-auth.shell heading="{{ __('Buat akun baru') }}"
    subheading="{{ __('Gratis untuk memulai — pilih paket belajarmu kapan saja.') }}">

    <form wire:submit="register" class="space-y-5">

        <x-ui.input label="{{ __('Nama Lengkap') }}" name="name" wire:model="name"
            placeholder="Budi Santoso" autocomplete="name" required autofocus />

        <x-ui.input label="{{ __('Email') }}" name="email" type="email" wire:model="email"
            placeholder="nama@email.com" autocomplete="email" required />

        <x-ui.input label="{{ __('Password') }}" name="password" type="password" wire:model="password"
            placeholder="{{ __('Minimal 6 karakter') }}" autocomplete="new-password" required />

        <x-ui.input label="{{ __('Konfirmasi Password') }}" name="password_confirmation" type="password"
            wire:model="password_confirmation" placeholder="{{ __('Ulangi password') }}"
            autocomplete="new-password" required />

        {{-- Info kode referral: hanya tampil jika datang dari link afiliasi (?ref=) --}}
        @if ($referralCode)
            <div class="flex items-center gap-3 p-3 rounded-xl banner-grad">
                <x-lucide-gift class="w-5 h-5 text-primary-dark shrink-0" />
                <p class="text-sm text-ink/90">
                    {{ __('Kamu diundang dengan kode') }}
                    <span class="font-mono font-extrabold text-primary-dark">{{ $referralCode }}</span>
                </p>
            </div>
        @endif

        <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="register">{{ __('Daftar') }}</span>
            <span wire:loading wire:target="register">{{ __('Memproses...') }}</span>
        </x-ui.button>
    </form>

    {{-- Kode referral ikut terbawa ke registrasi via Google --}}
    <x-auth.google-button :ref="$referralCode" />

    <p class="text-sm text-ink-muted text-center mt-8">
        {{ __('Sudah punya akun?') }}
        <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-primary-dark transition-colors">
            {{ __('Masuk') }}
        </a>
    </p>
</x-auth.shell>
