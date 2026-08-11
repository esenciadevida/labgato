@extends('layouts.auth')

@section('title', 'Reset Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-card border border-neutral-200 p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Set New Password</h1>

    <form method="POST" action="{{ route('auth.password.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-neutral-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ $email ?? old('email') }}"
                readonly
                class="mt-1 block w-full rounded-md border border-neutral-300 bg-neutral-100 text-neutral-500 shadow-card sm:text-sm"
            >
            @error('email')
                <p class="mt-1 text-sm text-danger-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-neutral-700">New Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                class="mt-1 block w-full rounded-md border border-neutral-300 bg-white shadow-card focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
            >
            @error('password')
                <p class="mt-1 text-sm text-danger-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-neutral-700">Confirm Password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="mt-1 block w-full rounded-md border border-neutral-300 bg-white shadow-card focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm"
            >
        </div>

        <div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-card text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Reset Password
            </button>
        </div>
    </form>
</div>
@endsection
