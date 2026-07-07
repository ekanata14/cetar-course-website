<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Verifikasi Dua Langkah') }} - {{ config('app.name', 'Cetar') }}</title>
    <link rel="icon" href="{{ asset('assets/images/logo_cetar.png') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Halaman tantangan 2FA (dirender Fortify setelah login jika user mengaktifkan 2FA).
     Alpine dipakai untuk toggle antara input kode OTP dan recovery code tanpa round-trip server. --}}

<body class="min-h-screen flex items-center justify-center bg-base-200 font-sans">
    <div class="w-full max-w-md bg-base-100 rounded-2xl shadow-lg p-8" x-data="{ recovery: false }">
        <h1 class="text-xl font-bold mb-2">{{ __('Verifikasi Dua Langkah') }}</h1>
        <p class="text-sm opacity-70 mb-6" x-show="!recovery">
            {{ __('Masukkan kode dari aplikasi authenticator Anda.') }}
        </p>
        <p class="text-sm opacity-70 mb-6" x-show="recovery" x-cloak>
            {{ __('Masukkan salah satu recovery code Anda.') }}
        </p>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
            @csrf

            <div x-show="!recovery">
                <label for="code" class="block text-sm font-semibold mb-1">{{ __('Kode Autentikasi') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    class="w-full rounded-xl border border-base-300 px-4 py-3 font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-primary" />
            </div>

            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="block text-sm font-semibold mb-1">{{ __('Recovery Code') }}</label>
                <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code"
                    class="w-full rounded-xl border border-base-300 px-4 py-3 font-mono focus:outline-none focus:ring-2 focus:ring-primary" />
            </div>

            @error('code')
                <p class="text-sm text-error">{{ $message }}</p>
            @enderror
            @error('recovery_code')
                <p class="text-sm text-error">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full btn btn-primary rounded-xl font-bold">
                {{ __('Verifikasi') }}
            </button>

            <button type="button" class="w-full text-sm opacity-70 hover:opacity-100 underline"
                @click="recovery = !recovery">
                <span x-show="!recovery">{{ __('Gunakan recovery code') }}</span>
                <span x-show="recovery" x-cloak>{{ __('Gunakan kode authenticator') }}</span>
            </button>
        </form>
    </div>
</body>

</html>
