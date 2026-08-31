@extends('layouts.public')

@section('title', 'Assessments - ' . config('app.name'))

@section('content')
<section class="bg-gradient-to-r from-[#0b2545] to-blue-800 dark:from-slate-900 dark:to-blue-900">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="eyebrow text-blue-200">{{ setting('hub.eyebrow', 'ASCPi Challenge') }}</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ setting('hub.heading', 'Assessments') }}</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">
            {{ setting('hub.subtitle', 'Timed, proctored challenge exams built on the ASCP Board of Certification content outline. One official attempt per challenge - practice runs are available once your attempt is in.') }}
        </p>
    </div>
</section>

<main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if($courses->isNotEmpty())
        <div class="mb-8 space-y-4">
            <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100">Courses</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($courses as $course)
                    <a href="{{ route('courses.show', $course) }}" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-primary-300 hover:shadow-card-hover dark:border-slate-700 dark:bg-slate-900 dark:hover:border-primary-700">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Course</p>
                        <h3 class="mt-1 font-display text-base font-bold text-slate-900 group-hover:text-primary-700 dark:text-slate-100 dark:group-hover:text-primary-300">{{ $course->title }}</h3>
                        @if($course->summary)
                            <p class="mt-1 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">{{ $course->summary }}</p>
                        @endif
                        <p class="mt-3 text-xs font-medium tabular-nums text-slate-500 dark:text-slate-400">{{ $course->assessments_count }} assessment{{ $course->assessments_count === 1 ? '' : 's' }} &middot; <span class="font-semibold text-primary-700 group-hover:underline dark:text-primary-300">View course</span></p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
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

                    @if($assessment->status === 'open' && $assessment->closes_at)
                        <p class="mt-4 flex items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300">
                            <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Closes in
                            <span class="inline-flex items-center gap-1 font-mono font-bold tabular-nums text-blue-800 dark:text-blue-300"
                                  x-data="assessmentCountdown({{ $assessment->closes_at->timestamp }}, {{ (int) now()->timestamp }})"
                                  role="timer"
                                  aria-label="Time remaining until the assessment closes">
                                <template x-if="!expired">
                                    <span class="inline-flex flex-wrap items-center gap-x-1.5">
                                        <span><span x-text="days" class="tabular-nums"></span><span class="text-slate-400">d</span></span>
                                        <span><span x-text="hours" class="tabular-nums"></span><span class="text-slate-400">h</span></span>
                                        <span><span x-text="minutes" class="tabular-nums"></span><span class="text-slate-400">m</span></span>
                                        <span><span x-text="seconds" class="tabular-nums"></span><span class="text-slate-400">s</span></span>
                                    </span>
                                </template>
                                <template x-if="expired">
                                    <span class="font-medium text-amber-600 dark:text-amber-400">Closing now</span>
                                </template>
                            </span>
                        </p>
                    @endif

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        @if($assessment->status === 'open')
                            @guest
                                <a href="{{ route('assessments.show', $assessment) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 dark:bg-blue-700 dark:hover:bg-blue-600">{{ setting('hub.card_cta', 'Take Challenge') }}</a>
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
                                    <a href="{{ route('assessments.show', $assessment) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 dark:bg-blue-700 dark:hover:bg-blue-600">{{ setting('hub.card_cta', 'Take Challenge') }}</a>
                                @endif
                            @endguest
                        @elseif($assessment->status === 'scheduled')
                            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-800 dark:bg-blue-900/30">
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-800 dark:text-blue-300">
                                    @if($assessment->opens_at)
                                        Opens {{ $assessment->opens_at->timezone(config('app.timezone'))->format('D, M j Y \a\t H:i') }}
                                    @else
                                        Opens soon
                                    @endif
                                </p>

                                @if($assessment->opens_at)
                                    <p class="mt-1.5 text-xs text-blue-700 dark:text-blue-300">Opens in:</p>
                                    <div class="mt-1 flex items-center gap-1 font-mono text-sm font-bold tabular-nums text-blue-900 dark:text-blue-100"
                                         x-data="assessmentCountdown({{ $assessment->opens_at->timestamp }}, {{ (int) now()->timestamp }})"
                                         role="timer"
                                         aria-label="Time remaining until the assessment opens">
                                        <template x-if="!expired">
                                            <span class="inline-flex flex-wrap items-center gap-x-1.5">
                                                <span><span x-text="days" class="tabular-nums"></span><span class="text-blue-500 dark:text-blue-400">d</span></span>
                                                <span><span x-text="hours" class="tabular-nums"></span><span class="text-blue-500 dark:text-blue-400">h</span></span>
                                                <span><span x-text="minutes" class="tabular-nums"></span><span class="text-blue-500 dark:text-blue-400">m</span></span>
                                                <span><span x-text="seconds" class="tabular-nums"></span><span class="text-blue-500 dark:text-blue-400">s</span></span>
                                            </span>
                                        </template>
                                        <template x-if="expired">
                                            <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">Opening now</span>
                                        </template>
                                    </div>
                                @endif

                                @if($assessment->closes_at)
                                    <p class="mt-2 text-xs text-blue-700/80 dark:text-blue-300/80">Closes {{ $assessment->closes_at->timezone(config('app.timezone'))->format('D, M j Y \a\t H:i') }}</p>
                                @endif
                            </div>
                        @else
                            @if($attempt && in_array($attemptStatus, ['submitted', 'auto_submitted'], true))
                                <a href="{{ route('exams.result', $attempt) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">My Result</a>
                            @endif
                            <a href="{{ route('leaderboard.show', $assessment) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Leaderboard</a>
                            <span class="text-xs italic text-slate-400 dark:text-slate-500">{{ setting('hub.next_coming_label', 'Next challenge coming soon.') }}</span>
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
                <h3 class="font-display text-base font-bold text-slate-900 dark:text-slate-100">{{ setting('hub.how_works_title', 'How it works') }}</h3>
                <ul class="mt-4 space-y-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    @php
                        $howWorksItems = collect(explode("\n", (string) setting('hub.how_works_items', '')))
                            ->map(fn ($line) => trim($line))
                            ->filter()
                            ->values()
                            ->all();
                        if (empty($howWorksItems)) {
                            $howWorksItems = [
                                'Questions are randomized per participant — order and options included.',
                                'One official attempt per challenge. The server clock runs the timer.',
                                'Scores stay hidden until results are officially released.',
                                'Finish strong and earn a verifiable certificate with your rank.',
                            ];
                        }
                    @endphp
                    @foreach($howWorksItems as $item)
                        <li class="flex gap-2">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-xl bg-gradient-to-br from-[#0b2545] to-blue-800 p-6 text-white dark:from-slate-900 dark:to-blue-900">
                <h3 class="font-display text-base font-bold">{{ setting('hub.practice_title', 'Practice makes perfect') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-blue-100">
                  {{ setting('hub.practice_text', 'Once your official attempt is submitted, practice runs let you replay similar questions — unranked, untethered from the leaderboard.') }}
                </p>
                <a href="{{ route('assessments.my-results') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-white hover:underline">
                    {{ setting('hub.practice_link_label', 'Check your results') }}
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </aside>
    </div>
</main>
@endsection
