@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">Notifications</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ $unreadCount }} unread of {{ $notifications->total() }} total
            </p>
        </div>

        @if($unreadCount > 0)
            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                @csrf
                <x-ui.button variant="secondary" type="submit">Mark all as read</x-ui.button>
            </form>
        @endif
    </div>

    <x-ui.card class="mt-6">
        <div class="space-y-3">
            @forelse($notifications as $notification)
            <div class="rounded-xl border bg-white p-5 shadow-card dark:border-slate-700 dark:bg-slate-800 {{ $notification->read_at === null ? 'border-primary-300 dark:border-primary-700' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            @if($notification->read_at === null)
                                <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-primary-600 dark:bg-primary-400" aria-hidden="true"></span>
                            @endif
                            <span class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">
                                @php
                                    $typeLabel = str(class_basename($notification->type))
                                        ->replace('Notification', '')
                                        ->snake()
                                        ->replace('_', ' ')
                                        ->title();
                                @endphp
                                {{ $typeLabel }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-sm text-slate-700 dark:text-slate-200">
                            {{ data_get($notification->data, 'message', 'You have a new notification.') }}
                        </p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        @php($link = \App\Support\NotificationLink::resolve($notification))
                        @if($link['href'] && $link['label'])
                            <a href="{{ $link['href'] }}" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                {{ $link['label'] }}
                            </a>
                        @endif

                        @if($notification->read_at === null)
                            <form method="POST" action="{{ route('admin.notifications.mark-read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="text-sm font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">No notifications yet.</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Contact messages and other alerts will appear here.</p>
            </div>
        @endforelse
        </div>
    </x-ui.card>

    <div class="mt-8">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
