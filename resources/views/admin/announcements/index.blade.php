@extends('layouts.admin')

@section('title', 'Announcements - ' . config('app.name'))

@section('header', 'Announcements')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">Manage the announcement banners shown to public visitors.</p>
        <x-ui.button href="{{ route('admin.announcements.create') }}">
            New Announcement
        </x-ui.button>
    </div>

    <x-ui.card>
        @if($announcements->isEmpty())
            <x-ui.empty-state
                title="No announcements yet"
                description="Create your first announcement banner to notify visitors about maintenance or updates."
            >
                <x-slot:action>
                    <x-ui.button href="{{ route('admin.announcements.create') }}">
                        New Announcement
                    </x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Message</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Schedule</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                        @foreach($announcements as $announcement)
                            <tr>
                                <td scope="row" class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $announcement->message }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="match($announcement->type) {
                                        'success' => 'success',
                                        'warning' => 'warning',
                                        'danger' => 'danger',
                                        default => 'info',
                                    }">
                                        {{ ucfirst($announcement->type) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="$announcement->is_active ? 'success' : 'default'">
                                        {{ $announcement->is_active ? 'Active' : 'Inactive' }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $announcement->starts_at?->format('M j, Y') ?? 'Any time' }}
                                    &rarr; {{ $announcement->ends_at?->format('M j, Y') ?? 'Forever' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <x-ui.button href="{{ route('admin.announcements.edit', $announcement) }}" variant="secondary" size="sm">
                                            Edit
                                        </x-ui.button>
                                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" data-confirm="Delete this announcement? This action cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" size="sm">
                                                Delete
                                            </x-ui.button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($announcements->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">
                    {{ $announcements->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@endsection
