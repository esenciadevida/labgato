@extends('layouts.public')

@section('title', 'My Results - ' . config('app.name'))

@section('content')
<main class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">My Results</h1>

    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
        Your examination attempts. Results unlock when officially released.
    </p>

    @if($rows->isEmpty())
        <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="mt-4 text-sm font-medium text-slate-700 dark:text-slate-300">You haven't taken any assessments yet</p>
        </div>
    @else
        <div class="mt-8 space-y-3">
            @foreach($rows as $row)
                @php
                    $attempt = $row->attempt;
                @endphp
                <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-900">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-semibold text-slate-900 dark:text-slate-100">{{ $attempt->assessment?->title }}</h2>
                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium capitalize text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">{{ $attempt->mode }}</span>
                        </div>

                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Submitted {{ $attempt->submitted_at?->format('d M Y, H:i') }}
                            &middot; {{ str_replace('_', ' ', $attempt->status) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        @if($row->released && $attempt->score !== null)
                            <span class="text-lg font-bold tabular-nums text-slate-900 dark:text-slate-100">
                                {{ $attempt->score }}/{{ count($attempt->question_ids ?? []) }}
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">({{ $attempt->percentage }}%)</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                Locked
                            </span>
                        @endif

                        @if($row->released)
                            <a href="{{ route('exams.result', $attempt) }}"
                               class="inline-flex items-center justify-center rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-900 dark:bg-blue-700 dark:hover:bg-blue-600">
                                View
                            </a>
                        @else
                            <span class="inline-flex cursor-not-allowed items-center justify-center rounded-lg bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                View
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>
@endsection
