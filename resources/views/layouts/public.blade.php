<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <x-public.meta
        :title="$pageTitle ?? config('app.name')"
        :description="$metaDescription ?? config('app.name') . ' - Professional Laboratory Toolkit'"
        :noindex="$metaNoindex ?? false"
    />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-50 flex flex-col min-h-screen">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:p-4 focus:bg-white focus:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
        Skip to main content
    </a>

    <x-public.header />

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <x-public.footer />

    @stack('scripts')
    @livewireScripts
</body>
</html>
