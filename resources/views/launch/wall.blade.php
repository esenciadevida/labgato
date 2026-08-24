<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" value="{{ csrf_token() }}">
    <meta name="robots" content="noindex">

    <title>Labgoto is coming - {{ config('app.name') }}</title>
    <meta name="description" content="A new digital platform for medical laboratory science is launching soon.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-primary-950 via-primary-900 to-slate-950 antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-16 text-center">
        @if(session('info'))
            <p class="mb-8 rounded-full border border-white/15 bg-white/5 px-5 py-2 text-sm text-white/80">
                {{ session('info') }}
            </p>
        @endif

        <div class="rounded-2xl border border-white/10 bg-white/95 p-4 shadow-panel dark:bg-slate-900/90">
            <x-brand.logo size="lg" />
        </div>

        <h1 class="mt-10 font-display text-4xl font-black uppercase tracking-[0.2em] text-white sm:text-6xl">
            Labgoto is coming
        </h1>
        <p class="mt-4 max-w-xl text-lg text-slate-300">
            A new digital platform for medical laboratory science.
        </p>

        <div class="mt-12">
            <x-launch.countdown :target="$settings->launchAtCarbon()" size="lg" />
        </div>

        <div class="mt-12 flex flex-col items-center gap-4 sm:flex-row">
            @if($settings->registration_open)
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-white px-8 py-3 text-sm font-semibold text-primary-900 shadow-card transition-colors hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    Create Account
                </a>
            @else
                <span class="inline-flex items-center rounded-xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-medium text-white/60">
                    Registration opens soon.
                </span>
            @endif
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center rounded-xl border border-white/20 px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                Login
            </a>
        </div>
    </main>

    <footer class="pb-8 text-center text-xs text-white/40">
        &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
    </footer>
</body>
</html>
