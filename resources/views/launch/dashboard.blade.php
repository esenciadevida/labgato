@extends('layouts.public')

@section('title', 'Launch Dashboard - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <header class="text-center">
            <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">
                Welcome to Labgoto, {{ $user->name }}
            </h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400">
                You are registered for the Labgoto launch.
            </p>
        </header>

        <div class="mt-10 flex justify-center rounded-2xl bg-gradient-to-b from-primary-950 via-primary-900 to-slate-950 px-6 py-12 shadow-panel">
            <x-launch.countdown :target="$settings->launchAtCarbon()" size="md" />
        </div>

        <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-800">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100">
                ASCPI Challenge
            </h2>

            <dl class="mt-5 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Status</dt>
                    <dd>
                        @if($settings->challenge_open)
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-success-600 dark:text-success-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Registered
                            </span>
                        @else
                            <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Challenge opens soon.</span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-sm text-slate-500 dark:text-slate-400">Result</dt>
                    <dd>
                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Locked
                        </span>
                    </dd>
                </div>
            </dl>

            <p class="mt-6 border-t border-slate-100 pt-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300">
                Your challenge result will be revealed when Labgoto officially launches.
            </p>
        </div>
    </div>
</section>
@endsection
