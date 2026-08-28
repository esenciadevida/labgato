@props([
    'src' => null,
    'alt' => '',
    'class' => '',
    'sizes' => null,
    'loading' => 'lazy',
    'eager' => false,
    'priority' => false,
])

@php
    $dims = $src ? \App\Support\StoredImage::resolve($src) : null;
    $srcset = $src ? \App\Support\StoredImage::srcset($src) : null;
    $effectiveSizes = $sizes ?? ($src ? \App\Support\StoredImage::DEFAULT_SIZES : null);
@endphp

@if($src)
<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    loading="{{ $eager ? 'eager' : $loading }}"
    @if($priority) fetchpriority="high" @endif
    @if($dims) width="{{ $dims['width'] }}" height="{{ $dims['height'] }}" @endif
    @if($srcset && $effectiveSizes) srcset="{{ $srcset }}" sizes="{{ $effectiveSizes }}" @endif
    class="{{ $class }}"
    {{ $attributes }}
>
@endif