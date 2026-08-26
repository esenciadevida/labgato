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
        'xl' => 'h-28 w-28 text-5xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $label = $initials !== '' ? $initials : '?';
@endphp

@if($src)
    <span x-data="{ failed: false }"
          {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-600 '.$sizeClass]) }}>
        <img src="{{ $src }}" alt="" loading="lazy" x-show="!failed" x-on:error="failed = true"
             class="h-full w-full rounded-full object-cover">
        <span x-show="failed" x-cloak aria-hidden="true"
              class="flex h-full w-full items-center justify-center rounded-full bg-primary-600 font-semibold text-white">
            {{ $label }}
        </span>
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-full bg-primary-600 font-semibold text-white shrink-0 '.$sizeClass]) }}
          aria-hidden="true">
        {{ $label }}
    </span>
@endif
