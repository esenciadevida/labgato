@extends('layouts.admin')

@section('title', 'View User - ' . config('app.name'))

@section('header', 'View User')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if($user->is_active)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400">Active</span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">Inactive</span>
            @endif
            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                @csrf
                <x-ui.button type="submit" variant="secondary">
                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                </x-ui.button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Name</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $user->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Email</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $user->email }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <div class="flex items-center justify-end gap-3">
                <x-ui.button href="{{ route('admin.users.index') }}" variant="ghost">
                    Back
                </x-ui.button>
            </div>
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Account</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Joined</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $user->created_at?->format('M j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Last login</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Contributions</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">SOPs</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $user->sops()->count() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Knowledge articles</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $knowledgeCount }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">Interpretation guides</dt>
                        <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $interpretationCount }}</dd>
                    </div>
                </dl>

                @if($contributions->isNotEmpty())
                    <div class="mt-4 border-t border-slate-200 dark:border-slate-700 pt-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Recent SOP contributions</h4>
                        <ul class="space-y-2">
                            @foreach($contributions as $contribution)
                                <li class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate text-slate-700 dark:text-slate-300">{{ $contribution->name }}</span>
                                    <span class="shrink-0 text-xs text-slate-500 dark:text-slate-400">{{ $contribution->status instanceof \App\Enums\ResourceStatus ? $contribution->status->label() : $contribution->status }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
