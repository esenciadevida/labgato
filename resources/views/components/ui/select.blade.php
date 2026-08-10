@props(['disabled' => false])

<select
    {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm' . ($disabled ? ' bg-gray-50 text-gray-500 cursor-not-allowed' : '')]) }}
    @if($disabled) disabled @endif
>
    {{ $slot }}
</select>
