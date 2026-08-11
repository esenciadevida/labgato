@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-card border border-neutral-200 p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Login to LabGato</h1>

    <form method="POST" action="{{ route('auth.login.attempt') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-neutral-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="mt-1 block w-full rounded-md border border-neutral-300 bg-white shadow-card focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
            >
            @error('email')
                <p class="mt-1 text-sm text-danger-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-neutral-700">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                class="mt-1 block w-full rounded-md border border-neutral-300 bg-white shadow-card focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
            >
            @error('password')
                <p class="mt-1 text-sm text-danger-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-card text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Sign In
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('auth.password.request') }}" class="text-sm text-primary-700 hover:text-primary-800">
                Forgot your password?
            </a>
        </div>
    </form>
</div>
@endsection
