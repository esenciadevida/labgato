<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Login - ' . config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-neutral-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md px-4">
        @if(session('status'))
            <div class="mb-4 rounded-md bg-success-50 p-4" role="status">
                <p class="text-sm text-success-600">{{ session('status') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-md bg-danger-50 p-4" role="alert">
                <p class="text-sm text-danger-600">{{ session('error') }}</p>
            </div>
        @endif

        @yield('content')
    </div>
</body>
</html>
