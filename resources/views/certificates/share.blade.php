@extends('layouts.public')

@php $metaNoindex = true; @endphp

@section('title', $pageTitle)

@section('content')
<section class="py-12 sm:py-16">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-white shadow-panel ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">

            {{-- Header brand bar --}}
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-950 via-blue-900 to-slate-900 px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <span class="font-display text-xl font-black tracking-tight text-white">LabGOTO</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/20 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-widest text-amber-300 ring-1 ring-amber-400/30">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M6.267 3.455a3.9 3.9 0 017.466 0l.5 1.5h1.767a.75.75 0 01.61 1.15l-3.41 4.945a.75.75 0 01-1.22 0l-3.41-4.944a.75.75 0 01.61-1.151h1.767l.5-1.5z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M10 13a3 3 0 100 6 3 3 0 000-6z" clip-rule="evenodd"/></svg>
                        Verified
                    </span>
                </div>
                <span class="text-xs font-medium text-slate-300">{{ $certificate->issued_at?->format('M j, Y') }}</span>
            </div>

            {{-- Body --}}
            <div class="px-6 py-8 text-center sm:px-10">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Certificate of {{ ucfirst($certificate->type) }}</p>
                <h1 class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-white">{{ $certificate->course?->title ?? $certificate->assessment?->title }}</h1>
                @if($certificate->course)
                    <p class="mt-2 inline-block rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Course Completion</p>
                @endif

                <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">Proudly presented to</p>
                <p class="mt-1 text-xl font-bold text-blue-900 dark:text-blue-300">{{ $holderName }}</p>

                {{-- Score --}}
                <div class="mt-7 flex items-end justify-center gap-1">
                    <p class="font-display text-6xl font-black leading-none text-slate-900 dark:text-white">{{ number_format((float) $certificate->score_percent, 1) }}</p>
                    <p class="mb-1 text-3xl font-semibold text-slate-400">%</p>
                </div>

                {{-- Score bar --}}
                <div class="mx-auto mt-4 h-1.5 w-full max-w-sm overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                    <div class="h-full rounded-full bg-gradient-to-r from-blue-700 via-blue-500 to-blue-400" style="width: {{ min(100, max(0, (float) $certificate->score_percent)) }}%;"></div>
                </div>

                {{-- Rank --}}
                @if($rank !== null)
                    <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-4 py-1.5 text-sm font-semibold text-blue-900 ring-1 ring-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11a1 1 0 11-2 0V8a1 1 0 112 0v5z" clip-rule="evenodd"/></svg>
                            Rank #{{ $rank }} of {{ number_format($participants) }}
                        </span>
                        <span class="inline-flex items-center rounded-full bg-amber-50 px-4 py-1.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-100 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800">
                            Top {{ $topPercent }}%
                        </span>
                    </div>
                @endif

                {{-- Verify --}}
                <div class="mt-7 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                    Verify authenticity at
                    <a href="{{ $certificate->verifyUrl() }}" class="font-semibold text-blue-700 underline underline-offset-2 hover:text-blue-800 dark:text-blue-300">
                        {{ $certificate->verifyUrl() }}
                    </a>
                </div>
            </div>

            {{-- Share actions --}}
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-5 dark:border-slate-700 dark:bg-slate-800/40">
                <p class="text-center text-[11px] font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Share your result</p>
                <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <a href="{{ $intents['whatsapp'] }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 00-8.47 15.26L2 22l4.9-1.44A10 10 0 1012 2zm5.26 14.24c-.22.62-1.28 1.16-1.77 1.2-.47.04-.9.22-3.04-.66-2.56-1.06-4.18-3.75-4.31-3.92-.13-.17-1.03-1.37-1.03-2.61 0-1.24.65-1.85.88-2.1.23-.25.5-.31.67-.31h.48c.15 0 .36-.06.56.43.21.5.71 1.73.77 1.86.06.13.1.28.02.45-.08.17-.12.27-.24.42-.12.15-.26.33-.37.44-.12.12-.25.26-.11.5.14.25.62 1.02 1.33 1.65.91.81 1.68 1.06 1.92 1.18.24.12.38.1.52-.06.14-.17.6-.7.76-.94.16-.24.32-.2.54-.12.22.08 1.4.66 1.64.78.24.12.4.18.46.28.06.1.06.58-.16 1.2z"/></svg>
                        WhatsApp
                    </a>
                    <a href="{{ $intents['x'] }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.6l5.24 6.93 6.06-6.93zm-1.29 19.5h2.04L6.49 3.24H4.3L17.61 20.65z"/></svg>
                        X
                    </a>
                    <a href="{{ $intents['linkedin'] }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-blue-700 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.24 8h4.52v14H.24V8zm7.44 0h4.33v1.92h.06c.6-1.14 2.07-2.34 4.26-2.34 4.56 0 5.4 3 5.4 6.9V22h-4.52v-6.55c0-1.56-.03-3.57-2.18-3.57-2.18 0-2.51 1.7-2.51 3.46V22H7.68V8z"/></svg>
                        LinkedIn
                    </a>
                    <a href="{{ $intents['facebook'] }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.09 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.09 24 18.1 24 12.07z"/></svg>
                        Facebook
                    </a>
                </div>
            </div>
        </div>

        <footer class="mt-8 text-center">
            <p class="text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                LabGOTO certificates recognize performance in LabGOTO assessments and are NOT ASCP certifications nor official ASCP credentials.
            </p>
        </footer>
    </div>
</section>
@endsection
