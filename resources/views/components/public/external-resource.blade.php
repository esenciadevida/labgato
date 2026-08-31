@props([
    'entry' => null,
    'title' => null,
])

@php
    $url = $entry['labtestsguide_url'] ?? null;
    $name = $title ?? ($entry['labgoto_name'] ?? 'Lab Tests Guide');
    $slug = $entry['labtestsguide_slug'] ?? null;
@endphp

@if($url)
<x-ui.card>
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </span>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Learn more</p>
            <a
                href="{{ $url }}"
                target="_blank"
                rel="noopener noreferrer nofollow"
                class="font-semibold text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 leading-snug break-words"
            >
                {{ $name }}
            </a>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">External reference on Lab Tests Guide{{ $slug ? ' · ' . str_replace('-', ' ', (string) $slug) : '' }}</p>
        </div>
    </div>
</x-ui.card>
@endif
