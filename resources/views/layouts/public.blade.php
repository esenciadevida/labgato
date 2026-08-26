<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/LabGoTo_favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/LabGoTo_favicon_32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/LabGoTo_favicon_16.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/LabGoTo_favicon_192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('assets/LabGoTo_favicon_512.png') }}">
    <x-brand.fonts />
    <x-theme-script />
    @php
        $filteredRequest = collect(['q', 'search', 'category', 'categories', 'tags', 'tag', 'sort', 'difficulty', 'department'])
            ->contains(fn ($key) => request()->query->has($key));
    @endphp
    <x-public.meta
        :title="$pageTitle ?? config('app.name')"
        :description="$metaDescription ?? config('app.name') . ' - Professional Laboratory Toolkit'"
        :noindex="($metaNoindex ?? false) || $filteredRequest"
        :ogType="$ogType ?? 'website'"
        :ogImage="$ogImage ?? null"
        :publishedTime="$publishedTime ?? null"
        :modifiedTime="$modifiedTime ?? null"
        :authorName="$authorName ?? null"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @php $ga4Id = setting('analytics.ga4_measurement_id', ''); @endphp
    @if($ga4Id)
    <script>
        (function() {
            try {
                var consent = localStorage.getItem('labgato-cookie-consent');
                if (consent === 'accepted') {
                    var s = document.createElement('script');
                    s.async = true;
                    s.src = 'https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}';
                    document.head.appendChild(s);
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());
                    gtag('config', '{{ $ga4Id }}');
                }
            } catch(e) {}
        })();
    </script>
    @endif
    @stack('head')
</head>
<body class="bg-white text-slate-900 dark:bg-slate-950 dark:text-slate-100 antialiased flex flex-col min-h-screen">
    <x-public.schema />
    <div id="top" aria-hidden="true" class="absolute top-0 pointer-events-none"></div>
    <x-public.announcement-banner />

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-4 focus:bg-slate-900 focus:text-primary-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
        Skip to main content
    </a>

    <x-public.header />

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <x-public.footer />

    {{-- Reading progress bar --}}
    <div x-data="readingProgress" x-init="init()" class="no-print">
        <div class="fixed top-0 left-0 z-[60] h-1 w-full bg-transparent pointer-events-none">
            <div class="h-full bg-primary-500 transition-[width] duration-150 ease-out" :style="`width: ${percent}%`"></div>
        </div>
    </div>

    {{-- Back-to-top button --}}
    <div x-data="backToTop" x-init="init()" class="no-print">
        <button
            x-show="visible"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            @click="scrollToTop()"
            type="button"
            class="fixed bottom-6 right-6 z-50 inline-flex items-center justify-center w-11 h-11 rounded-full bg-primary-600 text-white shadow-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950 transition-colors"
            aria-label="Back to top"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
        </button>
    </div>

    @stack('scripts')
    @livewireScriptConfig
    @if(auth()->check() && auth()->user()->isMember() && !auth()->user()->hasOnboarded())
        <x-onboarding.welcome-modal :tour-steps="app(\App\Services\OnboardingService::class)->dashboardTourSteps()" />
    @endif
    <x-public.popup :popups="app(\App\Services\PublicNoticeService::class)->activePopups()" />
    <x-public.cookie-consent />
    <x-ui.flash />
    <x-ui.confirm />
</body>
</html>
