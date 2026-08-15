<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Error')</title>
    <x-brand.fonts />
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-950 text-slate-100 antialiased flex items-center justify-center min-h-screen">
    <main class="text-center px-4">
        <div class="mb-8 flex justify-center">
            <x-brand.logo size="lg" />
        </div>
        @yield('content')
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                Go Home
            </a>
            <a href="{{ route('contact.create') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-700 px-5 py-2.5 text-sm font-semibold text-slate-200 transition-colors hover:bg-slate-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                Contact Support
            </a>
        </div>
    </main>
</body>
</html>
