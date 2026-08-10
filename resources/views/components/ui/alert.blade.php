@props(['type' => 'info'])

@php
$classes = 'rounded-md p-4';
$classes .= match($type) {
    'success' => ' bg-green-50 text-green-800',
    'error' => ' bg-red-50 text-red-800',
    'warning' => ' bg-yellow-50 text-yellow-800',
    default => ' bg-blue-50 text-blue-800',
};
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    <div class="text-sm">{{ $slot }}</div>
</div>
