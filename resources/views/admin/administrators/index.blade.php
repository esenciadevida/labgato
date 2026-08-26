@extends('layouts.admin')

@section('title', 'Administrators - ' . config('app.name'))

@section('header', 'Administrators')

@section('content')
@can('users.view')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.administrators.index') }}" class="flex gap-2" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search administrators..."
                aria-label="Search administrators"
            />
            <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
        </form>

        <x-ui.button href="{{ route('admin.administrators.create') }}">
            Invite Administrator
        </x-ui.button>
    </div>

    @if($pendingInvitations->isNotEmpty())
        <x-ui.card title="Pending Invitations">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Invited</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($pendingInvitations as $invitation)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $invitation->name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $invitation->email }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $invitation->role?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($invitation->isExpired())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-danger-50 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">Expired {{ $invitation->expires_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400">{{ $invitation->created_at->diffForHumans() }} · expires {{ $invitation->expires_at->diffForHumans() }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.administrators.invitations.resend', $invitation) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">Resend</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.administrators.invitations.revoke', $invitation) }}" class="inline"
                                           data-confirm="Revoke this invitation? The link will stop working immediately.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300">Revoke</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Last Login</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($administrators as $administrator)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                {{ $administrator->name }}
                                @if($administrator->isSuperAdministrator())
                                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">Super Admin</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $administrator->email }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                {{ $administrator->roles->pluck('name')->join(', ') ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($administrator->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                {{ $administrator->last_login_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                <a href="{{ route('admin.administrators.edit', $administrator) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">Edit</a>

                                <form method="POST" action="{{ route('admin.administrators.toggle-active', $administrator) }}" class="inline"
                                       data-confirm="{{ $administrator->is_active ? 'Deactivate this administrator? They will be logged out immediately.' : 'Reactivate this administrator?' }}">
                                    @csrf
                                    <button type="submit" class="ml-3 text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                                        {{ $administrator->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                @if($administrator->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.administrators.destroy', $administrator) }}" class="inline"
                                           data-confirm="Permanently delete {{ $administrator->email }}? Their account will be removed and they will be logged out immediately. This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 text-danger-600 hover:text-danger-700 dark:text-danger-400 dark:hover:text-danger-300">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8">
                                <x-ui.empty-state
                                    title="No administrators found"
                                    description="{{ $search !== '' ? 'No administrators match your search.' : 'Add your first administrator to get started.' }}"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($administrators->hasPages())
            <div class="mt-4 px-4">
                {{ $administrators->links() }}
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
