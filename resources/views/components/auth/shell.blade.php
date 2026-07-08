{{--
    Shell halaman auth Cetar: split-screen.
    - Kiri (desktop only): panel navy dengan value proposition brand.
    - Kanan: kartu form putih di atas surface-soft.
    - heading/subheading: judul & pengantar di atas form.
--}}
@props([
    'heading',
    'subheading' => null,
])

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">

    {{-- KIRI: VISUAL & VALUE PROPOSITION --}}
    <div class="hidden lg:flex lg:col-span-6 xl:col-span-7 relative bg-secondary text-white flex-col justify-between overflow-hidden p-12 xl:p-16">

        {{-- Aksen dekoratif: blob orange lembut (bukan gradient liar — tetap dalam brand) --}}
        <div class="absolute -right-32 -top-32 w-96 h-96 rounded-full bg-primary/20 blur-3xl"></div>
        <div class="absolute -left-24 -bottom-24 w-80 h-80 rounded-full bg-primary/10 blur-3xl"></div>

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3">
            <img src="{{ asset('assets/images/logo_cetar.svg') }}" alt="Logo Cetar" class="w-10 h-10 object-contain"
                onerror="this.style.display='none';">
            <span class="font-extrabold tracking-tight text-2xl">{{ config('app.name', 'BIMBEL CETAR') }}</span>
        </a>

        {{-- Copywriting --}}
        <div class="relative z-10 max-w-xl space-y-6">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-primary-light">
                {{ __('Platform CBT & E-Learning Premium') }}
            </p>
            <h1 class="text-4xl xl:text-5xl font-extrabold tracking-tight leading-tight">
                {{ __('Lolos CPNS & SNBT dimulai dari latihan yang serius.') }}
            </h1>
            <p class="text-white/70 text-[15px] leading-relaxed">
                {{ __('Try out CAT sesuai standar resmi, pembahasan lengkap, dan analisis skor real-time. Belajar terarah, hasil terukur.') }}
            </p>

            {{-- Poin nilai jual --}}
            <ul class="space-y-3 pt-2">
                @foreach ([__('Simulasi CAT dengan timer & grid soal asli'), __('Pembahasan mendalam di setiap soal'), __('Ranking nasional setiap Try Out Akbar')] as $point)
                    <li class="flex items-center gap-3 text-sm text-white/80">
                        <span class="w-6 h-6 rounded-full brand-grad flex items-center justify-center shrink-0">
                            <x-lucide-check class="w-3.5 h-3.5 text-white" />
                        </span>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative z-10 text-xs text-white/40">© {{ date('Y') }} {{ config('app.name', 'BIMBEL CETAR') }}. All rights reserved.</p>
    </div>

    {{-- KANAN: FORM --}}
    <div class="lg:col-span-6 xl:col-span-5 flex items-center justify-center p-6 md:p-12">
        <div class="w-full max-w-md">

            {{-- Brand kecil (mobile only, karena panel kiri tersembunyi) --}}
            <a href="{{ route('home') }}" class="lg:hidden flex items-center gap-2 mb-8">
                <img src="{{ asset('assets/images/logo_cetar.svg') }}" alt="Logo Cetar" class="w-8 h-8 object-contain"
                    onerror="this.style.display='none';">
                <span class="font-extrabold tracking-tight text-xl text-secondary">{{ config('app.name', 'BIMBEL CETAR') }}</span>
            </a>

            <div class="mb-8">
                <h2 class="text-2xl font-extrabold tracking-tight text-secondary">{{ $heading }}</h2>
                @if ($subheading)
                    <p class="text-ink-muted text-[15px] mt-1.5">{{ $subheading }}</p>
                @endif
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
