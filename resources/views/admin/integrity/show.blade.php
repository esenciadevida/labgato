@extends('layouts.admin')

@section('title', 'Integrity Review - ' . config('app.name'))

@section('header', 'Integrity Review')

@php
    $badges = [
        'normal' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
        'review' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'high_risk' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    ];

    $labels = [
        'normal' => 'Normal',
        'review' => 'Review',
        'high_risk' => 'High Risk',
    ];

    $eventStyles = [
        'tab_switch' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'window_blur' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'fullscreen_exit' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'copy_attempt' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'paste_attempt' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'cut_attempt' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'print_attempt' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'right_click_attempt' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        'session_change' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'multiple_session' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'unusual_timing' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.integrity.index') }}"
       class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-700 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
        &larr; Back to Integrity
    </a>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <x-ui.card class="!p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Attempt Summary</h2>

            <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Candidate</dt>
                    <dd class="mt-0.5 font-medium text-slate-900 dark:text-slate-100">{{ $attempt->user?->name }}</dd>
                    <dd class="text-xs text-slate-500 dark:text-slate-400">{{ $attempt->user?->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Assessment</dt>
                    <dd class="mt-0.5 font-medium text-slate-900 dark:text-slate-100">{{ $attempt->assessment?->title }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Mode / Attempt</dt>
                    <dd class="mt-0.5 capitalize text-slate-700 dark:text-slate-300">{{ $attempt->mode }} — #{{ $attempt->attempt_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Status</dt>
                    <dd class="mt-0.5 capitalize text-slate-700 dark:text-slate-300">{{ str_replace('_', ' ', $attempt->status) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Score</dt>
                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">
                        @if($attempt->score !== null)
                            {{ $attempt->score }} / {{ count($attempt->question_ids ?? []) }}
                            ({{ $attempt->percentage }}%)
                        @else
                            Not submitted
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Times</dt>
                    <dd class="mt-0.5 text-slate-700 dark:text-slate-300">
                        {{ $attempt->started_at?->format('d M Y H:i') }} &rarr; {{ $attempt->ends_at?->format('H:i') }}
                        @if($attempt->time_spent_seconds !== null)
                            ({{ floor($attempt->time_spent_seconds / 60) }} min spent)
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badges[$attempt->integrity_status] ?? '' }}">
                    {{ $labels[$attempt->integrity_status] ?? ucfirst($attempt->integrity_status) }}
                </span>

                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $events->count() }} events recorded</span>
            </div>

            <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                @if($attempt->user)
                    <a href="{{ route('admin.users.index', ['search' => $attempt->user->email]) }}"
                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                        Candidate Profile
                    </a>
                @endif
            </div>
        </x-ui.card>

        <x-ui.card class="!p-6">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Manual Override</h2>

            <form method="POST" action="{{ route('admin.integrity.status', $attempt) }}" class="mt-4 space-y-4">
                @csrf

                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700 dark:text-slate-300">Integrity status</span>

                    <select name="status"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                        @foreach($labels as $value => $label)
                            <option value="{{ $value }}" @selected($attempt->integrity_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="submit"
                        class="w-full rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                    Save Status
                </button>

                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Note: new integrity events will recalculate this status automatically.
                </p>
            </form>
        </x-ui.card>
    </div>

    <x-ui.card class="!p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Events Timeline</h2>

        @forelse($events as $event)
            <div class="flex items-start gap-4 border-b border-slate-100 py-3 last:border-b-0 dark:border-slate-800">
                <span class="whitespace-nowrap text-xs tabular-nums text-slate-400 dark:text-slate-500">
                    {{ $event->created_at->format('d M Y H:i:s') }}
                </span>

                <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $eventStyles[$event->event_type] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                    {{ str_replace('_', ' ', $event->event_type) }}
                    <span class="ml-1.5 opacity-60">+{{ $event->weight }}</span>
                </span>

                <span class="text-xs text-slate-500 dark:text-slate-400">
                    @if($event->question_number)
                        Q{{ $event->question_number }}
                    @endif
                </span>

                @if($event->metadata)
                    <code class="break-all text-xs text-slate-500 dark:text-slate-400">{{ json_encode($event->metadata) }}</code>
                @endif
            </div>
        @empty
            <p class="py-8 text-center text-sm text-slate-500 dark:text-slate-400">No integrity events recorded for this attempt.</p>
        @endforelse
    </x-ui.card>
</div>
@endsection
