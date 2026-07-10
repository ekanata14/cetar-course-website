<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center gap-4">
        <div class="flex-1 space-y-1">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Admin') }}</p>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-secondary">{{ __('Kelola Paket') }}</h1>
        </div>
        <x-ui.button wire:click="openCreate">
            <x-lucide-plus class="w-4 h-4" /> {{ __('Paket Baru') }}
        </x-ui.button>
    </div>

    {{-- SEARCH --}}
    <div class="max-w-sm">
        <x-ui.input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari paket...') }}" />
    </div>

    {{-- TABEL PAKET --}}
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[11px] font-semibold uppercase tracking-wider text-ink-faint">
                        <th class="px-5 py-3.5">{{ __('Paket') }}</th>
                        <th class="px-5 py-3.5">{{ __('Plans') }}</th>
                        <th class="px-5 py-3.5">{{ __('Konten') }}</th>
                        <th class="px-5 py-3.5">{{ __('Subscriber') }}</th>
                        <th class="px-5 py-3.5">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse ($packages as $package)
                        <tr wire:key="package-{{ $package->id }}" class="hover:bg-surface-tint transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-bold text-secondary">{{ $package->name }}</p>
                                <p class="text-xs text-ink-muted font-mono">{{ $package->slug }}</p>
                            </td>
                            <td class="px-5 py-4 font-mono font-extrabold tabular-nums text-secondary">
                                {{ $package->plans_count }}
                            </td>
                            <td class="px-5 py-4 font-mono font-extrabold tabular-nums text-secondary">
                                {{ $package->quizzes_count }} <span class="font-sans font-normal text-xs text-ink-muted">{{ __('kuis') }}</span>
                            </td>
                            <td class="px-5 py-4 font-mono font-extrabold tabular-nums text-secondary">
                                {{ $package->subscriptions_count }}
                            </td>
                            <td class="px-5 py-4">
                                {{-- Toggle status langsung dari tabel --}}
                                <button type="button" wire:click="toggleActive({{ $package->id }})"
                                    class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-1 rounded-full cursor-pointer transition-all {{ $package->is_active ? 'text-ok bg-ok-soft' : 'text-ink-muted bg-surface-soft' }}">
                                    {{ $package->is_active ? __('Aktif') : __('Nonaktif') }}
                                </button>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Susun roadmap belajar (modul + urutan materi/try out) --}}
                                    <a href="{{ route('admin.packages.roadmap', $package) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-primary-dark bg-primary/10 hover:brand-grad hover:text-white transition-all">
                                        <x-lucide-map class="w-4 h-4" /> {{ __('Roadmap') }}
                                    </a>
                                    <button type="button" wire:click="openEdit({{ $package->id }})"
                                        class="p-2 rounded-lg text-secondary hover:bg-surface-soft transition-all cursor-pointer"
                                        aria-label="Edit">
                                        <x-lucide-pencil class="w-4 h-4" />
                                    </button>
                                    <x-ui.confirm action="delete({{ $package->id }})"
                                        title="{{ __('Hapus Paket?') }}"
                                        message="{{ __('Semua plan, langganan, dan tautan konten ikut terhapus.') }}"
                                        confirm-label="{{ __('Hapus') }}">
                                        <button type="button"
                                            class="p-2 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer"
                                            aria-label="Delete">
                                            <x-lucide-trash-2 class="w-4 h-4" />
                                        </button>
                                    </x-ui.confirm>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-ink-muted">
                                {{ __('Belum ada paket. Buat paket pertamamu!') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($packages->hasPages())
            <div class="px-5 py-4 border-t border-black/5">
                {{ $packages->links() }}
            </div>
        @endif
    </x-ui.card>

    {{-- MODAL FORM CREATE/EDIT --}}
    <x-ui.modal wire:model="showForm" title="{{ $editingId ? __('Edit Paket') : __('Paket Baru') }}">
        <form wire:submit="save" class="space-y-5">

            <x-ui.input label="{{ __('Nama Paket') }}" name="name" wire:model="name"
                placeholder="{{ __('Contoh: Pejuang CPNS 2026') }}" />

            <x-ui.textarea label="{{ __('Deskripsi') }}" name="description" wire:model="description"
                placeholder="{{ __('Apa saja yang didapat member paket ini?') }}" />

            {{-- Toggle aktif --}}
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" wire:model="isActive" class="w-4 h-4 rounded accent-[#F5872A]">
                <span class="text-sm font-semibold text-secondary">{{ __('Paket aktif (tampil di katalog)') }}</span>
            </label>

            {{-- BARIS PLANS (tier harga) --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-secondary">{{ __('Tier Harga') }}</p>
                    <button type="button" wire:click="addPlan"
                        class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-primary-dark transition-colors cursor-pointer">
                        <x-lucide-plus class="w-4 h-4" /> {{ __('Tambah Tier') }}
                    </button>
                </div>

                @error('plans')
                    <p class="text-sm text-bad">{{ $message }}</p>
                @enderror

                @foreach ($plans as $index => $plan)
                    <div class="grid grid-cols-12 gap-2 items-start p-3 rounded-xl bg-surface-tint"
                        wire:key="plan-row-{{ $index }}">
                        <div class="col-span-12 sm:col-span-4">
                            <x-ui.input name="plans.{{ $index }}.name" wire:model="plans.{{ $index }}.name"
                                placeholder="{{ __('Nama (1 Bulan)') }}" />
                        </div>
                        <div class="col-span-5 sm:col-span-3">
                            <x-ui.input type="number" name="plans.{{ $index }}.duration_days"
                                wire:model="plans.{{ $index }}.duration_days" placeholder="{{ __('Hari') }}" min="1" />
                        </div>
                        <div class="col-span-5 sm:col-span-4">
                            <x-ui.input type="number" name="plans.{{ $index }}.price"
                                wire:model="plans.{{ $index }}.price" placeholder="{{ __('Harga (Rp)') }}" min="0" />
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex justify-end pt-2">
                            <button type="button" wire:click="removePlan({{ $index }})"
                                class="p-2 rounded-lg text-bad hover:bg-bad-soft transition-all cursor-pointer"
                                aria-label="Remove plan">
                                <x-lucide-trash-2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-end gap-2 pt-2">
                <x-ui.button variant="ghost" type="button" x-on:click="show = false">{{ __('Batal') }}</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? __('Simpan Perubahan') : __('Buat Paket') }}</span>
                    <span wire:loading wire:target="save">{{ __('Menyimpan...') }}</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
