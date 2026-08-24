@extends('layouts.admin')

@section('title', 'Assessments - ' . config('app.name'))

@section('header', 'Assessments')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Run the monthly challenge cycle: create the assessment, shape its blueprint, open and close it.
        </p>
        <x-ui.button href="{{ route('admin.assessments.create') }}" variant="primary">
            New Assessment
        </x-ui.button>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Assessment</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Window</th>
                        <th class="px-4 py-3 text-right">Participants</th>
                        <th class="px-4 py-3 text-right">Completed</th>
                        <th class="px-4 py-3 text-right">Avg %</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($assessments as $assessment)
                        @php
                            $statusClasses = match ($assessment->status) {
                                'open' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'scheduled' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                'closed' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                            };
                            $nextToggle = $assessment->status === 'open' ? ['closed', 'Close now'] : ['open', 'Open now'];
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.assessments.edit', $assessment) }}" class="font-semibold text-slate-900 hover:text-primary-700 dark:text-slate-100 dark:hover:text-primary-300">
                                    {{ $assessment->title }}
                                </a>
                                <span class="mt-0.5 block text-xs capitalize text-slate-500 dark:text-slate-400">{{ $assessment->type }} &middot; {{ $assessment->total_questions }} questions &middot; {{ $assessment->duration_minutes }} min</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusClasses }}">{{ $assessment->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                {{ $assessment->opens_at?->format('M j, Y H:i') ?? 'Any time' }}
                                &rarr;
                                {{ $assessment->closes_at?->format('M j, Y H:i') ?? 'No end' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">{{ $assessment->participants_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">{{ $assessment->completed_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">
                                {{ $assessment->avg_percentage !== null ? number_format((float) $assessment->avg_percentage, 1).'%' : '&mdash;' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.assessments.status', $assessment) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $nextToggle[0] }}">
                                        <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">
                                            {{ $nextToggle[1] }}
                                        </button>
                                    </form>
                                    <x-ui.button href="{{ route('admin.assessments.edit', $assessment) }}" variant="secondary" size="sm" class="!px-2.5 !py-1.5 !text-xs">
                                        Edit
                                    </x-ui.button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No assessments yet. Create the first monthly challenge.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $assessments->links() }}</div>
</div>
@endsection
