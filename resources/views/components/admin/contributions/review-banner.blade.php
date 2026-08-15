@props([
    'resource' => null,
    'type' => null,
])

@php
    $contributor = $resource->contributedBy;
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-700 dark:bg-slate-800">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4 min-w-0">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-600 text-sm font-semibold text-white">
                {{ strtoupper(substr($contributor?->name ?? '?', 0, 1)) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">
                    {{ $contributor?->name ?? 'Unknown contributor' }}
                </p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                    Submitted {{ $resource->created_at?->format('M j, Y g:i A') ?? '—' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-700 border border-primary-600/20 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-600/30">
                {{ $type?->label() }}
            </span>
            <x-ui.badge variant="warning">
                {{ $resource->status->label() }}
            </x-ui.badge>
        </div>
    </div>

    @if($resource->short_description)
        <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">
            {{ $resource->short_description }}
        </p>
    @endif
</div>
