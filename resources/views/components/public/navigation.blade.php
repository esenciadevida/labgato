@props(['active' => false])

<li>
    <a
        href="{{ $href ?? '#' }}"
        {{ $attributes->merge([
            'class' => 'text-neutral-700 hover:text-primary-700 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 ' .
                ($active ? 'text-primary-700 font-semibold' : '')
        ]) }}
        @if($active) aria-current="page" @endif
    >
        {{ $slot }}
    </a>
</li>
