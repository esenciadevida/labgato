@extends('layouts.auth')

@section('title', 'Reset Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Set a new password</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">Choose a strong password for your Labgoto account.</p>

    <form method="POST" action="{{ route('auth.password.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('PUT')

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ $email ?? old('email') }}"
                readonly
                class="input-base mt-1.5 bg-slate-100 text-slate-500 dark:text-slate-400 shadow-none"
            >
            @error('email')
                <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-auth.password-field
            id="password"
            name="password"
            label="New Password"
            autocomplete="new-password"
        />

        <x-auth.password-field
            id="password_confirmation"
            name="password_confirmation"
            label="Confirm Password"
            autocomplete="new-password"
        />

        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Reset Password
        </button>
    </form>
</div>
@endsection
