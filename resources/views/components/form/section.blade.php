@props(['title' => null, 'description' => null])

<div class="space-y-4">
    @if($title || $description)
        <div>
            @if($title)
                <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">{{ $title }}</h3>
            @endif
            @if($description)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
