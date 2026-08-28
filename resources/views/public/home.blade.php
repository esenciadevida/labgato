@extends('layouts.public')

@section('content')
<section class="bg-primary-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
        <p class="eyebrow text-primary-300">Laboratory Toolkit</p>
        <h1 class="mt-4 font-display font-bold text-4xl sm:text-5xl tracking-tight">
            Labgoto
        </h1>
        <p class="mt-5 text-lg sm:text-xl text-white/70 max-w-2xl mx-auto leading-relaxed">
            Labgoto is a medical laboratory knowledge platform with clinical calculators, reference ranges, standard operating procedures, and lab result interpretation guides for medical laboratory scientists.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ route('public.search') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-lg bg-white text-primary-800 hover:bg-white/90 focus:outline-none focus:ring-2 focus:ring-white/50 transition-colors">
                Search Resources
            </a>
            <a href="#resources" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold rounded-lg border border-white/20 text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30 transition-colors">
                Browse Categories
            </a>
        </div>
    </div>
</section>

<section id="resources" class="py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="eyebrow">Resources</p>
            <h2 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Laboratory Resources</h2>
            <p class="mt-3 text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                Comprehensive resources designed to support your laboratory workflow and professional development.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="{{ route('public.tools.index') }}" class="group bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-primary-700">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Laboratory Tools</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm">Essential tools and utilities for daily laboratory operations.</p>
                <p class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-700 group-hover:text-primary-800">
                    Explore Tools
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </p>
            </a>

            <a href="{{ route('public.calculators.index') }}" class="group bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-primary-700">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Calculators</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm">Clinical calculators for common laboratory calculations.</p>
                <p class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-700 group-hover:text-primary-800">
                    Explore Calculators
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </p>
            </a>

            <a href="{{ route('public.reference-ranges.index') }}" class="group bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-primary-700">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Reference Ranges</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm">Comprehensive reference ranges for clinical laboratory testing.</p>
                <p class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-700 group-hover:text-primary-800">
                    Explore Reference Ranges
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </p>
            </a>

            <a href="{{ route('public.sops.index') }}" class="group bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-primary-700">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">SOP Resources</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm">Standard operating procedures and protocol documentation.</p>
                <p class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-700 group-hover:text-primary-800">
                    Explore SOPs
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </p>
            </a>

            <a href="{{ route('public.interpretation.index') }}" class="group bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-primary-700">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Result Interpretation</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm">Guidance for interpreting laboratory results and clinical significance.</p>
                <p class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-700 group-hover:text-primary-800">
                    Explore Interpretations
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </p>
            </a>

            <a href="{{ route('public.knowledge.index') }}" class="group bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                <div class="w-12 h-12 rounded-xl bg-primary-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-primary-700">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">Knowledge & Education</h3>
                <p class="text-slate-600 dark:text-slate-300 text-sm">Educational resources and continuing education materials.</p>
                <p class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary-700 group-hover:text-primary-800">
                    Explore Knowledge Hub
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </p>
            </a>
        </div>
    </div>
</section>

@if($recommended->isNotEmpty())
    <x-public.recommended :resources="$recommended" />
@endif

@if($assessmentsLive)
    <section id="assessments" class="py-16 sm:py-20 bg-white border-y border-slate-200 dark:bg-slate-800 dark:border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <p class="eyebrow">{{ setting('hub.eyebrow', 'ASCPi Challenge') }}</p>
                <h2 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ setting('hub.heading', 'Assessments') }}</h2>
                <p class="mt-3 text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
                    {{ setting('hub.home_subtitle', 'Timed challenge exams with a live leaderboard, official results, and verifiable certificates.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @if($flagship)
                    <a href="{{ route('assessments.show', $flagship) }}" class="lg:col-span-2 group relative overflow-hidden rounded-xl bg-gradient-to-br from-[#0b2545] to-blue-800 p-8 text-white shadow-card transition hover:shadow-card-hover dark:from-slate-900 dark:to-blue-900">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-300">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ setting('hub.open_now_label', 'Open now') }}
                        </span>
                        <h3 class="mt-4 font-display text-2xl font-bold">{{ $flagship->title }}</h3>
                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-blue-100">
                            @php
                                $hubMeta = str_replace(
                                    ['{questions}', '{minutes}', '{attempt}'],
                                    [$flagship->total_questions ?? $flagship->blueprintQuestionCount(), $flagship->duration_minutes, 'one attempt'],
                                    (string) setting('hub.card_meta_template', '{questions} questions · {minutes} minutes · {attempt}.')
                                );
                            @endphp
                            {{ $hubMeta }}
                        </p>
                        <p class="mt-6 inline-flex items-center gap-1 text-sm font-semibold group-hover:gap-2 transition-all">
                            {{ setting('hub.primary_cta', 'Take the challenge') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </p>
                    </a>
                @else
                    <a href="{{ route('assessments.index') }}" class="lg:col-span-2 group flex flex-col justify-center rounded-xl bg-gradient-to-br from-[#0b2545] to-blue-800 p-8 text-white shadow-card transition hover:shadow-card-hover dark:from-slate-900 dark:to-blue-900">
                        <h3 class="font-display text-2xl font-bold">{{ setting('hub.next_preparing_title', 'The next challenge is being prepared') }}</h3>
                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-blue-100">{{ setting('hub.next_preparing_text', 'Browse past assessments, leaderboards, and your certificates while you wait.') }}</p>
                        <p class="mt-6 inline-flex items-center gap-1 text-sm font-semibold group-hover:gap-2 transition-all">
                            {{ setting('hub.visit_hub_label', 'Visit the hub') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </p>
                    </a>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-1">
                    <a href="{{ route('leaderboard.index') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-primary-300 hover:shadow-card-hover dark:border-slate-600 dark:bg-slate-900 dark:hover:border-primary-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ setting('hub.leaderboard_label', 'Leaderboard') }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ setting('hub.leaderboard_text', 'See who is leading this month\'s challenge.') }}</p>
                        <p class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary-700 dark:text-primary-300">{{ setting('hub.leaderboard_link', 'View standings') }}</p>
                    </a>

                    <a href="{{ route('assessments.index') }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-primary-300 hover:shadow-card-hover dark:border-slate-600 dark:bg-slate-900 dark:hover:border-primary-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ setting('hub.practice_runs_label', 'Practice Runs') }}</h3>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">{{ setting('hub.practice_runs_text', 'Replay challenges unranked after your official attempt.') }}</p>
                        <p class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary-700 dark:text-primary-300">{{ setting('hub.practice_runs_link', 'Open the hub') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif

<section class="py-16 sm:py-20 bg-white border-y border-slate-200 dark:bg-slate-800 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="eyebrow">About</p>
        <h2 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight text-slate-900 dark:text-slate-100">About Labgoto</h2>
        <p class="mt-4 text-slate-600 dark:text-slate-300 max-w-3xl mx-auto leading-relaxed">
            Labgoto is designed to be a comprehensive resource for laboratory professionals. Whether you need quick reference ranges, clinical calculators, or standard operating procedures, Labgoto aims to provide the tools you need in one place.
        </p>
    </div>
</section>
@endsection
