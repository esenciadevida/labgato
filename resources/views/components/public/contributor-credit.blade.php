@props(['contributor' => null, 'variant' => 'default'])

@if($contributor)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2.5 ' . ($variant === 'dark' ? 'text-white/70' : 'text-slate-600 dark:text-slate-400')]) }}>
        <x-public.avatar :name="$contributor->name" :user-id="$contributor->id" size="sm" />

        @if($contributor->is_active)
            <a
                href="{{ route('contributors.show', $contributor->profile_slug) }}"
                class="text-sm font-medium hover:underline transition-colors {{ $variant === 'dark' ? 'text-white hover:text-primary-300' : 'hover:text-primary-600' }}"
            >
                Contributed by {{ $contributor->name }}
            </a>
        @else
            <span class="text-sm font-medium">
                Contributed by {{ $contributor->name }}
            </span>
        @endif
    </div>
@endif
