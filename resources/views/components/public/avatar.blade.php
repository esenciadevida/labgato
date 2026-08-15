@props(['name' => '', 'userId' => null, 'size' => 'md'])

@php
    $initials = collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->map(fn (string $word) => strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->implode('');

    $palette = [
        '#0f766e',
        '#4338ca',
        '#be123c',
        '#b45309',
        '#1d4ed8',
        '#9d174d',
        '#047857',
        '#7c3aed',
    ];
    $color = $palette[abs((int) crc32((string) $userId)) % count($palette)];

    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-16 w-16 text-2xl',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full font-semibold text-white shrink-0 '.($sizes[$size] ?? $sizes['md'])]) }}
      style="background-color: {{ $color }}"
      aria-hidden="true">
    {{ $initials !== '' ? $initials : '?' }}
</span>
