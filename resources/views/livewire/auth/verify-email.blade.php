<x-auth.shell heading="{{ __('Verifikasi email kamu') }}"
    subheading="{{ __('Kami telah mengirim link verifikasi ke email kamu. Klik link tersebut untuk mengaktifkan akun.') }}">

    {{-- Notifikasi sukses kirim ulang --}}
    @if (session('status') == 'verification-link-sent' || session('success'))
        <div class="flex items-center gap-3 p-3 rounded-xl bg-ok-soft mb-5">
            <x-lucide-mail-check class="w-5 h-5 text-ok shrink-0" />
            <p class="text-sm text-ink/90">
                {{ session('success') ?? __('Link verifikasi baru telah dikirim ke email kamu.') }}
            </p>
        </div>
    @endif

    {{-- Ilustrasi sederhana: icon chip besar --}}
    <div class="flex justify-center my-8">
        <div class="w-20 h-20 rounded-2xl brand-grad text-white flex items-center justify-center shadow-hover">
            <x-lucide-mail-open class="w-9 h-9" />
        </div>
    </div>

    <div class="space-y-3">
        <x-ui.button type="button" class="w-full" wire:click="resend" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="resend">{{ __('Kirim Ulang Email Verifikasi') }}</span>
            <span wire:loading wire:target="resend">{{ __('Mengirim...') }}</span>
        </x-ui.button>

        <x-ui.button type="button" variant="ghost" class="w-full" wire:click="logout">
            <x-lucide-log-out class="w-4 h-4" /> {{ __('Keluar') }}
        </x-ui.button>
    </div>
</x-auth.shell>
