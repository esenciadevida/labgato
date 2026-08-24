@extends('layouts.public')

@section('title', 'Assessments - ' . config('app.name'))

@section('content')
<section class="bg-gradient-to-r from-[#0b2545] to-blue-800 dark:from-slate-900 dark:to-blue-900">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="eyebrow text-blue-200">ASCPi Challenge</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">Assessments</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">
            Timed, proctored challenge exams built on the ASCP Board of Certification content outline.
            One official attempt per challenge — practice runs are available once your attempt is in.
        </p>
    </div>
</section>

<main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
        <div class="space-y-6">
            @forelse($assessments as $assessment)
                @php
                    $state = $states[$assessment->id] ?? null;
                    $attemptStatus = $state['status'] ?? null;
                    $attempt = $state['attempt'] ?? null;
                    $typeBadge = match ($assessment->status) {
                        'open' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'closed', 'archived' => 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                    };
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-primary-300 hover:shadow-card-hover dark:border-slate-700 dark:bg-slate-900 dark:hover:border-primary-700">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                        <span class="rounded-full px-3 py-1 font-semibold uppercase tracking-wide {{ $typeBadge }}">{{ $assessment->status }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $assessment->type }}</span>
                        <span class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $assessment->duration_minutes }} min
                        </span>
                        <span class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                            {{ $assessment->total_questions ?? $assessment->blueprintQuestionCount() }} questions
                        </span>
                    </div>

                    <h2 class="mt-3 text-lg font-bold text-slate-900 dark:text-slate-100">
                        <a href="{{ route('assessments.show', $assessment) }}" class="hover:text-primary-700 dark:hover:text-primary-300">{{ $assessment->title }}</a>
                    </h2>

                    @if($assessment->description)
                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $assessment->description }}</p>
                    @endif

                    @if($assessment->blueprints->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach($assessment->blueprints as $blueprint)
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $blueprint->category?->name ?? 'General' }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        @if($assessment->status === 'open')
                            @guest
                                <a href="{{ route('assessments.show', $assessment) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 dark:bg-blue-700 dark:hover:bg-blue-600">Take Challenge</a>
                            @else
                                @if($attemptStatus === 'in_progress')
                                    <a href="{{ route('exams.show', $attempt) }}" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">Resume Attempt</a>
                                @elseif(in_array($attemptStatus, ['submitted', 'auto_submitted'], true))
                                    <a href="{{ route('exams.result', $attempt) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 dark:bg-blue-700 dark:hover:bg-blue-600">View Result</a>
                                    @if($state['can_practice'])
                                        <form method="POST" action="{{ route('assessments.start', $assessment) }}">
                                            @csrf
                                            <input type="hidden" name="mode" value="practice">
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Start Practice Run</button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('assessments.show', $assessment) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 dark:bg-blue-700 dark:hover:bg-blue-600">Take Challenge</a>
                                @endif
                            @endguest
                        @elseif($assessment->status === 'scheduled')
                            <time data-countdown="{{ $assessment->opens_at?->toIso8601String() }}" class="text-sm font-medium text-blue-700 dark:text-blue-300">
                                Opens {{ $assessment->opens_at?->timezone(config('app.timezone'))->format('D, M j Y \a\t H:i') ?? 'soon' }}
                            </time>
                        @else
                            @if($attempt && in_array($attemptStatus, ['submitted', 'auto_submitted'], true))
                                <a href="{{ route('exams.result', $attempt) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">My Result</a>
                            @endif
                            <a href="{{ route('leaderboard.show', $assessment) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Leaderboard</a>
                            <span class="text-xs italic text-slate-400 dark:text-slate-500">Next challenge coming soon.</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-sm text-slate-500 dark:text-slate-400">No assessments have been announced yet. Check back soon.</p>
                </div>
            @endforelse
        </div>

        <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h3 class="font-display text-base font-bold text-slate-900 dark:text-slate-100">How it works</h3>
                <ul class="mt-4 space-y-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    <li class="flex gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Questions are randomized per participant — order and options included.
                    </li>
                    <li class="flex gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        One official attempt per challenge. The server clock runs the timer.
                    </li>
                    <li class="flex gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Scores stay hidden until results are officially released.
                    </li>
                    <li class="flex gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Finish strong and earn a verifiable certificate with your rank.
                    </li>
                </ul>
            </div>

            <div class="rounded-xl bg-gradient-to-br from-[#0b2545] to-blue-800 p-6 text-white dark:from-slate-900 dark:to-blue-900">
                <h3 class="font-display text-base font-bold">Practice makes perfect</h3>
                <p class="mt-2 text-sm leading-relaxed text-blue-100">
                  Once your official attempt is submitted, practice runs let you replay similar questions — unranked, untethered from the leaderboard.
                </p>
                <a href="{{ route('assessments.my-results') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-white hover:underline">
                    Check your results
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </aside>
    </div>
</main>
@endsection
