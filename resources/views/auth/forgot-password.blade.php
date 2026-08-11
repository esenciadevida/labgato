@extends('layouts.auth')

@section('title', 'Forgot Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-card border border-neutral-200 p-8">
    <h1 class="text-2xl font-bold text-center mb-2">Reset Password</h1>
    <p class="text-sm text-neutral-600 text-center mb-6">Enter your email address and we'll send you a link to reset your password.</p>

    <form method="POST" action="{{ route('auth.password.email') }}" class="space-y-4">
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
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-card text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Send Reset Link
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('auth.login') }}" class="text-sm text-primary-700 hover:text-primary-800">
                Back to Login
            </a>
        </div>
    </form>
</div>
@endsection
