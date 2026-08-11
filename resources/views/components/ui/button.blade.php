@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'href' => null, 'disabled' => false])

@php
$classes = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
$classes .= match($variant) {
    'primary' => ' bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    'secondary' => ' bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 focus:ring-neutral-400',
    'danger' => ' bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500',
    'ghost' => ' text-primary-700 hover:bg-primary-50 focus:ring-primary-500',
};
$classes .= match($size) {
    'sm' => ' px-3 py-1.5 text-sm',
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
