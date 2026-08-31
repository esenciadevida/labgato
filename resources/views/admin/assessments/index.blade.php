@extends('layouts.admin')

@section('title', 'Assessments - ' . config('app.name'))

@section('header', 'Assessments')

@section('content')
@can('assessments.manage')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Run the monthly challenge cycle: create the assessment, shape its blueprint, open and close it.
        </p>
        <x-ui.button href="{{ route('admin.assessments.create') }}" variant="primary">
            New Assessment
        </x-ui.button>
    </div>

    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:bg-slate-700/50 dark:text-slate-400">
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
                            $statusMeta = match ($assessment->status) {
                                'open' => ['bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', 'Accepting attempts', 'Live'],
                                'scheduled' => ['bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'Scheduled, not yet live', 'Scheduled'],
                                'closed' => ['bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300', 'Stopped - no new attempts', 'Closed'],
                                'archived' => ['bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', 'Archived - hidden from members', 'Archived'],
                                default => ['bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300', 'Draft - not yet live', 'Draft'],
                            };

                            // Contextual lifecycle actions per status: [toStatus, label, style, confirm].
                            $transitions = match ($assessment->status) {
                                'draft' => [['open', 'Open now', 'success', "Open \"{$assessment->title}\" so members can take it? It becomes live immediately."]],
                                'scheduled' => [
                                    ['open', 'Open now', 'success', "Open \"{$assessment->title}\" for attempts now?"],
                                    ['archived', 'Archive', 'default', "Archive \"{$assessment->title}\"? Members will no longer see it."],
                                ],
                                'open' => [['closed', 'Stop attempts', 'danger', "Stop \"{$assessment->title}\" now? Members can no longer start it, but submitted results are kept."]],
                                'closed' => [
                                    ['open', 'Reopen', 'success', "Reopen \"{$assessment->title}\" for attempts?"],
                                    ['archived', 'Archive', 'default', "Archive \"{$assessment->title}\"? Members will no longer see it."],
                                ],
                                default => [['draft', 'Restore to draft', 'default', "Restore \"{$assessment->title}\" to draft?"]],
                            };

                            $deletable = ! in_array($assessment->status, ['open'], true);
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.assessments.edit', $assessment) }}" class="font-semibold text-slate-900 hover:text-primary-700 dark:text-slate-100 dark:hover:text-primary-300">
                                    {{ $assessment->title }}
                                </a>
                                <span class="mt-0.5 block text-xs capitalize text-slate-500 dark:text-slate-400">{{ $assessment->type }} &middot; {{ $assessment->total_questions }} questions &middot; {{ $assessment->duration_minutes }} min</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusMeta[0] }}">{{ $statusMeta[2] }}</span>
                                <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $statusMeta[1] }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                {{ $assessment->opens_at?->format('M j, Y H:i') ?? 'Any time' }}
                                &rarr;
                                {{ $assessment->closes_at?->format('M j, Y H:i') ?? 'No end' }}
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">{{ $assessment->participants_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">{{ $assessment->completed_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">
                                @if($assessment->avg_percentage !== null)
                                    {{ number_format((float) $assessment->avg_percentage, 1) }}%
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    @foreach($transitions as [$toStatus, $label, $btnStyle, $confirmText])
                                        <form method="POST" action="{{ route('admin.assessments.status', $assessment) }}"
                                              @if($confirmText) data-confirm="{{ $confirmText }}" @endif>
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $toStatus }}">
                                            @if($btnStyle === 'success')
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                                                    {{ $label }}
                                                </button>
                                            @elseif($btnStyle === 'danger')
                                                <button type="submit" class="rounded-lg bg-danger-600 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-danger-700">
                                                    {{ $label }}
                                                </button>
                                            @else
                                                <button type="submit" class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">
                                                    {{ $label }}
                                                </button>
                                            @endif
                                        </form>
                                    @endforeach

                                    <a href="{{ route('leaderboard.show', $assessment) }}"
                                       title="Public leaderboard"
                                       class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">
                                        Board
                                    </a>

                                    <form method="POST" action="{{ route('admin.assessments.duplicate', $assessment) }}">
                                        @csrf
                                        <button type="submit" title="Duplicate as draft"
                                                class="rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">
                                            Duplicate
                                        </button>
                                    </form>

                                    <x-ui.button href="{{ route('admin.assessments.edit', $assessment) }}" variant="secondary" size="sm" class="!px-2.5 !py-1.5 !text-xs">
                                        Edit
                                    </x-ui.button>

                                    @if($deletable)
                                        <form method="POST" action="{{ route('admin.assessments.destroy', $assessment) }}"
                                              data-confirm="{{ $assessment->participants_count > 0
                                                  ? 'This assessment has ' . $assessment->participants_count . ' attempt(s). Deleting will PERMANENTLY remove all attempts, answers, and integrity data. This cannot be undone.'
                                                  : 'Permanently delete this assessment and its blueprint? This cannot be undone.' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete"
                                                    class="rounded-lg border border-danger-200 px-2.5 py-1.5 text-xs font-medium text-danger-600 transition hover:bg-danger-50 dark:border-danger-800 dark:text-danger-400 dark:hover:bg-danger-900/30">
                                            Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No assessments yet.</p>
                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Create your first assessment to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div>{{ $assessments->links() }}</div>
</div>
@else
    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">You do not have permission to view this page.</p>
    </div>
@endcan
@endsection
