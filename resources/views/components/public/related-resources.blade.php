@props([
    'resources' => collect(),
    'currentType' => '',
    'stacked' => false,
])

@php
    if (! $resources instanceof \Illuminate\Support\Collection) {
        $resources = collect($resources ?? []);
    }

    $routeMap = [
        'Calculator' => 'public.calculators.show',
        'SOP' => 'public.sops.show',
        'Reference Range' => 'public.reference-ranges.show',
        'Knowledge' => 'public.knowledge.show',
        'Interpretation' => 'public.interpretation.show',
        'Laboratory Tool' => 'public.tools.show',
    ];

    $showRoute = $routeMap[$currentType] ?? null;
    $heading = 'Related ' . \Illuminate\Support\Str::plural($currentType ?: 'Resources');
@endphp

@if($resources->isNotEmpty())
@if($stacked)
<div>
    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">{{ $heading }}</h2>
@else
<div class="py-12 bg-white dark:bg-slate-900">
    <h2 class="text-2xl font-display font-bold text-slate-900 dark:text-slate-100 mb-6">{{ $heading }}</h2>
@endif

    @if($stacked)
    <div class="space-y-3">
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @endif
            @foreach($resources as $resource)
            <a href="{{ $resource->url ?? ($showRoute ? route($showRoute, $resource->slug) : '#') }}" class="block rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:border-primary-500 transition overflow-hidden">
                <span class="inline-block max-w-full truncate rounded-full bg-primary-50 dark:bg-primary-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-primary-700 dark:text-primary-400">
                    {{ $currentType ?: 'Resource' }}
                </span>
                <h3 class="mt-2 font-semibold text-slate-900 dark:text-slate-100 leading-snug line-clamp-2">{{ $resource->title ?? $resource->name }}</h3>
                @if($resource->short_description)
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ $resource->short_description }}</p>
                @endif
            </a>
            @endforeach
        </div>
</div>
@endif
