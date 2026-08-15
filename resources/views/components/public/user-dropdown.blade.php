@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 2));
    $isAdministrator = $user && $user->isAdministrator();
    $recentCount = $user ? \App\Models\RecentlyViewed::where('user_id', $user->id)->count() : 0;
@endphp

<div x-data="{
    open: false,
    init() {
        window.addEventListener('labgato-open-panel', e => {
            if (e.detail !== 'dropdown') this.open = false;
        });
    },
    toggle() {
        this.open = !this.open;
        if (this.open) window.dispatchEvent(new CustomEvent('labgato-open-panel', { detail: 'dropdown' }));
    }
}" class="relative" @keydown.escape.window="open = false">
    <button
        @click="toggle()"
        class="flex items-center gap-2 rounded-full ring-2 ring-transparent hover:ring-primary-500 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
        aria-haspopup="true"
        :aria-expanded="open"
        aria-label="User menu"
    >
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-xs font-semibold text-white">
            {{ $initials }}
        </span>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-lg shadow-pop ring-1 ring-black ring-opacity-5 dark:ring-slate-700 py-1.5 z-50"
        role="menu"
    >
        <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $user->name }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email }}</p>
        </div>

        @if($user->isMember())
            <a href="{{ route('account.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem" @click="open = false">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
        @endif

        @if($isAdministrator)
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem" @click="open = false">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Admin Portal
            </a>
        @endif

        <a href="{{ route('account.profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem" @click="open = false">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            My Profile
        </a>

        <a href="{{ route('recently-viewed.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem" @click="open = false">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Recently Viewed
            @if($recentCount > 0)
                <span class="ml-auto text-xs text-slate-400 dark:text-slate-500">{{ $recentCount }}</span>
            @endif
        </a>

        <a href="{{ route('account.messages') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem" @click="open = false">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            Messages
        </a>

        <a href="{{ route('contact.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem" @click="open = false">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            Contact Us
        </a>

        <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>

        <div class="px-4 py-2 flex items-center justify-between">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Theme</span>
            <x-theme-toggle />
        </div>

        <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>

        <form method="POST" action="{{ route('logout') }}" @click="open = false">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-danger-600 dark:text-danger-400 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" role="menuitem">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Log out
            </button>
        </form>
    </div>
</div>
