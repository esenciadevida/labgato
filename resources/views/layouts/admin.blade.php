<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin - ' . config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="flex min-h-screen">
        @include('components.admin.sidebar')
        <div class="flex-1">
            @include('components.admin.header')
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
