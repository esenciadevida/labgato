@extends('layouts.public')

@section('title', 'Certificate Verification')

@section('content')
<section class="py-14">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <header class="text-center">
            <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">Certificate Verification</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Check that a LabGOTO certificate is genuine.</p>
        </header>

        @if($status === 'valid')
            <div class="mt-8 overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm dark:border-emerald-800 dark:bg-slate-900">
                <div class="flex items-center gap-3 border-b border-emerald-100 bg-emerald-50 px-6 py-4 dark:border-emerald-800/60 dark:bg-emerald-900/20">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="font-semibold text-emerald-800 dark:text-emerald-300">Certificate Verified</h2>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400">This certificate was issued by LabGOTO and is currently valid.</p>
                    </div>
                </div>

                <dl class="divide-y divide-slate-100 px-6 py-2 dark:divide-slate-800">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Holder</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->user?->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Assessment</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->assessment?->title }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Tier</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->label() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Score</dt>
                        <dd class="text-right text-sm font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($certificate->score_percent, 1) }}%</dd>
                    </div>
                    @if($certificate->rank_at_issue !== null)
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Rank</dt>
                            <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">#{{ $certificate->rank_at_issue }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Issued</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->issued_at->format('F j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Certificate No.</dt>
                        <dd class="text-right font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->certificate_number }}</dd>
                    </div>
                </dl>
            </div>
        @elseif($status === 'revoked')
            <div class="mt-8 rounded-xl border border-red-200 bg-white p-8 text-center shadow-sm dark:border-red-800 dark:bg-slate-900">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                <h2 class="mt-4 font-semibold text-red-700 dark:text-red-300">This certificate has been revoked.</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">It is no longer considered valid by LabGOTO.</p>
            </div>
        @else
            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <h2 class="mt-4 font-semibold text-slate-700 dark:text-slate-200">No certificate matches this code.</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Double-check the code on the certificate and try again.</p>
            </div>
        @endif

        <footer class="mt-8 text-center">
            <p class="text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                Verified via LabGOTO ({{ config('app.name') }}). LabGOTO certificates recognize performance in LabGOTO assessments.
                They are NOT ASCP certifications nor official ASCP credentials.
            </p>
        </footer>
    </div>
</section>
@endsection
