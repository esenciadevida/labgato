@props(['name' => '', 'userId' => null, 'size' => 'md', 'src' => null])

@php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->map(fn (string $word) => strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->implode('');

    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-16 w-16 text-2xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($src)
    <img src="{{ $src }}" alt="" loading="lazy"
         {{ $attributes->merge(['class' => 'inline-block shrink-0 rounded-full object-cover '.$sizeClass]) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-primary-600 font-semibold text-white shrink-0 '.$sizeClass]) }}
          aria-hidden="true">
        {{ $initials !== '' ? $initials : '?' }}
    </span>
@endif
