@extends('layouts.public')

@section('title', $assessment->title.' Leaderboard')

@section('content')
<section class="py-10">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <a href="{{ route('leaderboard.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            All leaderboards
        </a>

        <header class="mt-4">
            <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">Leaderboard</h1>
            <p class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $assessment->title }}</span>
                @if(! $locked)
                    <span class="text-slate-400 dark:text-slate-500">&middot;</span>
                    <span>{{ number_format($totalParticipants) }} {{ Str::plural('participant', $totalParticipants) }}</span>
                @endif
            </p>
        </header>

        @if($locked)
            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <svg class="mx-auto h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <p class="mt-3 font-medium text-slate-700 dark:text-slate-200">Leaderboard will be revealed when LabGOTO launches.</p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your result is already recorded. Everyone starts at the same moment.</p>
            </div>
        @else
            @if($myPosition !== null && $myRow !== null)
                @php
                    $topPercent = max(1, (int) round($myPosition / max(1, $totalParticipants) * 100));
                    $myScore = $myRow['score'];
                    $myPercent = $myRow['percent'];
                @endphp
                <div class="mt-6 rounded-xl border border-primary-200 bg-gradient-to-r from-primary-50 to-white p-5 shadow-sm dark:border-primary-800 dark:from-primary-900/30 dark:to-slate-900">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="font-display text-4xl font-bold text-primary-700 dark:text-primary-300">#{{ $myPosition }}</span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Your position</p>
                                <p class="text-sm text-slate-700 dark:text-slate-200">{{ number_format($myScore) }} correct &middot; {{ number_format($myPercent, 1) }}% &middot; Top {{ $topPercent }}%</p>
                            </div>
                        </div>

                        @if(! $onPage)
                            <a href="{{ url('/leaderboard/'.$assessment->slug.'?page='.$jumpPage) }}#me" class="inline-flex min-h-[44px] items-center gap-1.5 rounded-lg border border-primary-300 bg-white px-4 py-2 text-sm font-semibold text-primary-700 hover:bg-primary-50 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-primary-700 dark:bg-slate-900 dark:text-primary-300 dark:hover:bg-primary-900/40">
                                Jump to my position
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <x-public.leaderboard.partials.board-table :rows="$rows" :variant="$variant" />
            </div>

            @if($paginated !== null && $paginated->hasPages())
                <div class="mt-6">
                    {{ $paginated->links() }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
