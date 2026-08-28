@extends('layouts.public')

@php $metaNoindex = true; @endphp

@section('title', 'Certificate Verification')

@section('content')
<section class="py-12 sm:py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">

        <header class="text-center">
            <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">Certificate Verification</h1>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400">
                Confirm the authenticity of a LabGOTO certificate.
            </p>
        </header>

        @if($status === 'valid')
            @php
                $showName = $certificate->user?->profile?->show_on_leaderboard !== false;
                $holderName = $showName
                    ? ($certificate->user?->profile?->display_name ?: $certificate->user?->name)
                    : 'A LabGOTO participant';
            @endphp

            @if($credentialSchema)
                <script type="application/ld+json">{!! $credentialSchema !!}</script>
            @endif

            <div class="mt-8 flex flex-col items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 sm:flex-row sm:justify-center dark:border-emerald-800/60 dark:bg-emerald-900/20">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <div class="text-center sm:text-left">
                    <h2 class="font-semibold text-emerald-800 dark:text-emerald-300">Certificate Verified</h2>
                    <p class="text-xs text-emerald-700 dark:text-emerald-400">Verified and issued by LabGOTO.</p>
                </div>
            </div>

            <!-- The certificate -->
            <div class="relative mx-auto mt-8 max-w-3xl overflow-hidden rounded-xl border-[3px] border-primary-800 bg-[#fffdf6] shadow-xl">
                <div class="absolute inset-2 rounded-lg border border-amber-400/70 pointer-events-none" aria-hidden="true"></div>
                <div class="relative p-6 sm:p-10">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                        <div class="text-left">
                            <p class="font-display text-lg font-black tracking-widest text-primary-800">LabGOTO</p>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Medical Laboratory Knowledge Platform</p>
                        </div>
                        <div class="text-left sm:text-right">
                            <p class="text-xs text-slate-500">Certificate No.</p>
                            <p class="font-mono text-sm font-bold text-slate-800">{{ $certificate->certificate_number }}</p>
                        </div>
                    </div>

                    <div class="mt-8 text-center">
                        <span class="inline-block rounded-full border border-amber-400 bg-white px-5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-amber-800">
                            {{ $certificate->label() }}
                        </span>
                        <p class="mt-6 text-sm italic text-slate-500">This certificate is proudly presented to</p>
                        <p class="mt-2 font-serif text-3xl font-bold text-primary-900 sm:text-4xl">
                            {{ $holderName }}
                        </p>
                        <div class="mx-auto mt-3 h-px w-40 bg-amber-400"></div>
                        <p class="mt-5 text-sm text-slate-700">
                            for successfully completing the
                            @if($certificate->course)
                                <span class="font-semibold text-primary-800">{{ $certificate->course->title }}</span> course
                            @else
                                <span class="font-semibold text-primary-800">{{ $certificate->assessment?->title }}</span>
                            @endif
                        </p>

                        @if($certificate->type !== 'participation')
                            <p class="mt-3 text-sm text-slate-600">
                                Score <span class="font-bold text-slate-900">{{ number_format($certificate->score_percent, 1) }}%</span>
                                @if($certificate->rank_at_issue !== null)
                                    &nbsp;&middot;&nbsp; Rank <span class="font-bold text-slate-900">#{{ $certificate->rank_at_issue }}</span>
                                @endif
                            </p>
                        @endif

                        <p class="mt-4 text-xs text-slate-500">Issued {{ $certificate->issued_at->format('F j, Y') }}</p>
                    </div>

                    <div class="mt-8 flex items-center justify-between border-t border-slate-200 pt-5">
                        <div class="text-left">
                            <p class="text-sm font-bold text-slate-800">Verified</p>
                            <p class="text-[11px] text-slate-500">{{ config('app.name') }} Authenticity Check</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Valid
                        </span>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <dl class="divide-y divide-slate-100 px-6 dark:divide-slate-800">
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Holder</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $holderName }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $certificate->course ? 'Course' : 'Assessment' }}</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->course?->title ?? $certificate->assessment?->title }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Tier</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->label() }}</dd>
                    </div>
                    @if($certificate->type !== 'participation')
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
                    @endif
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Issued</dt>
                        <dd class="text-right text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->issued_at->format('F j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Certificate No.</dt>
                        <dd class="text-right font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->certificate_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 py-3">
                        <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">Verification Code</dt>
                        <dd class="text-right font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->verification_code }}</dd>
                    </div>
                </dl>
            </div>
        @elseif($status === 'revoked')
            <div class="mx-auto mt-8 max-w-md rounded-xl border border-red-200 bg-white p-8 text-center shadow-sm dark:border-red-800 dark:bg-slate-900">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                <h2 class="mt-4 font-semibold text-red-700 dark:text-red-300">This certificate has been revoked.</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">It is no longer considered valid by LabGOTO.</p>
            </div>
        @else
            <div class="mx-auto mt-8 max-w-md rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <h2 class="mt-4 font-semibold text-slate-700 dark:text-slate-200">No certificate matches this code.</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Double-check the code on the certificate and try again.</p>
            </div>
        @endif

        <footer class="mt-10 text-center">
            <p class="mx-auto max-w-xl text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                Verified via LabGOTO ({{ config('app.name') }}). LabGOTO certificates recognize performance in LabGOTO assessments.
                They are NOT ASCP certifications nor official ASCP credentials.
            </p>
        </footer>
    </div>
</section>
@endsection
