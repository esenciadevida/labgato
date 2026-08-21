@extends('layouts.auth')

@section('title', 'Accept Invitation - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Set up your account</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">
        You've been invited to join the LabGato admin team as a
        <span class="font-medium text-primary-700 dark:text-primary-400">{{ $invitation->role?->name }}</span>.
    </p>

    <div class="mt-6 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800/60">
        <p class="text-sm text-slate-600 dark:text-slate-300"><span class="text-slate-400">Name:</span> {{ $invitation->name }}</p>
        <p class="text-sm text-slate-600 dark:text-slate-300"><span class="text-slate-400">Email:</span> {{ $invitation->email }}</p>
    </div>

    <form method="POST" action="{{ route('admin.invitation.accept', ['token' => $token]) }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Create Password</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                autofocus
                autocomplete="new-password"
                class="input-base mt-1.5 @error('password') input-error @enderror"
            >
            @error('password')
                <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Confirm Password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="input-base mt-1.5 @error('password_confirmation') input-error @enderror"
            >
            @error('password_confirmation')
                <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button type="submit" class="w-full justify-center">
            Create My Account
        </x-ui.button>
    </form>

    <p class="mt-4 text-xs text-center text-slate-400 dark:text-slate-500">
        This invitation link expires {{ $invitation->expires_at->diffForHumans() }} and can only be used once.
    </p>
</div>
@endsection
