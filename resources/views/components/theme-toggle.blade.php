@props(['class' => ''])

<div
    x-data="{ isDark: document.documentElement.classList.contains('dark') }"
    class="shrink-0 {{ $class }}"
>
    <button
        type="button"
        @click="
            isDark = !isDark;
            document.documentElement.classList.toggle('dark', isDark);
            localStorage.setItem('labgoto-theme', isDark ? 'dark' : 'light');
        "
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :aria-pressed="isDark.toString()"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        class="w-10 h-10 relative flex items-center justify-center rounded-full text-slate-500 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-all duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] active:scale-95 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700"
    >
        <svg x-show="!isDark" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-75 rotate-90" x-transition:enter-end="opacity-100 scale-100 rotate-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 rotate-0" x-transition:leave-end="opacity-0 scale-75 -rotate-90" class="w-5 h-5 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364-.707-.707M6.343 6.343l-.707-.707m12.728 0-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg x-show="isDark" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-75 -rotate-90" x-transition:enter-end="opacity-100 scale-100 rotate-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 rotate-0" x-transition:leave-end="opacity-0 scale-75 rotate-90" class="w-5 h-5 absolute" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>
</div>
