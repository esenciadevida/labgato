@extends('layouts.auth')

@section('title', 'Forgot Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-2">Reset Password</h1>
    <p class="text-sm text-gray-600 text-center mb-6">Enter your email address and we'll send you a link to reset your password.</p>

    <form method="POST" action="{{ route('auth.password.email') }}" class="space-y-4">
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
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Send Reset Link
            </button>
        </div>

        <div class="text-center">
            <a href="{{ route('auth.login') }}" class="text-sm text-blue-600 hover:text-blue-500">
                Back to Login
            </a>
        </div>
    </form>
</div>
@endsection
