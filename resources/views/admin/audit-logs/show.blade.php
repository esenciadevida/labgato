@extends('layouts.admin')

@section('title', 'Audit Log #' . $log->id . ' - ' . config('app.name'))

@section('header', 'Audit Log #' . $log->id)

@section('content')
<div class="mx-auto max-w-3xl">
    <x-ui.card title="Log Details">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">ID</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->id }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Timestamp</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->created_at->format('Y-m-d H:i:s T') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">User</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->user->name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Action</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->action }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Resource Type</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->resource_type ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Resource ID</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->resource_id ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">IP Address</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $log->ip_address ?: '—' }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">User Agent</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100 break-all">{{ $log->user_agent ?: '—' }}</dd>
            </div>
        </dl>
    </x-ui.card>

    @if($log->changes)
        <x-ui.card title="Changes" class="mt-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Field</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Old Value</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">New Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($log->changes as $field => $values)
                            @php
                                $old = is_array($values) ? ($values['old'] ?? $values[0] ?? '—') : $values;
                                $new = is_array($values) ? ($values['new'] ?? $values[1] ?? '—') : $values;
                                $isSensitive = in_array($field, ['password', 'password_confirmation', 'current_password', 'new_password', 'remember_token', 'token', 'api_key', 'secret'], true);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $field }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300">{{ $isSensitive ? '***' : $old }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600 dark:text-slate-300">{{ $isSensitive ? '***' : $new }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

    <div class="mt-6">
        <x-ui.button href="{{ route('admin.audit-logs.index') }}" variant="ghost">
            Back to Audit Logs
        </x-ui.button>
    </div>
</div>
@endsection
