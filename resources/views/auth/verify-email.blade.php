@extends('layouts.auth')

@section('title', 'Verify Email - ' . config('app.name'))

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-panel p-8 sm:p-10 dark:border-slate-700 dark:bg-slate-900">
    <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100 text-center">Verify your email</h1>
    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 text-center">
        A verification link has been sent to your email address. Please click the link to verify your account.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
        @csrf
        <button type="submit" class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-card transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            Resend Verification Email
        </button>
    </form>
</div>
@endsection
