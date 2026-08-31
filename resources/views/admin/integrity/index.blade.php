@extends('layouts.admin')

@section('title', 'Exam Integrity - ' . config('app.name'))

@section('header', 'Exam Integrity')

@php
    $statusTabs = [
        '' => 'All',
        'normal' => 'Normal',
        'review' => 'Review',
        'high_risk' => 'High Risk',
    ];

    $filterUrl = fn ($tabStatus) => route('admin.integrity.index', array_filter([
        'search' => $search,
        'status' => $tabStatus ?: null,
    ]));

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
@endphp

@section('content')
@can('integrity.view')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-1.5">
            @foreach($statusTabs as $tabStatus => $label)
                <a href="{{ $filterUrl($tabStatus) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition {{ ($status ?? '') === $tabStatus
                       ? 'bg-blue-700 text-white dark:bg-blue-600'
                       : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ ($status ?? '') === $tabStatus ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">{{ $counts[$tabStatus] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.integrity.index') }}" class="w-full sm:w-72">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="relative">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search candidate name or email"
                       class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-4 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">

                <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </div>
        </form>
    </div>

    @forelse($groups as $group)
        @php
            $assessment = $group['assessment'];
            $hasFlags = $group['high_risk'] > 0 || $group['review'] > 0;
        @endphp
        <x-ui.card class="!p-0 overflow-hidden" x-data="{ open: {{ $hasFlags ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                    class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800/60">
                <span class="flex items-center gap-3 min-w-0">
                    <svg x-show="!open" class="h-5 w-5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <svg x-show="open" class="h-5 w-5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold text-slate-900 dark:text-slate-100">{{ $assessment?->title ?? 'Unknown assessment' }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">
                            {{ $group['total'] }} attempt(s)
                            @if($group['high_risk'] > 0)
                                &middot; <span class="font-semibold text-red-600 dark:text-red-400">{{ $group['high_risk'] }} high risk</span>
                            @endif
                            @if($group['review'] > 0)
                                &middot; <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $group['review'] }} review</span>
                            @endif
                        </span>
                    </span>
                </span>
                <span class="flex flex-shrink-0 items-center gap-2">
                    @if($hasFlags)
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            Needs attention
                        </span>
                    @endif
                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500" x-text="open ? 'Collapse' : 'Expand'"></span>
                </span>
            </button>

            <div x-show="open" x-transition.opacity>
                <div class="overflow-x-auto border-t border-slate-200 dark:border-slate-700">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-700/50 dark:text-slate-400">
                                <th class="px-4 py-3">Candidate</th>
                                <th class="px-4 py-3">Mode</th>
                                <th class="px-4 py-3">Score</th>
                                <th class="px-4 py-3">Events</th>
                                <th class="px-4 py-3">Integrity Status</th>
                                <th class="px-4 py-3">Started</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($group['attempts'] as $attempt)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $attempt->user?->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $attempt->user?->email }}</p>
                                    </td>
                                    <td class="px-4 py-3 capitalize text-slate-700 dark:text-slate-300">{{ $attempt->mode }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                        @if($attempt->score !== null)
                                            {{ $attempt->score }} / {{ count($attempt->question_ids ?? []) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">{{ $attempt->integrity_events_count }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badges[$attempt->integrity_status] ?? '' }}">
                                            {{ $labels[$attempt->integrity_status] ?? ucfirst($attempt->integrity_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $attempt->started_at?->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('admin.integrity.show', $attempt) }}"
                                           class="font-medium text-blue-700 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-ui.card>
    @empty
        <x-ui.card class="!p-0 overflow-hidden">
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $search !== '' ? 'No attempts match your search.' : 'No exam attempts to review.' }}</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $search !== '' ? 'Try adjusting your filters.' : 'Attempts appear here as soon as candidates start taking assessments, with integrity events flagged automatically.' }}</p>
            </div>
        </x-ui.card>
    @endforelse
</div>
@else
    <x-ui.card class="!p-0 overflow-hidden">
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">You do not have permission to view this page.</p>
        </div>
    </x-ui.card>
@endcan
@endsection
