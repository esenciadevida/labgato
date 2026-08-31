@php
    $pageTitle = 'Launch Waiting Room';
    $metaNoindex = true;
@endphp

@extends('layouts.public')

@section('content')
<section class="py-8 sm:py-12 lg:py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="break-words font-display text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl lg:text-4xl">
                Welcome, {{ $user->profile?->display_name ?: $user->name }}
            </h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                You are registered for the Labgoto launch. Sit tight - everything unlocks the moment we go live.
            </p>
        </div>

        <div class="relative isolate mt-8 overflow-hidden rounded-3xl border border-primary-800/60 bg-gradient-to-b from-primary-950 via-primary-900 to-slate-950 p-5 shadow-panel sm:p-8 lg:p-10">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-24 left-1/2 h-72 w-[36rem] -translate-x-1/2 rounded-full bg-primary-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-48 w-48 translate-x-1/4 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute bottom-8 left-0 h-44 w-44 -translate-x-1/3 rounded-full bg-emerald-400/10 blur-3xl"></div>
            </div>

            <div class="relative">
                <div class="flex justify-center">
                    <x-launch.countdown :target="$settings->launchAtCarbon()" size="lg" reload-on-zero />
                </div>

                <div class="mt-8 w-full max-w-xl mx-auto">
                    <div class="relative isolate overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-5 text-left shadow-[0_18px_50px_-20px_rgb(0_0_0/0.6)] backdrop-blur-md sm:p-7">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/40 to-transparent" aria-hidden="true"></div>

                        <h2 class="font-display text-sm font-bold uppercase tracking-[0.2em] text-white/60">Your Challenges</h2>

                        <div class="mt-5 space-y-3">
                            @forelse($assessments as $assessment)
                                @php
                                    $state = $states[$assessment->id] ?? [];
                                    $attempt = $state['attempt'] ?? null;
                                    $attemptStatus = $attempt?->status;
                                    $canBegin = $state['canBegin'] ?? false;
                                    $cta = null;
                                    $statusLabel = 'Opens soon';
                                    if ($canBegin) {
                                        $cta = ['label' => 'Start Exam', 'href' => route('assessments.show', $assessment), 'primary' => true];
                                        $statusLabel = 'Open now';
                                    } elseif ($attemptStatus === 'in_progress') {
                                        $cta = ['label' => 'Resume', 'href' => route('exams.show', $attempt), 'primary' => true];
                                        $statusLabel = 'In progress';
                                    } elseif (in_array($attemptStatus, ['submitted', 'auto_submitted'], true)) {
                                        $cta = ['label' => 'View Result', 'href' => route('exams.result', $attempt), 'primary' => false];
                                        $statusLabel = 'Complete';
                                    }
                                @endphp
                                <div class="flex flex-col gap-3 border-b border-white/5 pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                    <div class="min-w-0">
                                        <h3 class="break-words font-display text-sm font-bold text-white">{{ $assessment->title }}</h3>
                                        <p class="mt-0.5 text-xs text-slate-400">
                                            {{ ucfirst($assessment->type) }} &middot; {{ $assessment->total_questions }} questions &middot; {{ $assessment->duration_minutes }} min &middot;
                                            <span class="{{ $canBegin ? 'text-success-300' : 'text-white/60' }}">{{ $statusLabel }}</span>
                                        </p>
                                        @if($assessment->closes_at)
                                            <p class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-amber-300/90">
                                                <svg class="h-3.5 w-3.5 shrink-0 text-amber-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>Challenge expires in</span>
                                                <span class="inline-flex items-center gap-1 font-mono font-bold tabular-nums text-white"
                                                      x-data="assessmentCountdown({{ $assessment->closes_at->timestamp }}, {{ (int) now()->timestamp }})"
                                                      role="timer"
                                                      aria-label="Time remaining until the challenge expires">
                                                    <template x-if="!expired">
                                                        <span class="inline-flex flex-wrap items-center gap-x-1.5">
                                                            <span><span x-text="days" class="tabular-nums"></span><span class="text-slate-400">d</span></span>
                                                            <span><span x-text="hours" class="tabular-nums"></span><span class="text-slate-400">h</span></span>
                                                            <span><span x-text="minutes" class="tabular-nums"></span><span class="text-slate-400">m</span></span>
                                                            <span><span x-text="seconds" class="tabular-nums"></span><span class="text-slate-400">s</span></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="expired">
                                                        <span class="font-medium text-amber-300">Expiring now</span>
                                                    </template>
                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    @if($cta)
                                        <a href="{{ $cta['href'] }}"
                                           class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-semibold transition-colors sm:w-auto sm:shrink-0 sm:py-2 {{ $cta['primary'] ? 'bg-white text-primary-900 hover:bg-slate-100' : 'border border-white/20 text-white hover:bg-white/10' }}">
                                            {{ $cta['label'] }}
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                            </svg>
                                        </a>
                                    @else
                                        <span class="shrink-0 text-xs font-medium text-white/40">Locked</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Your challenges will appear here when they open.</p>
                            @endforelse
                        </div>

                        <p class="mt-6 border-t border-white/5 pt-4 text-sm leading-relaxed text-slate-400">
                            Results and answers are revealed when Labgoto officially launches.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
