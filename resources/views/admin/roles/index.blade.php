@extends('layouts.admin')

@section('title', 'Roles - ' . config('app.name'))

@section('header', 'Roles')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Manage roles and their permissions.
        </p>

        @can('manage', \App\Models\Role::class)
            <x-ui.button href="{{ route('admin.roles.create') }}">
                New Role
            </x-ui.button>
        @endcan
    </div>

    <x-ui.card>
        @if($roles->isEmpty())
            <x-ui.empty-state
                title="No roles found"
                description="Create your first role to organize permissions."
            >
                @can('manage', \App\Models\Role::class)
                    <x-slot:action>
                        <x-ui.button href="{{ route('admin.roles.create') }}">
                            New Role
                        </x-ui.button>
                    </x-slot:action>
                @endcan
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Slug</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Users</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($roles as $role)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $role->name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $role->slug }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $role->description ?: '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $role->users_count }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($role->is_system_role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-info-50 text-info-700 dark:bg-info-900/30 dark:text-info-400">System</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Custom</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
