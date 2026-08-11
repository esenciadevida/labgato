<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-neutral-50 flex items-center justify-center min-h-screen">
    <div class="text-center">
        @yield('content')
        <a href="/" class="mt-4 inline-block text-primary-700 hover:text-primary-800">Return Home</a>
    </div>
</body>
</html>
