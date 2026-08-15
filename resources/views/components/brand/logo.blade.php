@props(['dark' => false, 'theme' => false, 'size' => 'md', 'href' => null, 'class' => '', 'imgClass' => ''])

@php
    $height = match ($size) {
        'xs' => 'h-8',
        'sm' => 'h-10',
        'md' => 'h-14',
        'lg' => 'h-20',
        'xl' => 'h-28',
        '2xl' => 'h-36',
        default => 'h-10',
    };
    $filterClass = ($dark || $theme) ? 'logo-theme' : '';
@endphp

<a href="{{ $href ?? route('home') }}" class="inline-flex items-center shrink-0 rounded-md focus:outline-none focus:ring-0 {{ $class }}" aria-label="LabGoTo home">
    <img
        src="{{ asset('assets/LabGoTo_logo.svg') }}"
        alt="LabGoTo"
        class="{{ $height }} w-auto {{ $imgClass }} {{ $filterClass }} shrink-0 object-contain transition-transform duration-150 hover:scale-[1.02] group-hover:scale-[1.05]"
        loading="eager"
    >
</a>
