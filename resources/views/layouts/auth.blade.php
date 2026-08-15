<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Login - ' . config('app.name'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/LabGoTo_favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/LabGoTo_favicon_32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/LabGoTo_favicon_16.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/LabGoTo_favicon_192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/LabGoTo_favicon_512.png') }}">
    <x-brand.fonts />
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased">
    <div class="flex min-h-screen">
        <div class="hidden lg:flex lg:w-[46%] xl:w-[44%] flex-col justify-between bg-primary-950 text-white">

            <div class="p-10 xl:p-14 flex flex-col min-h-screen justify-between">
                <x-brand.logo dark size="lg" />

                <div class="py-10">
                    <p class="eyebrow text-primary-300">Laboratory Toolkit</p>
                    <p class="mt-4 font-display font-bold text-4xl xl:text-[2.75rem] leading-tight tracking-tight text-white">
                        Precision tools for the modern laboratory
                    </p>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-white/65">
                        Evidence-based calculators, reference ranges, SOPs, and knowledge resources built for laboratory professionals who demand accuracy.
                    </p>
                    <ul class="mt-8 space-y-3 text-sm text-white/80">
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-600/40 text-primary-200">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.79 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </span>
                            Clinical laboratory calculators
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-600/40 text-primary-200">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.79 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </span>
                            Curated reference ranges and interpretation
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary-600/40 text-primary-200">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" focusable="false"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.79 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                            </span>
                            SOPs and quality resources for daily practice
                        </li>
                    </ul>
                </div>

                <p class="relative text-sm text-white/45">&copy; {{ date('Y') }} Labgoto. All rights reserved.</p>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center px-4 py-12 sm:px-6">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-10 flex justify-center">
                    <x-brand.logo theme size="lg" />
                </div>

                <div class="flex justify-end mb-4">
                    <x-theme-toggle />
                </div>

                <main>
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @livewireScriptConfig
    <x-ui.flash />
    <x-ui.confirm />
</body>
</html>
