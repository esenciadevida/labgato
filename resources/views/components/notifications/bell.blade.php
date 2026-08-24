@props(['recentLimit' => 10])

@auth
    @php
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()->latest()->limit($recentLimit)->get();
    @endphp

    <div x-data="{ open: false }" class="relative">
        <button
            type="button"
            @click="open = !open"
            @keydown.escape="open = false"
            class="relative w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700"
            aria-haspopup="true"
            :aria-expanded="open"
            aria-label="Notifications"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @if($unreadCount > 0)
                <span class="absolute top-0.5 right-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-600 px-1 text-[10px] font-bold leading-none text-white" aria-label="{{ $unreadCount }} unread notifications">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </button>

        <div
            x-cloak
            x-show="open"
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-lg shadow-pop ring-1 ring-black ring-opacity-5 z-50 dark:bg-slate-800 dark:ring-slate-700"
            role="menu"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notifications</span>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-xs font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            <div class="max-h-80 overflow-y-auto">
                @forelse($recent as $notification)
                    <div class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 {{ $notification->read_at === null ? 'bg-primary-50/50 dark:bg-primary-900/20' : '' }}">
                        <p class="text-sm text-slate-700 dark:text-slate-200">
                            {{ data_get($notification->data, 'message', 'You have a new notification.') }}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No notifications yet.</p>
                @endforelse
            </div>

            <a
                href="{{ auth()->user()?->isAdmin() ? route('admin.notifications.index') : route('notifications.index') }}"
                role="menuitem"
                class="block border-t border-slate-100 px-4 py-2.5 text-center text-sm font-medium text-primary-700 hover:bg-slate-50 dark:border-slate-700 dark:text-primary-400 dark:hover:bg-slate-700"
            >View all notifications</a>
        </div>
    </div>
@endauth
