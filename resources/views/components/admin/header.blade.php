<header class="bg-white border-b border-slate-200 sticky top-0 z-30 dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3">
        <div class="flex items-center gap-3 min-w-0">
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 lg:hidden dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700"
                aria-label="Toggle navigation"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <button
                @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('sidebarCollapsed', sidebarCollapsed)"
                class="hidden lg:flex w-10 h-10 items-center justify-center rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-all duration-200 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700"
                :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <svg x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -rotate-90" x-transition:enter-end="opacity-100 rotate-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 rotate-0" x-transition:leave-end="opacity-0 -rotate-90" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <svg x-show="sidebarCollapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 rotate-90" x-transition:enter-end="opacity-100 rotate-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 rotate-0" x-transition:leave-end="opacity-0 -rotate-90" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            </button>
            <h1 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100 truncate">@yield('header', 'Dashboard')</h1>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ url('/') }}"
                target="_blank"
                rel="noopener"
                class="hidden sm:inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-700"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                View site
            </a>

            <x-theme-toggle />

            <x-notifications.panel />

            <x-public.user-dropdown />
        </div>
    </div>
</header>
