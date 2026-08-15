@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'href' => null, 'disabled' => false])

@php
$classes = 'inline-flex items-center justify-center rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
$classes .= match($variant) {
    'primary' => ' bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-card',
    'secondary' => ' bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400 focus:ring-slate-400 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:border-slate-600',
    'success' => ' bg-success-600 text-white hover:bg-success-700 focus:ring-success-500 shadow-card dark:bg-success-500 dark:hover:bg-success-400',
    'danger' => ' bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500 shadow-card dark:bg-danger-500 dark:hover:bg-danger-400',
    'info' => ' bg-info-600 text-white hover:bg-info-700 focus:ring-info-500 shadow-card dark:bg-info-500 dark:hover:bg-info-400',
    'ghost' => ' text-primary-700 hover:bg-primary-50 focus:ring-primary-500 dark:text-primary-400 dark:hover:bg-primary-900/30',
    default => ' bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 shadow-card',
};
$classes .= match($size) {
    'sm' => ' px-2.5 py-1 text-sm',
    'md' => ' px-4 py-2 text-sm',
    'lg' => ' px-6 py-3 text-base',
};
if ($disabled) {
    $classes .= ' opacity-50 cursor-not-allowed';
}
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
