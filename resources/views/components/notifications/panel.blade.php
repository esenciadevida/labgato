@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $recent = $user ? $user->notifications()->latest()->limit(10)->get() : collect();
@endphp

<div x-data="{
    open: false,
    unreadCount: {{ $unreadCount }},
    init() {
        window.addEventListener('labgato-open-panel', e => {
            if (e.detail !== 'panel') this.open = false;
        });
    },
    toggle() {
        this.open = !this.open;
        if (this.open) window.dispatchEvent(new CustomEvent('labgato-open-panel', { detail: 'panel' }));
    },
    async markAsRead(id, event) {
        if (event) event.stopPropagation();
        try {
            const res = await fetch(`{{ url('notifications') }}/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) {
                this.unreadCount = data.unread_count;
                const el = document.getElementById('notification-' + id);
                if (el) {
                    el.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/20');
                    const badge = el.querySelector('.bg-primary-500');
                    if (badge) badge.remove();
                    const msg = el.querySelector('p');
                    if (msg) msg.classList.remove('font-semibold');
                }
            }
        } catch (e) {}
    },
    async markAllAsRead() {
        try {
            const res = await fetch(`{{ url('notifications/read-all') }}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) {
                this.unreadCount = 0;
                document.querySelectorAll('[data-unread-badge]').forEach(el => el.remove());
                document.querySelectorAll('[data-unread-dot]').forEach(el => el.remove());
            }
        } catch (e) {}
    }
}" class="relative" @keydown.escape.window="open = false">
    <button
        type="button"
        @click="toggle()"
        class="relative w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700"
        aria-haspopup="true"
        :aria-expanded="open"
        aria-label="Notifications"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span x-show="unreadCount > 0" x-cloak data-unread-badge class="absolute top-0.5 right-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-danger-600 px-1 text-[10px] font-bold leading-none text-white" aria-label="{{ $unreadCount }} unread notifications" :aria-label="unreadCount + ' unread notifications'">
            <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
        </span>
    </button>

    <div
        x-cloak
        x-show="open"
        @click="open = false"
        class="fixed inset-0 z-40 bg-slate-900/40 sm:hidden"
        aria-hidden="true"
    ></div>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95"
        @click.outside="open = false"
        class="fixed inset-x-0 bottom-0 z-50 sm:absolute sm:inset-x-auto sm:right-0 sm:bottom-auto sm:mt-2 sm:w-96 flex max-h-[85vh] sm:max-h-none flex-col bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-lg shadow-pop ring-1 ring-black ring-opacity-5 dark:ring-slate-700 overflow-hidden sm:max-w-[calc(100vw-2rem)]"
        role="dialog"
        aria-label="Notifications panel"
    >
        <div class="sm:hidden flex justify-center pt-2.5">
            <span class="h-1.5 w-10 rounded-full bg-slate-300 dark:bg-slate-600" aria-hidden="true"></span>
        </div>

        <div class="shrink-0 flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700">
            <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notifications</span>
            <div class="flex items-center gap-3">
                <button
                    x-show="unreadCount > 0"
                    x-cloak
                    type="button"
                    @click="markAllAsRead()"
                    class="text-xs font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300"
                >
                    Mark all as read
                </button>
                <button
                    type="button"
                    @click="open = false"
                    class="sm:hidden inline-flex items-center justify-center rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                    aria-label="Close notifications"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto sm:max-h-96">
            @forelse($recent as $notification)
                <div id="notification-{{ $notification->id }}" data-unread-dot class="{{ $notification->read_at === null ? 'bg-primary-50/50 dark:bg-primary-900/20' : '' }}">
                    <x-notifications.item :notification="$notification" />
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">You're all caught up!</p>
                </div>
            @endforelse
        </div>

        <div class="shrink-0 border-t border-slate-200 dark:border-slate-700">
            <a
                href="{{ route('notifications.index') }}"
                class="block px-4 py-2.5 text-center text-sm font-medium text-primary-700 hover:bg-slate-50 dark:text-primary-400 dark:hover:bg-slate-700 transition-colors"
                @click="open = false"
            >
                View all notifications
            </a>
        </div>
    </div>
</div>
