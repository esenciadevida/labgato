@extends('layouts.auth')

@section('title', 'Forgot Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Reset Password</h1>
    <p class="text-sm text-gray-600 text-center mb-4">Enter your email to receive a reset link.</p>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <x-ui.input id="email" name="email" type="email" required autofocus />
        </div>
        <x-ui.button type="submit" class="w-full">Send Reset Link</x-ui.button>
    </form>
</div>
@endsection
