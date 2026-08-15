@extends('layouts.auth')

@section('title', 'Forgot Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Reset your password</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">Enter your email address and we'll send you a link to reset your password.</p>

    <form method="POST" action="{{ route('auth.password.email') }}" class="mt-8 space-y-5">
        @csrf

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

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Send Reset Link
        </button>

        <div class="text-center">
            <a href="{{ route('auth.login') }}" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                Back to Login
            </a>
        </div>
    </form>
</div>
@endsection
