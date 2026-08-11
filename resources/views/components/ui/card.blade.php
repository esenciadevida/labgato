@props(['title' => null, 'interactive' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-neutral-200 shadow-card' . ($interactive ? ' transition hover:border-primary-300' : '')]) }}>
    @if($title)
        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200">
            <h3 class="text-lg font-semibold text-neutral-900">{{ $title }}</h3>
        </div>
    @endif
    <div class="px-4 sm:px-6 py-4">
        {{ $slot }}
    </div>
</div>
