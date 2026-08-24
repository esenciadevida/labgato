<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" value="{{ csrf_token() }}">
    <meta name="robots" content="noindex">

    <title>Launch Dashboard - {{ config('app.name') }}</title>
    <meta name="description" content="Your Labgoto launch waiting room.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative min-h-screen overflow-x-hidden bg-gradient-to-b from-primary-950 via-primary-900 to-slate-950 antialiased">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 left-1/2 h-[30rem] w-[48rem] -translate-x-1/2 rounded-full bg-primary-500/20 blur-3xl"></div>
        <div class="absolute bottom-10 right-0 h-72 w-72 translate-x-1/3 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute bottom-24 left-0 h-64 w-64 -translate-x-1/3 rounded-full bg-emerald-400/10 blur-3xl"></div>
    </div>

    <main class="relative mx-auto flex min-h-screen w-full max-w-3xl flex-col items-center px-4 py-12 text-center sm:py-16">
        <div class="flex w-full items-center justify-between">
            <div class="rounded-xl border border-white/10 bg-white/95 p-2.5 shadow-panel dark:bg-slate-900/90">
                <x-brand.logo size="sm" />
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-white/15 px-3.5 py-2 text-xs font-semibold text-white/70 transition-colors hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40">
                    Log out
                </button>
            </form>
        </div>

        @if(session('success'))
            <p class="mt-8 rounded-xl border border-success-400/25 bg-success-500/10 px-5 py-3 text-sm font-medium text-success-200 backdrop-blur">
                {{ session('success') }}
            </p>
        @endif

        <h1 class="mt-10 font-display text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">
            Welcome, {{ $user->profile?->display_name ?: $user->name }}
        </h1>
        <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-slate-300">
            You are registered for the Labgoto launch. Sit tight - everything unlocks the moment we go live.
        </p>

        <section class="mt-12 w-full" aria-label="Countdown to launch">
            <x-launch.countdown :target="$settings->launchAtCarbon()" size="lg" />
        </section>

        <section class="mt-14 w-full max-w-lg" aria-label="Your challenge status">
            <div class="relative isolate overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 text-left shadow-[0_18px_50px_-20px_rgb(0_0_0/0.6)] backdrop-blur-md sm:p-7">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/40 to-transparent" aria-hidden="true"></div>

                <h2 class="font-display text-sm font-bold uppercase tracking-[0.2em] text-white/60">ASCPI Challenge</h2>

                <dl class="mt-5 space-y-4">
                    <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-4">
                        <dt class="text-sm text-slate-400">Status</dt>
                        <dd>
                            @if($settings->challenge_open)
                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-success-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Registered
                                </span>
                            @else
                                <span class="text-sm font-medium text-slate-400">Challenge opens soon.</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-sm text-slate-400">Result</dt>
                        <dd>
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-white/60">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Locked
                            </span>
                        </dd>
                    </div>
                </dl>

                <p class="mt-6 border-t border-white/5 pt-4 text-sm leading-relaxed text-slate-400">
                    Your result is revealed when Labgoto officially launches.
                </p>
            </div>
        </section>
    </main>

    <footer class="relative pb-8 text-center">
        <nav class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-white/50" aria-label="Legal">
            <a href="{{ route('legal.privacy-policy') }}" class="transition-colors hover:text-white/80">Privacy Policy</a>
            <a href="{{ route('legal.terms-of-service') }}" class="transition-colors hover:text-white/80">Terms of Service</a>
            <a href="{{ route('legal.disclaimer') }}" class="transition-colors hover:text-white/80">Disclaimer</a>
        </nav>
        <p class="mt-3 text-xs text-white/40">
            &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.
        </p>
    </footer>
</body>
</html>
