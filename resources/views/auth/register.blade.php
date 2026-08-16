@extends('layouts.auth')

@section('title', 'Register - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Create your account</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">Join Labgoto to save your favorite tools and resources.</p>

    <div class="mt-8 space-y-5">
        <x-auth.google-button />

        <div class="flex items-center gap-3" role="separator" aria-label="or">
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
            <span class="text-xs font-medium uppercase tracking-wide text-slate-400 dark:text-slate-500">or</span>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
        </div>
    </div>

    <form method="POST" action="{{ route('register.submit') }}" class="mt-5 space-y-5">
        @csrf

        <x-ui.honeypot />

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="input-base mt-1.5 @error('name') input-error @enderror"
            >
            @error('name')
                <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
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
            autocomplete="new-password"
        />

        <x-auth.password-field
            id="password_confirmation"
            name="password_confirmation"
            label="Confirm Password"
            autocomplete="new-password"
        />

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Create Account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Sign in</a>
    </p>
</div>
@endsection
