@props(['variant' => 'default'])

@php
$classes = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
$classes .= match($variant) {
    'success' => ' bg-success-50 text-success-600 border border-success-600/20',
    'danger' => ' bg-danger-50 text-danger-600 border border-danger-600/20',
    'warning' => ' bg-warning-50 text-warning-600 border border-warning-600/20',
    'info' => ' bg-info-50 text-info-600 border border-info-600/20',
    default => ' bg-neutral-100 text-neutral-700 border border-neutral-200',
};
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
