@props(['title' => null, 'description' => null])

<div class="space-y-4">
    @if($title || $description)
        <div>
            @if($title)
                <h3 class="text-lg font-medium text-neutral-900">{{ $title }}</h3>
            @endif
            @if($description)
                <p class="mt-1 text-sm text-neutral-500">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
