@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Login to LabGato</h1>

    <form method="POST" action="{{ route('auth.login.attempt') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Sign In
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('auth.password.request') }}" class="text-sm text-blue-600 hover:text-blue-500">
                Forgot your password?
            </a>
        </div>
    </form>
</div>
@endsection
