@props([])

<div
    x-data
    x-cloak
    x-show="$store.confirm.open"
    @keydown.escape.window="$store.confirm.cancel()"
    class="fixed inset-0 z-[95] overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ui-confirm-title"
>
    <div
        class="fixed inset-0 bg-slate-900/60 transition-opacity"
        x-show="$store.confirm.open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$store.confirm.cancel()"
        aria-hidden="true"
    ></div>

    <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
        <div
            x-show="$store.confirm.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-panel dark:border-slate-700 dark:bg-slate-900"
        >
            <div class="flex items-start gap-4">
                <span
                    class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full"
                    :class="$store.confirm.danger
                        ? 'bg-danger-100 text-danger-600 dark:bg-danger-900/40 dark:text-danger-400'
                        : 'bg-warning-100 text-warning-600 dark:bg-warning-900/40 dark:text-warning-400'"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>

                <div class="min-w-0 flex-1">
                    <h2 id="ui-confirm-title" class="font-display text-base font-semibold text-slate-900 dark:text-slate-100" x-text="$store.confirm.title"></h2>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600 dark:text-slate-300" x-text="$store.confirm.message"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    @click="$store.confirm.cancel()"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    <span x-text="$store.confirm.cancelText"></span>
                </button>
                <button
                    type="button"
                    @click="$store.confirm.proceed()"
                    class="inline-flex min-h-[44px] items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    :class="$store.confirm.danger
                        ? 'bg-danger-600 hover:bg-danger-700 focus:ring-danger-500'
                        : 'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500'"
                >
                    <span x-text="$store.confirm.confirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
