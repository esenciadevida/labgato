@props(['variant' => 'default'])

@php
$classes = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
$classes .= match($variant) {
    'success' => ' bg-success-50 text-success-600 border border-success-600/20 dark:bg-success-900/30 dark:text-success-400 dark:border-success-600/30',
    'danger' => ' bg-danger-50 text-danger-600 border border-danger-600/20 dark:bg-danger-900/30 dark:text-danger-400 dark:border-danger-600/30',
    'warning' => ' bg-warning-50 text-warning-600 border border-warning-600/20 dark:bg-warning-900/30 dark:text-warning-400 dark:border-warning-600/30',
    'info' => ' bg-info-50 text-info-600 border border-info-600/20 dark:bg-info-900/30 dark:text-info-400 dark:border-info-600/30',
    'accent' => ' bg-primary-50 text-primary-600 border border-primary-600/20 dark:bg-primary-900/30 dark:text-primary-400 dark:border-primary-600/30',
    default => ' bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
};
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
