@extends('layouts.admin')

@section('title', 'Courses - ' . config('app.name'))

@section('header', 'Courses')

@section('content')
@can('assessments.manage')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-slate-600 dark:text-slate-400">
            Group assessments into a learning program. Completing every required assessment earns a course certificate.
        </p>
        <x-ui.button href="{{ route('admin.courses.create') }}" variant="primary">
            New Course
        </x-ui.button>
    </div>

    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:bg-slate-700/50 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Course</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Assessments</th>
                        <th class="px-4 py-3">Certificate</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($courses as $course)
                        @php
                            $statusClasses = match ($course->status) {
                                'published' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'archived' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                default => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.courses.edit', $course) }}" class="font-semibold text-slate-900 hover:text-primary-700 dark:text-slate-100 dark:hover:text-primary-300">
                                    {{ $course->title }}
                                </a>
                                <span class="mt-0.5 block text-xs capitalize text-slate-500 dark:text-slate-400">sort {{ $course->sort_order }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold uppercase tracking-wide {{ $statusClasses }}">{{ $course->status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-700 dark:text-slate-300">{{ $course->assessments_count }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600 dark:text-slate-300">
                                @if($course->certificate_enabled)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 dark:text-emerald-300">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        {{ $course->certificate_requires_all ? 'All required' : 'Passthrough' }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500">Disabled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <x-ui.button href="{{ route('admin.courses.edit', $course) }}" variant="secondary" size="sm" class="!px-2.5 !py-1.5 !text-xs">
                                        Edit
                                    </x-ui.button>

                                    @if($course->certificates()->count() === 0)
                                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                              data-confirm="Permanently delete this course and its assessment links? This cannot be undone.">
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
                            <td colspan="5" class="px-4 py-10">
                                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No courses yet.</p>
                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Create your first course to group assessments and issue completion certificates.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div>{{ $courses->links() }}</div>
</div>
@else
    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">You do not have permission to view this page.</p>
    </div>
@endcan
@endsection