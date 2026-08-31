@extends('layouts.admin')

@section('title', 'Edit ' . $assessment->title . ' - ' . config('app.name'))

@section('header', 'Edit Assessment')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.assessments.index') }}" variant="ghost" size="sm">
        &larr; Back to assessments
    </x-ui.button>
</div>

@php
    $statusMeta = match ($assessment->status) {
        'open' => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', 'Live - accepting attempts'],
        'scheduled' => ['bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'Scheduled, will open automatically at the window start'],
        'closed' => ['bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300', 'Stopped - new attempts are blocked, results are kept'],
        'archived' => ['bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', 'Archived - hidden from members'],
        default => ['bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', 'Draft - not live yet'],
    };
@endphp

<div class="mx-auto max-w-4xl">
    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-700 dark:bg-slate-800">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Assessment</p>
                <h2 class="mt-1 font-display text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $assessment->title }}</h2>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusMeta[0] }}">{{ strtoupper($assessment->status) }}</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">{{ $statusMeta[1] }}</span>
                </div>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                @if(in_array($assessment->status, ['draft', 'scheduled', 'closed'], true))
                    <form method="POST" action="{{ route('admin.assessments.status', $assessment) }}"
                          data-confirm="Open &quot;{{ $assessment->title }}&quot; for attempts now? Members can start it as long as it is within the open window.">
                        @csrf
                        <input type="hidden" name="status" value="open">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Open for attempts
                        </button>
                    </form>
                @endif

                @if(in_array($assessment->status, ['open'], true))
                    <form method="POST" action="{{ route('admin.assessments.status', $assessment) }}"
                          data-confirm="Stop &quot;{{ $assessment->title }}&quot; now? Members can no longer start it, but submitted results are kept.">
                        @csrf
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-danger-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                            Stop attempts
                        </button>
                    </form>
                @endif

                @if($assessment->status === 'archived')
                    <form method="POST" action="{{ route('admin.assessments.status', $assessment) }}"
                          data-confirm="Restore &quot;{{ $assessment->title }}&quot; to draft? It will be hidden from members until reopened.">
                        @csrf
                        <input type="hidden" name="status" value="draft">
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                            Restore to draft
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @include('admin.assessments.partials.form', [
        'assessment' => $assessment,
        'categories' => $categories,
        'action' => route('admin.assessments.update', $assessment),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
</div>
@endsection
