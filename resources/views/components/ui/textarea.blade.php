@props(['disabled' => false, 'rows' => 4])

<textarea
    {{ $attributes->merge([
        'class' => 'block w-full rounded-md border border-neutral-300 bg-white px-3 py-2 text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 sm:text-sm' .
            ($disabled ? ' cursor-not-allowed bg-neutral-100 text-neutral-500' : ''),
        'rows' => $rows,
    ]) }}
    @if($disabled) disabled @endif
>{{ $slot }}</textarea>
