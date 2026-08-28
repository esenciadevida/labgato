@extends('layouts.auth')

@section('title', 'Verify Sign-In Code - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Check your email</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">
        @if($email)
            We sent a one-time sign-in code to <strong class="text-slate-700 dark:text-slate-200">{{ $email }}</strong>.
        @else
            We sent a one-time sign-in code to your email address.
        @endif
    </p>

    <form method="POST" action="{{ route('auth.login.two-factor.verify', ['token' => $token]) }}" class="mt-8 space-y-5">
        @csrf

        <x-ui.honeypot />

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="code" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Sign-in code</label>
            <input
                id="code"
                name="code"
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                value="{{ old('code') }}"
                required
                autofocus
                autocomplete="one-time-code"
                class="input-base mt-1.5 tracking-[0.5em] text-center @error('code') input-error @enderror"
            >
            @error('code')
                <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-sm text-slate-500 dark:text-slate-400">This code expires in 10 minutes.</p>

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Continue
        </button>
    </form>

    <form method="POST" action="{{ route('auth.login.two-factor.resend', ['token' => $token]) }}" class="mt-4">
        @csrf

        <x-ui.honeypot />

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
            Resend code
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('auth.login') }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Use a different account</a>
        </p>
    </div>
</div>
@endsection