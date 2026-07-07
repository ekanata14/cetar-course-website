<div class="space-y-6">

    {{-- HEADER --}}
    <div class="space-y-1">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Program Afiliasi') }}</p>
        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Afiliasi & Komisi') }}</h1>
        <p class="text-[15px] leading-relaxed text-ink-muted">
            {{ __('Bagikan link referral-mu — dapatkan komisi :rate% dari setiap pembayaran teman yang kamu undang.', ['rate' => (int) (config('cetar.commission_rate') * 100)]) }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- SALDO + PENARIKAN --}}
        <div class="bg-secondary rounded-xl shadow-card p-6 text-white space-y-4">
            <div class="flex items-center gap-2">
                <x-lucide-wallet class="w-4 h-4 text-primary-light" />
                <p class="text-[11px] font-semibold uppercase tracking-wider text-white/60">{{ __('Saldo Komisi') }}</p>
            </div>
            <p class="text-3xl font-mono font-extrabold tabular-nums">
                Rp{{ number_format(auth()->user()->wallet_balance, 0, ',', '.') }}
            </p>
            <x-ui.button class="w-full" wire:click="openWithdrawForm">
                <x-lucide-banknote class="w-4 h-4" /> {{ __('Tarik Saldo') }}
            </x-ui.button>
            <p class="text-xs text-white/50">
                {{ __('Minimal penarikan Rp:min.', ['min' => number_format(config('cetar.min_withdrawal'), 0, ',', '.')]) }}
            </p>
        </div>

        {{-- LINK REFERRAL --}}
        <div class="lg:col-span-2">
            <x-ui.card class="!p-6 h-full space-y-4"
                x-data="{
                    copied: false,
                    copy() {
                        navigator.clipboard.writeText('{{ $this->referralLink }}');
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    }
                }">
                <div class="flex items-center gap-2">
                    <x-lucide-gift class="w-4 h-4 text-primary" />
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Link Referral Kamu') }}</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1 px-4 py-3 rounded-xl bg-surface-soft font-mono text-sm text-ink truncate">
                        {{ $this->referralLink }}
                    </div>
                    <x-ui.button variant="secondary" @click="copy()" class="shrink-0">
                        <span x-show="!copied" class="inline-flex items-center gap-2">
                            <x-lucide-copy class="w-4 h-4" /> {{ __('Salin') }}
                        </span>
                        <span x-show="copied" x-cloak class="inline-flex items-center gap-2">
                            <x-lucide-check class="w-4 h-4" /> {{ __('Tersalin!') }}
                        </span>
                    </x-ui.button>
                </div>

                <p class="text-sm text-ink-muted leading-relaxed">
                    {{ __('Kode kamu:') }}
                    <span class="font-mono font-extrabold text-primary-dark">{{ auth()->user()->referral_code }}</span>
                    · {{ __('Komisi otomatis masuk saldo setiap pembayaran mereka settled.') }}
                </p>
            </x-ui.card>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- LEDGER KOMISI --}}
        <x-ui.card class="!p-0 overflow-hidden">
            <div class="px-5 py-4">
                <h2 class="font-extrabold tracking-tight text-secondary">{{ __('Komisi Masuk') }}</h2>
            </div>
            <div class="divide-y divide-black/5">
                @forelse ($this->commissions as $commission)
                    <div class="px-5 py-3.5 flex items-center gap-3" wire:key="commission-{{ $commission->id }}">
                        <div class="w-9 h-9 rounded-full bg-ok-soft text-ok flex items-center justify-center shrink-0">
                            <x-lucide-arrow-down-left class="w-4 h-4" />
                        </div>
                        <div class="flex-1 min-w-0 leading-tight">
                            <p class="text-sm font-semibold text-secondary truncate">{{ $commission->referred->name }}</p>
                            <p class="text-xs text-ink-muted">{{ $commission->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                        <p class="font-mono font-extrabold tabular-nums text-ok text-sm shrink-0">
                            +Rp{{ number_format($commission->amount, 0, ',', '.') }}
                        </p>
                    </div>
                @empty
                    <p class="px-5 py-8 text-sm text-ink-muted text-center">
                        {{ __('Belum ada komisi. Mulai bagikan link referral-mu!') }}
                    </p>
                @endforelse
            </div>
        </x-ui.card>

        {{-- RIWAYAT PENARIKAN --}}
        <x-ui.card class="!p-0 overflow-hidden">
            <div class="px-5 py-4">
                <h2 class="font-extrabold tracking-tight text-secondary">{{ __('Riwayat Penarikan') }}</h2>
            </div>
            <div class="divide-y divide-black/5">
                @forelse ($this->withdrawals as $withdrawal)
                    @php
                        $statusStyle = match ($withdrawal->status->value) {
                            'success' => 'text-ok bg-ok-soft',
                            'pending' => 'text-warn-dark bg-warn-soft',
                            default => 'text-bad bg-bad-soft',
                        };
                    @endphp
                    <div class="px-5 py-3.5 flex items-center gap-3" wire:key="withdrawal-{{ $withdrawal->id }}">
                        <div class="flex-1 min-w-0 leading-tight">
                            <p class="text-sm font-semibold text-secondary">
                                Rp{{ number_format($withdrawal->amount, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-ink-muted truncate">
                                {{ $withdrawal->bank_details['bank_name'] ?? '' }} · {{ $withdrawal->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full shrink-0 {{ $statusStyle }}">
                            {{ $withdrawal->status->value }}
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-sm text-ink-muted text-center">{{ __('Belum ada penarikan.') }}</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    {{-- MODAL FORM PENARIKAN --}}
    <x-ui.modal wire:model="showWithdrawForm" title="{{ __('Tarik Saldo Komisi') }}" maxWidth="max-w-lg">
        <form wire:submit="requestWithdrawal" class="space-y-5">

            <x-ui.input label="{{ __('Nominal (Rp)') }}" name="amount" type="number" wire:model="amount"
                placeholder="{{ number_format(config('cetar.min_withdrawal'), 0, ',', '.') }}" min="1" />

            <x-ui.input label="{{ __('Nama Bank') }}" name="bankName" wire:model="bankName"
                placeholder="{{ __('Contoh: BCA') }}" />

            <x-ui.input label="{{ __('Nomor Rekening') }}" name="accountNumber" wire:model="accountNumber"
                placeholder="1234567890" />

            <x-ui.input label="{{ __('Nama Pemilik Rekening') }}" name="accountName" wire:model="accountName"
                placeholder="{{ __('Sesuai buku tabungan') }}" />

            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="requestWithdrawal">{{ __('Ajukan Penarikan') }}</span>
                    <span wire:loading wire:target="requestWithdrawal">{{ __('Memproses...') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
