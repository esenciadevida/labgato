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
<body class="relative min-h-screen overflow-x-hidden bg-gradient-to-b from-primary-950 via-primary-900 to-slate-950 antialiased">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 left-1/2 h-[32rem] w-[52rem] -translate-x-1/2 rounded-full bg-primary-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-72 w-72 translate-x-1/3 rounded-full bg-cyan-400/10 blur-3xl"></div>
        <div class="absolute bottom-24 left-0 h-64 w-64 -translate-x-1/3 rounded-full bg-emerald-400/10 blur-3xl"></div>
    </div>

    <main class="relative flex min-h-screen flex-col items-center px-4 py-12 text-center sm:py-16">
        @if(session('info'))
            <p class="mb-8 rounded-full border border-white/15 bg-white/5 px-5 py-2 text-sm text-white/80 backdrop-blur">
                {{ session('info') }}
            </p>
        @endif

        <div class="rounded-2xl border border-white/10 bg-white/95 p-4 shadow-panel dark:bg-slate-900/90">
            <x-brand.logo size="lg" />
        </div>

        <p class="mt-10 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.25em] text-white/70 backdrop-blur">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-success-400"></span>
            </span>
            Coming soon
        </p>

        <h1 class="mt-6 font-display text-4xl font-black uppercase tracking-[0.18em] text-white sm:text-6xl">
            Labgoto
            <span class="mt-2 block bg-gradient-to-r from-primary-300 via-cyan-200 to-emerald-300 bg-clip-text text-transparent">is coming</span>
        </h1>

        <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-slate-300">
            A new digital platform for medical laboratory science.
            Practice, learn and compete - all in one place.
        </p>

        <section class="mt-12 w-full max-w-3xl" aria-label="Countdown to launch">
            <x-launch.countdown :target="$settings->launchAtCarbon()" size="lg" reload-on-zero />
        </section>

        <div class="mt-12 flex w-full max-w-sm flex-col items-center gap-4 sm:max-w-none sm:flex-row sm:justify-center">
            @if($settings->registration_open)
                <a href="{{ route('register') }}"
                   class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-8 py-3 text-sm font-semibold text-primary-900 shadow-card transition-all hover:-translate-y-0.5 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:w-auto">
                    Create Account
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center rounded-xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-medium text-white/60">
                    Registration opens soon.
                </span>
            @endif
            <a href="{{ route('login') }}"
               class="inline-flex w-full items-center justify-center rounded-xl border border-white/20 px-8 py-3 text-sm font-semibold text-white transition-colors hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/40 sm:w-auto">
                Login
            </a>
        </div>

        <section class="mt-16 w-full max-w-4xl" aria-label="What is coming to Labgoto">
            <h2 class="text-xs font-semibold uppercase tracking-[0.25em] text-white/50">What is waiting at launch</h2>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-left backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-400/15 text-primary-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-base font-bold text-white">Reference Ranges</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-400">
                        Quick, reliable clinical chemistry and haematology values at your fingertips.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-left backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-400/15 text-cyan-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm.375 0h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zM9 6.75V6a2.25 2.25 0 012.25-2.25h1.5A2.25 2.25 0 0115 6v.75m3 0v11.25A2.25 2.25 0 0115.75 20.25h-7.5A2.25 2.25 0 016 18V6.75m12 0H6"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-base font-bold text-white">Smart Calculators</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-400">
                        Anion gap, corrected sodium and more - computed instantly, with interpretation.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-left backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-base font-bold text-white">SOPs &amp; Knowledge</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-400">
                        Community-built standard operating procedures and a growing knowledge hub.
                    </p>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 text-left backdrop-blur transition-colors hover:border-white/20 hover:bg-white/10">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400/15 text-amber-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 font-display text-base font-bold text-white">Monthly Challenge</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-400">
                        Timed mock exams, a live leaderboard and verifiable certificates.
                    </p>
                </div>
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
