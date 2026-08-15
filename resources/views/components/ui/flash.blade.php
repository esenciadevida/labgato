@props([])

@php
    $flashes = [];

    if (session('status')) {
        $flashes[] = ['type' => 'success', 'title' => 'Success', 'message' => session('status')];
    }

    if (session('success')) {
        $success = ['type' => 'success', 'title' => 'Success', 'message' => session('success')];
        if (session('reference_code')) {
            $success['message'] .= ' (Reference: ' . session('reference_code') . ')';
        }
        $flashes[] = $success;
    }

    if (session('error')) {
        $flashes[] = ['type' => 'error', 'title' => 'Error', 'message' => session('error')];
    }

    if (session('warning')) {
        $flashes[] = ['type' => 'warning', 'title' => 'Warning', 'message' => session('warning')];
    }

    if (session('info')) {
        $flashes[] = ['type' => 'info', 'title' => 'Information', 'message' => session('info')];
    }
@endphp

<div
    x-data
    x-cloak
    x-init="{{ Js::from($flashes) }}.forEach((flash) => $store.toast.push(flash.type, flash.message, flash.title))"
    class="pointer-events-none fixed right-4 top-4 z-[100] w-[min(92vw,24rem)] space-y-3"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="toast in $store.toast.toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4"
            role="status"
            class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border bg-white p-4 shadow-pop dark:bg-slate-900"
            :class="{
                'border-success-600/30': toast.type === 'success',
                'border-danger-600/30': toast.type === 'error',
                'border-warning-600/30': toast.type === 'warning',
                'border-info-600/30': toast.type === 'info',
            }"
        >
            <span
                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full"
                :class="{
                    'bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300': toast.type === 'success',
                    'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300': toast.type === 'error',
                    'bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300': toast.type === 'warning',
                    'bg-info-100 text-info-700 dark:bg-info-900/40 dark:text-info-300': toast.type === 'info',
                }"
            >
                <template x-if="toast.type === 'success'">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </span>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="toast.title"></p>
                <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300" x-text="toast.message"></p>
            </div>

            <button
                type="button"
                class="flex-shrink-0 rounded p-1 text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-500 dark:hover:text-slate-300"
                @click="$store.toast.dismiss(toast.id)"
                aria-label="Dismiss"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
