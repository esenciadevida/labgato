@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 flex items-start gap-3 dark:border-amber-700/50 dark:bg-amber-950/40 ' . $class]) }} role="note" aria-label="Educational reference only">
    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Educational Reference Only</p>
        <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-300">
            Reference ranges are provided for general educational purposes and may not apply to your situation. Always rely on the ranges reported by your laboratory and the judgment of your healthcare provider.
        </p>
    </div>
</div>
