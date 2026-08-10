@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button', 'href' => null, 'disabled' => false])

@php
$classes = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2';
$classes .= match($variant) {
    'primary' => ' bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'secondary' => ' bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
    'danger' => ' bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'ghost' => ' text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
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
