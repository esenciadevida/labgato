@extends('layouts.public')

@section('title', 'Notifications - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">Notifications</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $notifications->total() }} notification{{ $notifications->total() === 1 ? '' : 's' }} for you
                </p>
            </div>

            @if(auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-card transition-colors hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-8 space-y-3">
            @forelse($notifications as $notification)
                <div class="rounded-xl border bg-white shadow-card p-5 dark:border-slate-700 dark:bg-slate-800 {{ $notification->read_at === null ? 'border-primary-300 dark:border-primary-700' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                @if($notification->read_at === null)
                                    <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-primary-600" aria-hidden="true"></span>
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
                            @if($actionText = data_get($notification->data, 'action_text'))
                                @if($actionUrl = data_get($notification->data, 'action_url'))
                                    <a href="{{ $actionUrl }}" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400">
                                        {{ $actionText }}
                                    </a>
                                @endif
                            @endif

                            @if($notification->read_at === null)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
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
                    <p class="text-sm text-slate-500 dark:text-slate-400">You have no notifications yet.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    </div>
</section>
@endsection
