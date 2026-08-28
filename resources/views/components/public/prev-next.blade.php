@props([
    'previous' => null,
    'next' => null,
    'previousUrl' => null,
    'nextUrl' => null,
])

@if($previous || $next)
<nav aria-label="Article navigation" class="mt-10 border-t border-slate-200 pt-6 dark:border-slate-700">
    <div class="grid gap-4 sm:grid-cols-2">
        @if($previous)
            <a
                href="{{ $previousUrl }}"
                class="group rounded-lg border border-slate-200 p-4 transition-colors hover:border-primary-400 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-500 dark:hover:bg-primary-900/20"
            >
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Previous
                </span>
                <span class="mt-1 block font-medium leading-snug text-slate-900 line-clamp-2 group-hover:text-primary-700 dark:text-slate-100 dark:group-hover:text-primary-300">
                    {{ $previous->name }}
                </span>
            </a>
        @else
            <span aria-hidden="true" class="hidden sm:block"></span>
        @endif

        @if($next)
            <a
                href="{{ $nextUrl }}"
                class="group rounded-lg border border-slate-200 p-4 text-right transition-colors hover:border-primary-400 hover:bg-primary-50/50 dark:border-slate-700 dark:hover:border-primary-500 dark:hover:bg-primary-900/20"
            >
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Next
                </span>
                <span class="mt-1 block font-medium leading-snug text-slate-900 line-clamp-2 group-hover:text-primary-700 dark:text-slate-100 dark:group-hover:text-primary-300">
                    {{ $next->name }}
                </span>
            </a>
        @else
            <span aria-hidden="true" class="hidden sm:block"></span>
        @endif
    </div>
</nav>
@endif