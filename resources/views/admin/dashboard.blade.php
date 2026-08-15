@extends('layouts.admin')

@section('title', 'Dashboard - ' . config('app.name'))

@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Welcome back, {{ Auth::user()->name }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Here is an overview of your system.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-lg bg-primary-50 dark:bg-primary-900/30">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Administrators</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $adminCount }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-lg bg-success-50 dark:bg-success-900/30">
                    <svg class="w-6 h-6 text-success-600 dark:text-success-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Roles</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $roleCount }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 rounded-lg bg-info-50 dark:bg-info-900/30">
                    <svg class="w-6 h-6 text-info-600 dark:text-info-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Permissions</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $permissionCount }}</p>
                </div>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card title="Recent Activity">
        @if($recentActivity->isEmpty())
            <x-ui.empty-state
                title="No recent activity"
                description="No recent activity recorded yet."
            />
        @else
            <div class="flow-root">
                <ul class="-mb-8 divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($recentActivity as $log)
                        <li class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center ring-8 ring-white dark:ring-slate-800">
                                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1">
                                    <div>
                                        <p class="text-sm text-slate-500 dark:text-slate-400">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">{{ $log->action ?? 'Unknown action' }}</span>
                                            on <span class="font-medium text-slate-900 dark:text-slate-100">{{ $log->resource_type ?? 'resource' }}</span>
                                        </p>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-xs text-slate-500 dark:text-slate-400">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
