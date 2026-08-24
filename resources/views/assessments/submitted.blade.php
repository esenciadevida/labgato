@extends('layouts.public')

@section('title', 'Examination Submitted - ' . config('app.name'))

@section('content')
<main class="mx-auto max-w-2xl px-4 py-16">
    <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40">
            <svg class="h-9 w-9 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
        </div>

        <h1 class="mt-6 text-2xl font-bold text-slate-900 dark:text-slate-100">Your examination has been submitted.</h1>

        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
            {{ $attempt->assessment->title }} — attempt #{{ $attempt->attempt_number }} is securely recorded.
        </p>

        @if($released)
            <div class="mt-8">
                <a href="{{ route('exams.result', $attempt) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-8 py-3.5 text-base font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-700 dark:hover:bg-blue-600">
                    View My Result
                </a>
            </div>
        @else
            <div class="mx-auto mt-8 max-w-sm rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800">
                <div class="flex items-center justify-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                    <svg class="h-5 w-5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    Results locked
                </div>
                <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Results will be revealed when LabGOTO officially launches.
                </p>
            </div>
        @endif

        <div class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-700">
            <a href="{{ route('account.dashboard') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Back to Dashboard
            </a>
        </div>
    </div>
</main>
@endsection
