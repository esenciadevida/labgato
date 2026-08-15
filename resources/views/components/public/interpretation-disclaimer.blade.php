@props(['text' => '', 'class' => ''])

@php
    $disclaimerText = $text !== '' ? $text : 'Educational Interpretation Only: This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient\'s clinical condition and consult with a qualified healthcare professional.';
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 flex items-start gap-3 dark:border-amber-700/50 dark:bg-amber-950/40 ' . $class]) }} role="note" aria-label="Educational interpretation disclaimer">
    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Educational Interpretation Only</p>
        <p class="mt-0.5 text-sm text-amber-800 dark:text-amber-300">{{ $disclaimerText }}</p>
    </div>
</div>
