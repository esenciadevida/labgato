@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Welcome back</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">Sign in to continue to Labgoto.</p>

    <form method="POST" action="{{ route('auth.login.attempt') }}" class="mt-8 space-y-5">
        @csrf

        <x-ui.honeypot />

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
                class="input-base mt-1.5 @error('email') input-error @enderror"
            >
            @error('email')
                <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-auth.password-field
            id="password"
            name="password"
            label="Password"
            autocomplete="current-password"
            :show-toggle="false"
        />

        <div class="text-right">
            <a href="{{ route('auth.password.request') }}" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                Forgot your password?
            </a>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Sign In
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('login') }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Member? Sign in to your account</a>
        </p>
    </div>
</div>
@endsection
