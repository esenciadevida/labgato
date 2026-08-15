@props(['popups' => null])

@php
    $popups = $popups ?? collect();
    $popup = $popups->first(fn ($p) => $p->is_active
        && (trim((string) $p->title) !== '' || trim((string) $p->content) !== ''));
    $onAuthPage = request()->is('login', 'register', 'password/*', 'auth/*');
@endphp

@if($popup && ! $onAuthPage)
<div
    x-data="{
        open: false,
        key: 'labgato-popup-dismissed-{{ $popup->id }}',
        init() {
            try {
                if (localStorage.getItem(this.key) === '1') return;
            } catch (e) {}
            {{ $popup->trigger_type === 'after_delay'
                ? 'setTimeout(() => this.open = true, '.((int) $popup->delay_seconds * 1000).');'
                : 'this.open = true;' }}
        },
        close() {
            this.open = false;
            try {
                localStorage.setItem(this.key, '1');
            } catch (e) {}
        }
    }"
    x-show="open"
    x-cloak
    @keydown.escape.window="close()"
    class="fixed inset-0 z-[90] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $popup->title ?: 'Announcement' }}"
>
    {{-- Overlay (click to close) --}}
    <div
        x-show="open"
        @click="close()"
        class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm"
    ></div>

    {{-- Card --}}
    <div
        x-show="open"
        class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900"
    >
        {{-- Close button (always visible + functional) --}}
        <button
            @click="close()"
            type="button"
            aria-label="Close"
            class="absolute right-3 top-3 rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        @if(filled($popup->title))
            <h2 class="pr-8 text-lg font-semibold text-slate-900 dark:text-white">{{ $popup->title }}</h2>
        @endif

        @if(filled($popup->content))
            <div class="mt-2 whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $popup->content }}</div>
        @endif

        @if(filled($popup->cta_text) && filled($popup->cta_url))
            <a
                href="{{ $popup->cta_url }}"
                class="mt-4 inline-block rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700"
            >
                {{ $popup->cta_text }}
            </a>
        @endif
    </div>
</div>
@endif
