<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Konfirmasi Password') }} - {{ config('app.name', 'Cetar') }}</title>
    <link rel="icon" href="{{ asset('assets/images/logo_cetar.png') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Halaman konfirmasi password (dipakai Fortify sebelum aksi sensitif, mis. mengaktifkan 2FA) --}}

<body class="min-h-screen flex items-center justify-center bg-base-200 font-sans">
    <div class="w-full max-w-md bg-base-100 rounded-2xl shadow-lg p-8">
        <h1 class="text-xl font-bold mb-2">{{ __('Konfirmasi Password') }}</h1>
        <p class="text-sm opacity-70 mb-6">
            {{ __('Demi keamanan, silakan konfirmasi password Anda untuk melanjutkan.') }}
        </p>

        <form method="POST" action="{{ route('password.confirm.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="password" class="block text-sm font-semibold mb-1">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required autofocus autocomplete="current-password"
                    class="w-full rounded-xl border border-base-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary" />
                @error('password')
                    <p class="text-sm text-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full btn btn-primary rounded-xl font-bold">
                {{ __('Konfirmasi') }}
            </button>
        </form>
    </div>
</body>

</html>
