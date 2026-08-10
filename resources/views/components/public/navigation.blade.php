@props(['active' => false])

<li>
    <a
        href="{{ $href ?? '#' }}"
        {{ $attributes->merge([
            'class' => 'text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors ' .
                ($active ? 'text-blue-600 font-semibold' : '')
        ]) }}
        @if($active) aria-current="page" @endif
    >
        {{ $slot }}
    </a>
</li>
