@extends('layouts.public')

@section('title', 'Exam - ' . config('app.name'))

@section('content')
<main class="mx-auto max-w-3xl px-4 py-16">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $attempt->assessment->title }}</h1>

    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
        Attempt #{{ $attempt->attempt_number }} ({{ ucfirst($attempt->mode) }})
        — {{ count($attempt->question_ids ?? []) }} questions
        @if($attempt->status === 'in_progress')
            — <span id="exam-timer" data-initial-left="{{ $remainingSeconds }}">{{ gmdate('H:i:s', $remainingSeconds) }}</span> remaining
        @endif
    </p>

    @if($attempt->status !== 'in_progress')
        <div class="mt-6 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
            Submitted. Score: {{ $attempt->score ?? '—' }} / {{ count($attempt->question_ids ?? []) }}
            @if($attempt->percentage !== null)
                ({{ $attempt->percentage }}%)
            @endif
        </div>
    @else
        <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">
            The full exam interface arrives in the next release. Your attempt is frozen and your timer is running server-side.
        </p>
    @endif
</main>
@endsection
