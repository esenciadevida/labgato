@props(['name' => '', 'userId' => null, 'size' => 'md'])

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
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-primary-600 font-semibold text-white shrink-0 '.($sizes[$size] ?? $sizes['md'])]) }}
      aria-hidden="true">
    {{ $initials !== '' ? $initials : '?' }}
</span>
