<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    <link rel="icon" href="{{ asset('assets/images/logo_cetar.svg') }}" type="image/svg+xml">

    {{-- Fonts Cetar: Inter (UI) + JetBrains Mono (timer & angka) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:600,700,800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Shell utama Cetar: sidebar putih + topbar putih di atas background surface-soft.
     Alpine `sidebarOpen` menangani drawer mobile tanpa round-trip server. --}}

<body class="min-h-screen font-sans antialiased bg-surface-soft text-ink" x-data="{ sidebarOpen: false }">

    {{-- Komponen deteksi untuk sinkronisasi Database/Server --}}
    <livewire:timezone-detector />

    {{-- OVERLAY MOBILE: menutup drawer saat area gelap diklik --}}
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-secondary-dark/50 lg:hidden" x-cloak></div>

    {{-- SIDEBAR: fixed di mobile (drawer), static di desktop --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-surface shadow-card flex flex-col transition-transform duration-300 lg:translate-x-0">

        {{-- BRAND --}}
        <div class="px-5 pb-3 pt-6 flex items-center gap-3">
            <img src="{{ asset('assets/images/logo_cetar.svg') }}" alt="Logo" class="w-10 h-10 object-contain"
                onerror="this.style.display='none';">
            <h2 class="font-extrabold tracking-tight text-xl text-secondary">{{ config('app.name', 'BIMBEL CETAR') }}</h2>
        </div>

        {{-- MENU (role-based) --}}
        <nav class="flex-1 overflow-y-auto px-3 mt-4 space-y-1">
            @if (auth()->user()->role === 'super_admin')
                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-ink-faint">{{ __('Admin') }}</p>
                <x-ui.sidebar-link route="admin.dashboard" label="{{ __('Dashboard') }}">
                    <x-slot:icon><x-lucide-layout-dashboard class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="admin.users" pattern="admin.users*" label="{{ __('Users') }}">
                    <x-slot:icon><x-lucide-users class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="admin.packages" pattern="admin.packages*" label="{{ __('Paket') }}">
                    <x-slot:icon><x-lucide-package class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="admin.contents" pattern="admin.contents*" label="{{ __('Materi') }}">
                    <x-slot:icon><x-lucide-book-open class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="admin.quizzes" pattern="admin.quizzes*" label="{{ __('Kuis') }}">
                    <x-slot:icon><x-lucide-file-text class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="admin.withdrawals" pattern="admin.withdrawals*" label="{{ __('Penarikan') }}">
                    <x-slot:icon><x-lucide-banknote class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
            @endif

            @if (auth()->user()->role === 'user')
                <x-ui.sidebar-link route="user.dashboard" label="{{ __('Dashboard') }}">
                    <x-slot:icon><x-lucide-layout-dashboard class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="user.packages" label="{{ __('Paket') }}">
                    <x-slot:icon><x-lucide-package class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="user.transactions" pattern="user.transactions*" label="{{ __('Transaksi') }}">
                    <x-slot:icon><x-lucide-receipt-text class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
                <x-ui.sidebar-link route="user.affiliate" label="{{ __('Afiliasi') }}">
                    <x-slot:icon><x-lucide-gift class="w-4 h-4" /></x-slot:icon>
                </x-ui.sidebar-link>
            @endif

            <div class="my-3 border-t border-black/5"></div>

            <x-ui.sidebar-link route="settings" label="{{ __('Settings') }}">
                <x-slot:icon><x-lucide-settings class="w-4 h-4" /></x-slot:icon>
            </x-ui.sidebar-link>
        </nav>

        {{-- FOOTER SIDEBAR: kartu user ringkas --}}
        <div class="p-3">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-surface-tint">
                <div class="w-9 h-9 rounded-full brand-grad text-white flex items-center justify-center text-xs font-extrabold shrink-0">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="min-w-0 leading-tight">
                    <p class="text-sm font-semibold text-secondary truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-ink-muted truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- AREA KONTEN (offset selebar sidebar di desktop) --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 bg-surface shadow-card px-4 md:px-8 py-3 flex items-center gap-4">

            {{-- Hamburger (mobile only) --}}
            <button type="button" class="lg:hidden p-2 rounded-lg text-secondary hover:bg-surface-soft transition-all"
                @click.stop="sidebarOpen = true" aria-label="Open menu">
                <x-lucide-menu class="w-5 h-5" />
            </button>

            {{-- Global search: khusus Super Admin --}}
            <div class="flex-1 max-w-xl">
                @if (auth()->user()->role === 'super_admin')
                    <livewire:global-search-bar />
                @endif
            </div>

            <div class="flex items-center gap-2 md:gap-3 ms-auto">

                {{-- LIVE CLOCK (Alpine murni, tanpa server) --}}
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-secondary text-white rounded-full text-sm shadow-card"
                    x-data="{
                        time: '',
                        tz: Intl.DateTimeFormat().resolvedOptions().timeZone,
                        updateTime() {
                            this.time = new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        }
                    }" x-init="updateTime(); setInterval(() => updateTime(), 1000)">
                    <x-lucide-clock class="w-4 h-4 text-primary-light" />
                    <span x-text="time" class="font-mono font-extrabold tabular-nums w-[70px] text-center"></span>
                    <span x-text="tz" class="hidden xl:inline text-xs opacity-60 font-medium"></span>
                </div>

                <livewire:language-switcher />
                <livewire:navbar-notifications />

                {{-- USER MENU (Alpine dropdown; trigger pakai @click.stop karena panel punya @click.outside) --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click.stop="open = !open"
                        class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-surface-soft transition-all cursor-pointer">
                        <div class="w-9 h-9 rounded-full brand-grad text-white flex items-center justify-center text-xs font-extrabold">
                            {{ auth()->user()->initials() }}
                        </div>
                        <span class="text-sm font-bold text-secondary hidden md:block">{{ auth()->user()->name }}</span>
                        <x-lucide-chevron-down class="w-3.5 h-3.5 text-ink-muted" />
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition.origin.top.right x-cloak
                        class="absolute right-0 mt-2 w-72 bg-surface rounded-2xl shadow-hover p-2 z-50">

                        <div class="p-3 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full brand-grad text-white flex items-center justify-center text-sm font-extrabold shrink-0">
                                {{ auth()->user()->initials() }}
                            </div>
                            <div class="min-w-0 leading-tight">
                                <p class="font-bold text-secondary truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-ink-muted truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <div class="border-t border-black/5 my-1"></div>

                        <a href="{{ route('settings') }}"
                            class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-secondary hover:bg-surface-soft transition-all">
                            <x-lucide-settings class="w-4 h-4" /> {{ __('Settings') }}
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-bad hover:bg-bad-soft transition-all cursor-pointer">
                                <x-lucide-log-out class="w-4 h-4" /> {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="flex-1 p-4 md:p-8">
            {{ $slot }}
        </main>
    </div>

    <x-toast />
</body>

</html>
