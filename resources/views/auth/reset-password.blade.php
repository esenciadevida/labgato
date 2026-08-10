@extends('layouts.auth')

@section('title', 'Reset Password - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Set New Password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token ?? '' }}">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <x-ui.input id="email" name="email" type="email" required />
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <x-ui.input id="password" name="password" type="password" required />
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <x-ui.input id="password_confirmation" name="password_confirmation" type="password" required />
        </div>
        <x-ui.button type="submit" class="w-full">Reset Password</x-ui.button>
    </form>
</div>
@endsection
