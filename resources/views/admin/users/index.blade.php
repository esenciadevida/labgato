@extends('layouts.admin')

@section('title', 'Users - ' . config('app.name'))

@section('header', 'Users')

@section('content')
@can('users.view')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search users..."
                aria-label="Search users"
            />
            <x-ui.select name="status" aria-label="Filter by status">
                <option value="">All Status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </x-ui.select>
            <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
        </form>
    </div>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Joined</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Contributions</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Last Login</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                {{ $user->created_at?->format('M j, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                {{ $user->contributions_count }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition-colors hover:border-primary-300 hover:bg-primary-50 hover:text-primary-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-slate-700 dark:text-slate-400 dark:hover:border-primary-500/50 dark:hover:bg-primary-500/10 dark:hover:text-primary-400"
                                        title="View"
                                        aria-label="View {{ $user->name }}"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline"
                                           data-confirm="{{ $user->is_active ? 'Deactivate this user? They will be logged out immediately and cannot log in until reactivated.' : 'Reactivate this user?' }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-500 transition-colors hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-slate-700 dark:text-slate-400 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-white"
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                            aria-label="{{ $user->is_active ? 'Deactivate' : 'Activate' }} {{ $user->name }}"
                                        >
                                            @if($user->is_active)
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    @if(Auth::user()->isSuperAdministrator())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                               data-confirm="Permanently delete this user? This will remove their account and all associated data. This cannot be undone.">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-danger-600 transition-colors hover:border-danger-300 hover:bg-danger-50 hover:text-danger-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-danger-500 dark:border-slate-700 dark:text-danger-400 dark:hover:border-danger-500/50 dark:hover:bg-danger-500/10 dark:hover:text-danger-300"
                                                title="Delete"
                                                aria-label="Delete {{ $user->name }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8">
                                <x-ui.empty-state
                                    title="No users found"
                                    description="{{ $search !== '' || $status !== '' ? 'No users match your filters.' : 'Public users will appear here when they register.' }}"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-4 px-4">
                {{ $users->links() }}
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
