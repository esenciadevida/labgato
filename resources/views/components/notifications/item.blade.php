@props(['notification'])

@php
    $type = data_get($notification->data, 'type', 'default');
    $message = data_get($notification->data, 'message', 'You have a new notification.');
    $url = data_get($notification->data, 'url');
    $isUnread = $notification->read_at === null;
    $icon = match ($type) {
        'contribution_approved' => [
            'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'text-success-600 bg-success-50 dark:text-success-400 dark:bg-success-900/30',
        ],
        'contribution_rejected' => [
            'path' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'text-danger-600 bg-danger-50 dark:text-danger-400 dark:bg-danger-900/30',
        ],
        'contact_message' => [
            'path' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'color' => 'text-info-600 bg-info-50 dark:text-info-400 dark:bg-info-900/30',
        ],
        'member_registered' => [
            'path' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'color' => 'text-primary-600 bg-primary-50 dark:text-primary-400 dark:bg-primary-900/30',
        ],
        default => [
            'path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
            'color' => 'text-slate-600 bg-slate-100 dark:text-slate-400 dark:bg-slate-700',
        ],
    };
    $accent = $isUnread ? 'border-l-2 border-l-primary-500' : 'border-l-2 border-l-transparent';
@endphp

@if($url)
    <a
        href="{{ $url }}"
        data-notification-item
        class="group flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/40 {{ $accent }} {{ $isUnread ? 'bg-primary-50/50 dark:bg-primary-900/20' : '' }}"
        x-data="{
            read: {{ $isUnread ? 'false' : 'true' }},
            async markRead() {
                if (read) return;
                try {
                    const res = await fetch('{{ route('notifications.read', $notification) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        read = true;
                        window.dispatchEvent(new CustomEvent('labgoto:notification-updated', { detail: { unread_count: data.unread_count } }));
                    }
                } catch (e) {}
            }
        }"
        @click="markRead()"
    >
@else
    <div
        data-notification-item
        class="group flex items-start gap-3 px-4 py-3 transition-colors hover:bg-slate-50 dark:hover:bg-slate-700/40 {{ $accent }} {{ $isUnread ? 'bg-primary-50/50 dark:bg-primary-900/20' : '' }}"
        x-data="{
            read: {{ $isUnread ? 'false' : 'true' }},
            async markRead() {
                if (read) return;
                try {
                    const res = await fetch('{{ route('notifications.read', $notification) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        read = true;
                        window.dispatchEvent(new CustomEvent('labgoto:notification-updated', { detail: { unread_count: data.unread_count } }));
                    }
                } catch (e) {}
            }
        }"
        @click="markRead()"
    >
@endif

    <div class="flex-shrink-0 mt-0.5 transition-transform group-hover:scale-105">
        <div class="h-8 w-8 rounded-full flex items-center justify-center {{ $icon['color'] }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon['path'] }}"/>
            </svg>
        </div>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm leading-snug text-slate-700 dark:text-slate-200 {{ $isUnread ? 'font-semibold' : '' }}">
            {{ $message }}
        </p>
        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">
            {{ $notification->created_at->diffForHumans() }}
        </p>
    </div>
    @if($isUnread)
        <span class="flex-shrink-0 h-2 w-2 rounded-full bg-primary-500 mt-1.5" aria-label="Unread"></span>
    @endif

@if($url)
    </a>
@else
    </div>
@endif
