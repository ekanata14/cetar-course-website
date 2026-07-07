<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetar — Try Out CPNS & SNBT Online</title>
    <meta name="description" content="Platform try out CBT untuk persiapan CPNS dan UTBK-SNBT. Simulasi CAT, timer otomatis, penilaian per seksi, dan pembahasan lengkap.">

    <link rel="icon" href="{{ asset('assets/images/logo_cetar.png') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace']
                    },
                    colors: {
                        cetar: {
                            orange: '#F5872A',
                            'orange-soft': '#FBA94C',
                            'orange-dark': '#D9741A',
                            navy: '#22384D',
                            'navy-light': '#3A5575',
                            'navy-dark': '#16232F',
                            ok: '#27A35A',
                            warn: '#F2C200',
                            grid: '#94A3AB',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }

        .bg-grid-pattern {
            background-image: linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .brand-grad { background-image: linear-gradient(100deg, #F5872A 0%, #FBB823 100%); }
        .banner-grad { background-image: linear-gradient(105deg, #FBE6C2 0%, #FCEFDD 55%, #FDF6EC 100%); }
    </style>
</head>
<body class="antialiased bg-slate-50 text-slate-800 selection:bg-cetar-orange selection:text-white">

    {{-- NAVBAR --}}
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md shadow-sm">
        <div class="container mx-auto px-6 lg:px-12 flex justify-between items-center h-16">
            <a href="#" class="flex items-center gap-3 font-extrabold text-xl tracking-tight text-cetar-navy">
                <img src="{{ asset('assets/images/logo_cetar.png') }}" alt="Logo Cetar" class="w-9 h-9 object-contain" onerror="this.style.display='none';">
                Cetar
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#fitur" class="hover:text-cetar-orange transition">Fitur</a>
                <a href="#cara-kerja" class="hover:text-cetar-orange transition">Cara Kerja</a>
                <a href="#harga" class="hover:text-cetar-orange transition">Harga</a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-cetar-navy hover:text-cetar-orange transition">Masuk</a>
                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl brand-grad text-white text-sm font-bold shadow-md hover:-translate-y-0.5 transition-all">Daftar Gratis</a>
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <header class="relative bg-cetar-navy-dark text-white overflow-hidden">
        <div class="absolute inset-0 bg-grid-pattern opacity-40"></div>
        <div class="absolute -right-40 -top-20 w-96 h-96 bg-cetar-orange rounded-full mix-blend-screen filter blur-3xl opacity-20"></div>
        <div class="absolute -left-40 bottom-0 w-96 h-96 bg-cetar-navy-light rounded-full mix-blend-screen filter blur-3xl opacity-30"></div>

        <div class="relative z-10 container mx-auto px-6 lg:px-12 py-20 lg:py-28 grid grid-cols-1 lg:grid-cols-2 gap-14 items-center">
            <div class="opacity-0 animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold tracking-wide text-cetar-orange-soft mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cetar-orange opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-cetar-orange"></span>
                    </span>
                    Persiapan CPNS & UTBK-SNBT 2026
                </div>
                <h1 class="text-4xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
                    Lolos Ujian Impianmu<br>
                    <span class="text-transparent bg-clip-text brand-grad">Dimulai dari Latihan yang Tepat</span>
                </h1>
                <p class="text-lg lg:text-xl text-slate-300 mb-10 max-w-xl leading-relaxed">
                    Try out CBT dengan simulasi CAT seperti ujian asli — lengkap dengan timer, penilaian otomatis per seksi, dan pembahasan setiap soal.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl brand-grad font-bold text-white text-center shadow-lg shadow-orange-500/30 hover:scale-105 transition-transform">
                        Mulai Sekarang — Daftar Gratis
                    </a>
                    <a href="#harga" class="px-8 py-4 rounded-xl bg-white/10 border border-white/20 font-bold text-white text-center hover:bg-white/20 backdrop-blur-md transition">
                        Lihat Paket & Harga
                    </a>
                </div>
            </div>

            {{-- MOCK KARTU UJIAN --}}
            <div class="opacity-0 animate-fade-in-up delay-200 hidden lg:block">
                <div class="bg-white rounded-2xl shadow-2xl p-6 text-slate-800 max-w-md ml-auto">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Try Out SKD CPNS</p>
                            <p class="font-extrabold text-cetar-navy">Paket Pejuang CPNS</p>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-cetar-navy text-white">
                            <svg class="w-4 h-4 text-cetar-orange-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="font-mono font-bold text-sm tabular-nums">89:59</span>
                        </div>
                    </div>

                    <p class="text-sm font-semibold text-slate-500 mb-2">Soal 41 dari 110 · TIU</p>
                    <p class="text-sm text-slate-700 leading-relaxed mb-4">Jika semua peserta ujian belajar dengan konsisten, maka... <span class="text-slate-400">— pilih jawaban yang paling tepat.</span></p>

                    <div class="space-y-2 mb-5">
                        <div class="flex items-center gap-3 p-3 rounded-xl border-2 border-cetar-orange bg-orange-50 text-sm font-semibold">
                            <span class="w-6 h-6 rounded-full brand-grad text-white flex items-center justify-center text-xs font-extrabold">B</span>
                            Peluang kelulusan meningkat
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 text-sm text-slate-500">
                            <span class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold">C</span>
                            Tidak dapat disimpulkan
                        </div>
                    </div>

                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Navigasi Soal</p>
                    <div class="grid grid-cols-10 gap-1.5">
                        @foreach (range(1, 30) as $i)
                            <div class="h-6 rounded-md text-[10px] font-mono font-bold text-white flex items-center justify-center
                                {{ $i <= 18 ? 'bg-cetar-ok' : ($i <= 21 ? 'bg-cetar-warn' : 'bg-cetar-grid') }}">
                                {{ $i }}
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-4 mt-3 text-[11px] font-semibold text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cetar-ok"></span> Dijawab</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cetar-warn"></span> Ragu-ragu</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cetar-grid"></span> Belum</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- STATS STRIP --}}
    <section class="bg-white border-b border-slate-100">
        <div class="container mx-auto px-6 lg:px-12 py-8 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
            <div class="opacity-0 animate-fade-in-up">
                <p class="font-mono text-3xl font-extrabold text-cetar-orange">{{ $packages->sum('quizzes_count') }}+</p>
                <p class="text-sm font-semibold text-slate-500 mt-1">Try out siap dikerjakan</p>
            </div>
            <div class="opacity-0 animate-fade-in-up delay-100">
                <p class="font-mono text-3xl font-extrabold text-cetar-orange">2</p>
                <p class="text-sm font-semibold text-slate-500 mt-1">Jalur ujian: CPNS & SNBT</p>
            </div>
            <div class="opacity-0 animate-fade-in-up delay-200">
                <p class="font-mono text-3xl font-extrabold text-cetar-orange">24/7</p>
                <p class="text-sm font-semibold text-slate-500 mt-1">Akses dari perangkat apa pun</p>
            </div>
        </div>
    </section>

    {{-- FITUR --}}
    <section id="fitur" class="py-24 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="text-center max-w-3xl mx-auto mb-16 opacity-0 animate-fade-in-up">
                <h2 class="text-sm font-bold text-cetar-orange tracking-widest uppercase mb-2">Fitur Unggulan</h2>
                <h3 class="text-3xl lg:text-4xl font-extrabold text-cetar-navy mb-4">Semua yang Kamu Butuhkan untuk Lolos</h3>
                <p class="text-slate-600 text-lg">
                    Bukan sekadar bank soal. Cetar mensimulasikan pengalaman ujian sesungguhnya dan membedah hasilmu sampai ke akar.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all opacity-0 animate-fade-in-up delay-100">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-cetar-orange flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-cetar-navy mb-2">Simulasi CAT Seperti Ujian Asli</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Kerjakan soal satu per satu dengan grid navigasi, tandai soal ragu-ragu, dan rasakan atmosfer CAT standar BKN sebelum hari-H.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all opacity-0 animate-fade-in-up delay-100">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-cetar-navy flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-cetar-navy mb-2">Timer Otomatis & Anti Curang</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Hitung mundur berjalan persis seperti ujian sungguhan — jawaban tersimpan otomatis, dan ujian terkumpul sendiri saat waktu habis.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all opacity-0 animate-fade-in-up delay-200">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-cetar-orange flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-cetar-navy mb-2">Penilaian Otomatis per Seksi</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Skor langsung keluar begitu selesai — lengkap dengan rincian TWK, TIU, TKP: berapa benar, salah, dan kosong di tiap seksi.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all opacity-0 animate-fade-in-up delay-200">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-cetar-navy flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-cetar-navy mb-2">Pembahasan Lengkap Tiap Soal</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Setelah ujian, review semua soal beserta kunci jawaban dan penjelasannya — belajar dari kesalahan jadi jauh lebih cepat.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all opacity-0 animate-fade-in-up delay-300">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-cetar-orange flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-cetar-navy mb-2">Pembayaran Aman via DOKU</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Bayar dengan berbagai metode melalui gateway DOKU. Invoice terkirim otomatis ke email, akses paket langsung aktif setelah pembayaran.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all opacity-0 animate-fade-in-up delay-300">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-cetar-navy flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="text-xl font-bold text-cetar-navy mb-2">Afiliasi: Ajak Teman, Dapat Komisi</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Bagikan kode referralmu. Setiap teman yang membeli paket menghasilkan komisi yang bisa ditarik langsung ke rekening bankmu.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CARA KERJA --}}
    <section id="cara-kerja" class="py-24 bg-slate-50">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="text-center max-w-3xl mx-auto mb-16 opacity-0 animate-fade-in-up">
                <h2 class="text-sm font-bold text-cetar-orange tracking-widest uppercase mb-2">Cara Kerja</h2>
                <h3 class="text-3xl lg:text-4xl font-extrabold text-cetar-navy mb-4">Empat Langkah Menuju Skor Terbaikmu</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ([
                    ['title' => 'Daftar Akun', 'desc' => 'Buat akun gratis dalam satu menit, verifikasi email, dan langsung masuk dashboard.'],
                    ['title' => 'Pilih Paket & Bayar', 'desc' => 'Pilih paket CPNS atau SNBT sesuai tujuanmu, bayar aman lewat DOKU.'],
                    ['title' => 'Kerjakan Try Out', 'desc' => 'Latihan dengan simulasi CAT bertimer — kapan pun, dari perangkat apa pun.'],
                    ['title' => 'Pelajari Hasilmu', 'desc' => 'Lihat skor per seksi dan pembahasan tiap soal, lalu ulangi sampai konsisten lolos.'],
                ] as $i => $step)
                    <div class="text-center opacity-0 animate-fade-in-up delay-{{ min(($i + 1) * 100, 300) }}">
                        <div class="w-14 h-14 mx-auto rounded-full brand-grad text-white flex items-center justify-center text-xl font-mono font-extrabold shadow-lg shadow-orange-500/20 mb-5">
                            {{ $i + 1 }}
                        </div>
                        <h4 class="text-lg font-bold text-cetar-navy mb-2">{{ $step['title'] }}</h4>
                        <p class="text-slate-600 text-sm leading-relaxed max-w-xs mx-auto">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HARGA --}}
    <section id="harga" class="py-24 bg-cetar-navy-dark text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-grid-pattern opacity-30"></div>
        <div class="absolute -right-40 top-0 w-96 h-96 bg-cetar-orange rounded-full mix-blend-screen filter blur-3xl opacity-10"></div>

        <div class="container mx-auto px-6 lg:px-12 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16 opacity-0 animate-fade-in-up">
                <h2 class="text-sm font-bold text-cetar-orange-soft tracking-widest uppercase mb-2">Paket & Harga</h2>
                <h3 class="text-3xl lg:text-4xl font-extrabold mb-4">Investasi Kecil untuk Masa Depan Besar</h3>
                <p class="text-slate-300 text-lg">Pilih paket sesuai tujuanmu. Semua paket sudah termasuk simulasi CAT, penilaian otomatis, dan pembahasan lengkap.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-5xl mx-auto">
                @forelse ($packages as $package)
                    @php $bestPlan = $package->plans->sortByDesc('duration_days')->first(); @endphp
                    <div class="bg-white rounded-2xl p-8 text-slate-800 shadow-2xl opacity-0 animate-fade-in-up delay-{{ $loop->iteration * 100 }}">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h4 class="text-2xl font-extrabold text-cetar-navy">{{ $package->name }}</h4>
                                <p class="text-sm font-semibold text-cetar-orange mt-1">{{ $package->quizzes_count }} try out tersedia</p>
                            </div>
                            <div class="w-12 h-12 rounded-xl brand-grad text-white flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                        </div>
                        <p class="text-slate-600 text-sm leading-relaxed mb-6">{{ $package->description }}</p>

                        <div class="space-y-3">
                            @foreach ($package->plans as $plan)
                                <div class="flex items-center justify-between p-4 rounded-xl border {{ $plan->is($bestPlan) && $package->plans->count() > 1 ? 'border-cetar-orange bg-orange-50' : 'border-slate-200' }}">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-cetar-navy">{{ $plan->name }}</p>
                                            @if ($plan->is($bestPlan) && $package->plans->count() > 1)
                                                <span class="px-2 py-0.5 rounded-full brand-grad text-white text-[10px] font-extrabold uppercase tracking-wide">Paling Hemat</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $plan->duration_days }} hari akses penuh</p>
                                    </div>
                                    <p class="font-mono font-extrabold text-xl text-cetar-navy">Rp{{ number_format($plan->price, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('register') }}" class="mt-6 block w-full py-3.5 rounded-xl brand-grad text-white text-center font-bold shadow-md hover:-translate-y-0.5 transition-all">
                            Pilih {{ $package->name }}
                        </a>
                    </div>
                @empty
                    <div class="lg:col-span-2 bg-white/10 border border-white/20 rounded-2xl p-12 text-center backdrop-blur-md">
                        <h4 class="text-xl font-bold mb-2">Paket segera hadir</h4>
                        <p class="text-slate-300 text-sm">Daftar sekarang agar jadi yang pertama tahu saat paket try out dibuka.</p>
                        <a href="{{ route('register') }}" class="inline-block mt-6 px-8 py-3.5 rounded-xl brand-grad text-white font-bold">Daftar Gratis</a>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- AFILIASI CTA --}}
    <section class="banner-grad">
        <div class="container mx-auto px-6 lg:px-12 py-16 flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="max-w-2xl text-center lg:text-left">
                <h3 class="text-2xl lg:text-3xl font-extrabold text-cetar-navy mb-3">Belajar Sambil Menghasilkan? Bisa.</h3>
                <p class="text-slate-700 leading-relaxed">
                    Ikut program afiliasi Cetar: bagikan kode referralmu ke teman seperjuangan, dapatkan komisi dari setiap pembelian paket, dan tarik saldomu langsung ke rekening bank.
                </p>
            </div>
            <a href="{{ route('register') }}" class="px-8 py-4 rounded-xl bg-cetar-navy text-white font-bold shadow-lg hover:-translate-y-0.5 transition-all shrink-0">
                Gabung & Mulai Referral
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-cetar-navy-dark text-slate-300 py-12">
        <div class="container mx-auto px-6 lg:px-12 flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <div class="flex items-center gap-2 font-extrabold text-xl tracking-tight text-white justify-center md:justify-start">
                    <img src="{{ asset('assets/images/logo_cetar.png') }}" alt="Logo Cetar" class="w-8 h-8 object-contain" onerror="this.style.display='none';">
                    Cetar
                </div>
                <p class="text-sm text-slate-400 mt-2 max-w-sm text-center md:text-left">
                    Platform try out CBT untuk persiapan CPNS dan UTBK-SNBT dengan simulasi CAT dan pembahasan lengkap.
                </p>
            </div>
            <div class="flex flex-col items-center md:items-end gap-3">
                <div class="flex gap-6 text-sm font-semibold">
                    <a href="#fitur" class="hover:text-cetar-orange-soft transition">Fitur</a>
                    <a href="#harga" class="hover:text-cetar-orange-soft transition">Harga</a>
                    <a href="{{ route('login') }}" class="hover:text-cetar-orange-soft transition">Masuk</a>
                    <a href="{{ route('register') }}" class="hover:text-cetar-orange-soft transition">Daftar</a>
                </div>
                <p class="text-xs text-slate-500">© {{ date('Y') }} Cetar. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll('.opacity-0');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.remove('opacity-0');
                        entry.target.classList.add('animate-fade-in-up');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            elements.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
