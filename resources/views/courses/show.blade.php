@extends('layouts.public')

@section('title', $course->title . ' - ' . config('app.name'))

@section('content')
<section class="bg-gradient-to-r from-[#0b2545] to-blue-800 dark:from-slate-900 dark:to-blue-900">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="eyebrow text-blue-200">{{ $course->isPublished() ? 'Open Course' : ucfirst($course->status) }}</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $course->title }}</h1>
        @if($course->summary)
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">{{ $course->summary }}</p>
        @endif
    </div>
</section>

<main class="mx-auto max-w-4xl space-y-6 px-4 py-12 sm:px-6 lg:px-8">
    @if($course->description)
        <div class="prose prose-slate max-w-none dark:prose-invert">{{ $course->description }}</div>
    @endif

    @php
        $required = $course->assessments->filter(fn ($a) => $a->pivot->required);
        $optionalCount = $course->assessments->count() - $required->count();
        $requiredPassed = $required->reduce(fn ($carry, $a) => $carry + ($state[$a->id]['passed'] ?? false ? 1 : 0), 0);
        $progress = $required->count() > 0
            ? (int) round(($requiredPassed / $required->count()) * 100)
            : 0;
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-3 p-6">
            <div>
                <h2 class="font-display text-base font-bold text-slate-900 dark:text-slate-100">Course Progress</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $requiredPassed }} of {{ $required->count() }} required assessment{{ $required->count() === 1 ? '' : 's' }} passed
                    @if($optionalCount > 0)
                        <span class="ml-1">(+{{ $optionalCount }} optional)</span>
                    @endif
                </p>
            </div>
            <span class="font-display text-2xl font-bold tabular-nums text-blue-800 dark:text-blue-300">{{ $progress }}%</span>
        </div>
        <div class="h-2 bg-slate-100 dark:bg-slate-800">
            <div class="h-full bg-gradient-to-r from-blue-800 to-blue-600 transition-all duration-500" style="width: {{ $progress }}%"></div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <h2 class="font-display text-base font-bold text-slate-900 dark:text-slate-100">Assessments</h2>
        <ul class="mt-4 space-y-3">
            @foreach($course->assessments as $assessment)
                @php
                    $s = $state[$assessment->id] ?? ['status' => null, 'passed' => false, 'attempt' => null];
                    $required = (bool) $assessment->pivot->required;
                    [$label, $icon] = match (true) {
                        $s['status'] === 'in_progress' => [$required ? 'In progress' : 'In progress (optional)', 'amber'],
                        in_array($s['status'], ['submitted', 'auto_submitted'], true) && $s['passed'] => [$required ? 'Passed' : 'Completed (optional)', 'emerald'],
                        in_array($s['status'], ['submitted', 'auto_submitted'], true) => [$required ? 'Not passed' : 'Submitted (optional)', 'rose'],
                        $s['status'] !== null => [ucfirst(str_replace('_', ' ', $s['status'])), 'slate'],
                        default => [($required ? 'Not started' : 'Optional'), 'slate'],
                    };
                    $badgeClasses = match ($icon) {
                        'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                        'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        'rose' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                        default => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                    };
                @endphp
                <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-100 p-4 dark:border-slate-800">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('assessments.show', $assessment) }}" class="font-semibold text-slate-900 hover:text-primary-700 dark:text-slate-100 dark:hover:text-primary-300">{{ $assessment->title }}</a>
                            @if(!$required)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">Optional</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $assessment->duration_minutes }} min &middot; {{ $assessment->total_questions ?? $assessment->blueprintQuestionCount() }} questions &middot; Pass {{ $assessment->pass_percentage ?? 0 }}%</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeClasses }}">{{ $label }}</span>
                        @if($s['attempt'] && in_array($s['status'], ['submitted', 'auto_submitted'], true))
                            <a href="{{ route('exams.result', $s['attempt']) }}" class="text-sm font-semibold text-primary-700 hover:underline dark:text-primary-300">Result</a>
                        @elseif($s['status'] === 'in_progress')
                            <a href="{{ route('exams.show', $s['attempt']) }}" class="text-sm font-semibold text-amber-600 hover:underline dark:text-amber-400">Resume</a>
                        @else
                            <a href="{{ route('assessments.show', $assessment) }}" class="text-sm font-semibold text-primary-700 hover:underline dark:text-primary-300">Start</a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    @if($course->certificate_enabled)
        <div class="rounded-xl border p-6 shadow-sm {{ $complete ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40' : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900' }}">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-base font-bold {{ $complete ? 'text-emerald-900 dark:text-emerald-300' : 'text-slate-900 dark:text-slate-100' }}">Course Certificate</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        @if($complete)
                            Congratulations — you completed every required assessment. Your verifiable course certificate is ready.
                        @else
                            Pass every required assessment in this course to earn a verifiable course completion certificate.
                        @endif
                    </p>
                </div>
                @if($complete)
                    <a href="{{ route('courses.certificate-download', $course) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        Download Certificate
                    </a>
                @endif
            </div>
        </div>
    @endif
</main>
@endsection