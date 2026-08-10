@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h1 class="text-2xl font-bold text-center mb-6">Login to LabGato</h1>

    @if(session('error'))
        <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <x-ui.input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus />
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <x-ui.input id="password" name="password" type="password" required />
        </div>
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300">
            <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
        </div>
        <x-ui.button type="submit" class="w-full">Sign In</x-ui.button>
    </form>
</div>
@endsection
