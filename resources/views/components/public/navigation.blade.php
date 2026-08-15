@props(['active' => false, 'href' => null])

<li>
    <a
        href="{{ $href ?? '#' }}"
        {{ $attributes->merge([
            'class' => 'text-slate-700 hover:text-primary-700 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-200 dark:hover:text-primary-400 ' .
                ($active ? 'text-primary-700 font-semibold dark:text-primary-400' : '')
        ]) }}
        @if($active) aria-current="page" @endif
    >
        {{ $slot }}
    </a>
</li>
