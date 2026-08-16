@extends('layouts.auth')

@section('title', 'Member Login - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Member sign in</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">Sign in to access your Labgoto account.</p>

    <div class="mt-8 space-y-5">
        <x-auth.google-button />

        <div class="flex items-center gap-3" role="separator" aria-label="or">
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
            <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">or</span>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
        </div>
    </div>

    <form method="POST" action="{{ route('login.submit') }}" class="mt-5 space-y-5">
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

    <div class="mt-6 text-center space-y-2">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            New to Labgoto?
            <a href="{{ route('register') }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Create an account</a>
        </p>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('auth.login') }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Staff? Sign in to the admin area</a>
        </p>
    </div>
</div>
@endsection
