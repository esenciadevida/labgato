@extends('layouts.public')

@section('title', $assessment->title . ' - ' . config('app.name'))

@section('content')
<main class="mx-auto max-w-4xl px-4 py-10">
    <div class="rounded-2xl bg-gradient-to-r from-[#0b2545] to-blue-800 p-8 text-white shadow-lg dark:from-slate-900 dark:to-blue-900">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
            <span class="rounded-full bg-white/15 px-3 py-1 uppercase tracking-wide">{{ ucfirst($assessment->type) }}</span>
            <span class="rounded-full bg-white/15 px-3 py-1">{{ $assessment->duration_minutes }} minutes</span>
            <span class="rounded-full bg-white/15 px-3 py-1">{{ $actualQuestionCount }} questions</span>
        </div>

        <h1 class="mt-4 text-3xl font-bold">{{ $assessment->title }}</h1>

        @if($assessment->description)
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 dark:text-slate-300">{{ $assessment->description }}</p>
        @endif
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Examination Rules</h2>

            <ul class="mt-4 space-y-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                @if($assessment->max_attempts === 1)
                    <li class="flex gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        <span>You have <strong>one attempt only</strong>. Once you begin, your attempt is used.</span>
                    </li>
                @endif
                <li class="flex gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>The timer is controlled by our server and starts the moment you begin. The exam <strong>auto-submits at zero</strong>.</span>
                </li>
                @if($assessment->fullscreen_required)
                    <li class="flex gap-2">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L15 15M20.25 20.25v-4.5m0 4.5h-4.5m4.5 0L15 15" /></svg>
                        <span>Fullscreen mode is required; leaving fullscreen or switching tabs may be recorded.</span>
                    </li>
                @endif
                <li class="flex gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    <span>No copy, paste, printing, or external aids. Answers auto-save as you go.</span>
                </li>
            </ul>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Subject Distribution</h2>

            <div class="mt-4 flex flex-wrap gap-2">
                @forelse($blueprints as $blueprint)
                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        {{ $blueprint->category?->name ?? 'General' }}
                        <span class="font-semibold text-blue-700 dark:text-blue-400">{{ $blueprint->question_count }}</span>
                    </span>
                @empty
                    <span class="text-sm text-slate-500 dark:text-slate-400">Mixed subjects.</span>
                @endforelse
            </div>
        </section>
    </div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
        @if($canBegin)
            <form method="POST" action="{{ route('assessments.start', $assessment) }}"
                  x-data
                  @submit="document.documentElement.requestFullscreen && document.documentElement.requestFullscreen().catch(() => {})">
                @csrf
                <input type="hidden" name="mode" value="official">

                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-800 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 md:w-auto dark:bg-blue-700 dark:hover:bg-blue-600">
                    Begin Examination
                </button>

                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    By beginning, you accept the examination rules above.
                </p>
            </form>
        @else
            <p class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                {{ $blockReason }}
            </p>

            <button type="button" disabled
                    class="mt-4 inline-flex cursor-not-allowed items-center justify-center rounded-lg bg-slate-300 px-8 py-4 text-base font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-500">
                Begin Examination
            </button>
        @endif

        @if($canPractice ?? false)
            <form method="POST" action="{{ route('assessments.start', $assessment) }}" class="mt-6 border-t border-slate-200 pt-5 dark:border-slate-700">
                @csrf
                <input type="hidden" name="mode" value="practice">
                <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                    Start Practice Run
                </button>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Practice runs are not ranked and do not affect your official score.</p>
            </form>
        @endif
    </div>
</main>
@endsection
