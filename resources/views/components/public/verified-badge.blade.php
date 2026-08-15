@props([
    'date' => null,
    'source' => null,
])

@php
    $formattedDate = $date ? \Carbon\Carbon::parse($date)->format('F j, Y') : null;
@endphp

@if($formattedDate)
<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800" role="status" aria-live="polite">
    <svg class="w-4 h-4 text-success-600 dark:text-success-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    <span class="text-sm font-medium text-success-800 dark:text-success-200">
        Last verified: {{ $formattedDate }}
    </span>
    @if($source)
        <button
            type="button"
            class="ml-1 text-success-600 dark:text-success-400 hover:text-success-700 dark:hover:text-success-300 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 rounded p-0.5"
            aria-label="Show source"
            x-data="{ open: false }"
            @click.outside="open = false"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
        <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute z-10 mt-1 w-80 p-3 bg-slate-800 text-slate-100 rounded-lg shadow-lg text-sm whitespace-pre-line" role="tooltip">
            {{ $source }}
        </div>
    @endif
</div>
@endif