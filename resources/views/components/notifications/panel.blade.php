@php
    $user = auth()->user();
    $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
    $recent = $user ? $user->notifications()->latest()->limit(10)->get() : collect();
    $isAdmin = $user?->isAdmin() ?? false;
    $canViewMessages = $isAdmin && ($user->isSuperAdministrator() || $user->hasPermission('contact-messages.view'));
    $viewAllHref = $canViewMessages
        ? route('admin.contact-messages.index')
        : ($isAdmin ? route('admin.notifications.index') : route('notifications.index'));
    $viewAllClearUrl = $canViewMessages ? route('admin.notifications.clear') : '';
@endphp

<div x-data="{
    open: false,
    unreadCount: {{ $unreadCount }},
    init() {
        window.addEventListener('labgato-open-panel', e => {
            if (e.detail !== 'panel') this.open = false;
        });
        window.addEventListener('labgoto:notification-updated', e => {
            if (e.detail && typeof e.detail.unread_count === 'number') this.unreadCount = e.detail.unread_count;
        });
    },
    toggle() {
        this.open = !this.open;
        if (this.open) {
            window.dispatchEvent(new CustomEvent('labgato-open-panel', { detail: 'panel' }));
            this.$nextTick(() => this.$refs.panel?.focus());
        } else {
            this.$refs.button?.focus();
        }
    },
    close() {
        this.open = false;
        this.$refs.button?.focus();
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
                    const item = el.querySelector('[data-notification-item]');
                    if (item) {
                        item.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/20', 'border-l-primary-500');
                        item.classList.add('border-l-transparent');
                    }
                    const badge = el.querySelector('.bg-primary-500');
                    if (badge) badge.remove();
                    const msg = el.querySelector('p');
                    if (msg) msg.classList.remove('font-semibold');
                }
            }
        } catch (e) {}
    },
    async viewAll(clearUrl, redirectUrl) {
        if (clearUrl) {
            try {
                await fetch(clearUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                });
            } catch (e) {}
        }
        window.location.href = redirectUrl;
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
                document.querySelectorAll('[data-notification-item].border-l-primary-500').forEach(el => {
                    el.classList.remove('bg-primary-50/50', 'dark:bg-primary-900/20', 'border-l-primary-500');
                    el.classList.add('border-l-transparent');
                });
                document.querySelectorAll('[data-notification-item] p.font-semibold').forEach(el => el.classList.remove('font-semibold'));
            }
        } catch (e) {}
    }
}" class="relative" @keydown.escape.window="open = false">
    <button
        type="button"
        x-ref="button"
        @click="toggle()"
        class="relative w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700"
        aria-haspopup="dialog"
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
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 z-40"
        aria-hidden="true"
    ></div>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        @click.outside="close()"
        @keydown.escape="close()"
        x-ref="panel"
        tabindex="-1"
        class="fixed left-4 right-4 top-20 z-50 sm:absolute sm:right-0 sm:left-auto sm:top-full sm:mt-2 sm:w-[min(24rem,calc(100vw-2rem))] origin-top-right rounded-2xl outline-none sm:w-96"
        role="dialog"
        aria-modal="true"
        aria-label="Notifications panel"
    >
        <span class="absolute -top-1.5 right-6 h-3 w-3 rotate-45 rounded-[2px] border-l border-t border-black/5 bg-white dark:border-slate-700 dark:bg-slate-800" aria-hidden="true"></span>

        <div class="flex max-h-[80vh] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-slate-800 dark:ring-slate-700">
            <div class="shrink-0 border-b border-slate-200 bg-slate-50/80 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/80">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-primary-600/10 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm font-semibold leading-tight text-slate-900 dark:text-white">Notifications</h2>
                            <p x-show="unreadCount > 0" x-cloak class="mt-0.5 truncate text-xs font-medium text-danger-700 dark:text-danger-400" x-text="unreadCount + ' unread'"></p>
                            <p x-show="unreadCount === 0" x-cloak class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">All caught up</p>
                        </div>
                    </div>
                    <div class="flex flex-shrink-0 items-center gap-1.5">
                        <button
                            x-show="unreadCount > 0"
                            x-cloak
                            type="button"
                            @click="markAllAsRead()"
                            class="inline-flex items-center gap-1 rounded-lg border border-primary-200 bg-primary-50 px-2.5 py-1.5 text-xs font-medium text-primary-700 transition-colors hover:bg-primary-100 dark:border-primary-500/30 dark:bg-primary-500/10 dark:text-primary-300 dark:hover:bg-primary-500/20"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="hidden sm:inline">Mark all as read</span>
                            <span class="sm:hidden">Read all</span>
                        </button>
                        <button
                            type="button"
                            @click="close()"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
                            aria-label="Close notifications"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="scrollbar-slim min-h-0 flex-1 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-700/60">
                @forelse($recent as $notification)
                    <div id="notification-{{ $notification->id }}" data-unread-dot>
                        <x-notifications.item :notification="$notification" />
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700/60">
                            <svg class="h-6 w-6 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-medium text-slate-700 dark:text-slate-200">You're all caught up!</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">New notifications will appear here.</p>
                    </div>
                @endforelse
            </div>

            <div class="shrink-0 border-t border-slate-200 bg-slate-50/50 dark:border-slate-700 dark:bg-slate-800/50">
                <a
                    href="{{ $viewAllHref }}"
                    class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-center text-sm font-medium text-primary-700 transition-colors hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-slate-700/60"
                    @if($canViewMessages)
                        @click.prevent="viewAll('{{ $viewAllClearUrl }}', '{{ $viewAllHref }}')"
                    @else
                        @click="close()"
                    @endif
                >
                    View all notifications
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
