@extends('layouts.admin')

@section('title', 'Audit Logs - ' . config('app.name'))

@section('header', 'Audit Logs')

@section('content')
@can('audit-logs.view')
<div class="space-y-6">

    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-col gap-3 sm:flex-row" role="search">
        <x-ui.input
            type="search"
            name="search"
            value="{{ $search }}"
            placeholder="Search logs..."
            aria-label="Search audit logs"
        />
        <x-ui.select name="action" aria-label="Filter by action">
            <option value="">All Actions</option>
            @foreach($actions as $act)
                <option value="{{ $act }}" @selected($action === $act)>{{ \App\Models\AuditLog::formatAction($act) }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select name="resource_type" aria-label="Filter by resource type">
            <option value="">All Resources</option>
            @foreach($resourceTypes as $type)
                <option value="{{ $type }}" @selected($resourceType === $type)>{{ $type }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.input
            type="date"
            name="from"
            value="{{ $from }}"
            aria-label="From date"
        />
        <x-ui.input
            type="date"
            name="to"
            value="{{ $to }}"
            aria-label="To date"
        />
        <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
    </form>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Resource</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                                {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                {{ $log->resource_type ? class_basename($log->resource_type) . ' #' . $log->resource_id : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <a href="{{ route('admin.audit-logs.show', $log) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8">
                                <x-ui.empty-state
                                    title="No audit logs found"
                                    description="{{ $search !== '' || $action !== '' || $resourceType !== '' ? 'No logs match your filters.' : 'No audit logs recorded yet.' }}"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 px-4">
                {{ $logs->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
@else
    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">You do not have permission to view this page.</p>
    </div>
@endcan
@endsection
