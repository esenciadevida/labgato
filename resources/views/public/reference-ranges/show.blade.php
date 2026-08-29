@extends('layouts.public')

@section('content')
@php
    $lower = $referenceRange->lower_limit;
    $upper = $referenceRange->upper_limit;
    $unit = $referenceRange->unit;
    $fmt = fn ($value) => $value === null
        ? null
        : (floor($value) == $value ? number_format($value, 0, '.', '') : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'));
    $rangeLabel = trim(
        ($lower !== null ? $fmt($lower) : '')
        . ($lower !== null && $upper !== null ? '–' : '')
        . ($upper !== null ? $fmt($upper) : '')
    );
    $copyValue = $referenceRange->name . ($rangeLabel !== '' ? ': ' . $rangeLabel . ($unit ? ' ' . $unit : '') : '');
@endphp

<x-public.resource-hero
    :title="$referenceRange->name"
    :type="'Reference Ranges'"
    :typeLabel="'Reference Range'"
    :shortDescription="$referenceRange->short_description"
    :categories="$referenceRange->categories"
    :tags="$referenceRange->tags"
    :lastVerifiedAt="$referenceRange->last_verified_at"
    :publishedAt="$referenceRange->published_at"
    resourceType="reference-ranges"
>
    <x-public.share-button :url="url()->current()" />
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-6" id="article-content">
                <x-public.reference-range-disclaimer />

                <x-ui.card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Reference Range</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ collect([
                                    $referenceRange->population_group,
                                    $referenceRange->sex ? ucfirst($referenceRange->sex) : null,
                                    $referenceRange->specimen_type,
                                ])->filter()->implode(' · ') }}
                            </p>
                        </div>
                        <x-public.copy-button :value="$copyValue" label="Copy reference range" />
                    </div>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($lower !== null || $upper !== null)
                            <div class="rounded-xl bg-slate-50 dark:bg-slate-800 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Reference Range</p>
                                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">
                                    {{ $rangeLabel }}
                                    @if($unit)
                                        <span class="text-base font-medium text-slate-500 dark:text-slate-400">{{ $unit }}</span>
                                    @endif
                                </p>
                            </div>
                        @endif

                        @if($referenceRange->critical_low !== null || $referenceRange->critical_high !== null)
                            <div class="rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700/40 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Critical Values</p>
                                @if($referenceRange->critical_low !== null)
                                    <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                        Critical low: <span class="font-bold">{{ $fmt($referenceRange->critical_low) }} {{ $unit }}</span>
                                    </p>
                                @endif
                                @if($referenceRange->critical_high !== null)
                                    <p class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                                        Critical high: <span class="font-bold">{{ $fmt($referenceRange->critical_high) }} {{ $unit }}</span>
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <x-public.unit-toggle :range="$referenceRange" />

                @if($variations->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Demographic Variations</h2>
                        <ul class="space-y-3">
                            @foreach($variations as $variation)
                                <li class="flex items-start justify-between gap-4 pb-3 border-b border-slate-200 dark:border-slate-700 last:pb-0 last:border-b-0">
                                    <div>
                                        <a href="{{ route('public.reference-ranges.show', $variation->slug) }}" class="font-medium text-slate-900 dark:text-slate-100 hover:text-primary-600 transition-colors">
                                            {{ $variation->name }}
                                        </a>
                                        @if($variation->sex || $variation->population_group)
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                {{ collect([$variation->population_group, $variation->sex ? ucfirst($variation->sex) : null])->filter()->implode(' · ') }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 shrink-0">
                                        @if($variation->lower_limit !== null || $variation->upper_limit !== null)
                                            {{ ($variation->lower_limit !== null ? $fmt($variation->lower_limit) : '') . ($variation->lower_limit !== null && $variation->upper_limit !== null ? '–' : '') . ($variation->upper_limit !== null ? $fmt($variation->upper_limit) : '') }}
                                            @if($variation->unit)
                                                <span class="font-normal text-slate-500 dark:text-slate-400">{{ $variation->unit }}</span>
                                            @endif
                                        @else
                                            <span class="font-normal text-slate-400">—</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                <x-ui.card>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Details</h2>
                    <dl class="space-y-3 text-sm">
                        @if($referenceRange->analyte_name)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Analyte</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $referenceRange->analyte_name }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->specimen_type)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Specimen</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $referenceRange->specimen_type }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->methodology)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Method</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $referenceRange->methodology }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->population_group)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Population</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $referenceRange->population_group }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->sex)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Sex</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ ucfirst($referenceRange->sex) }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->age_min !== null || $referenceRange->age_max !== null)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Age Range</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ $referenceRange->age_min !== null ? $referenceRange->age_min : '' }}
                                    @if($referenceRange->age_min !== null && $referenceRange->age_max !== null)–@endif
                                    {{ $referenceRange->age_max !== null ? $referenceRange->age_max : '' }}
                                </dd>
                            </div>
                        @endif
                        @if($referenceRange->source_citation)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Source</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $referenceRange->source_citation }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->last_verified_at)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Last Verified</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $referenceRange->last_verified_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->effective_date)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Effective Date</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $referenceRange->effective_date->format('M j, Y') }}</dd>
                            </div>
                        @endif
                        @if($referenceRange->published_at)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Published</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $referenceRange->published_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>
            </div>

            <aside class="lg:col-span-4 space-y-6">
                <x-public.table-of-contents content-selector="#article-content" />

                @if($referenceRange->tags->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Tags</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach($referenceRange->tags as $tag)
                                <li>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs font-medium">
                                        #{{ $tag->name }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                @if($sops->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Covered in SOPs</h2>
                        <ul class="space-y-2">
                            @foreach($sops as $sopRef)
                                <li>
                                    <a href="{{ route('public.sops.show', $sopRef->slug) }}" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                        {{ $sopRef->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                @if($relatedCalculators->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Used by calculators</h2>
                        <ul class="space-y-2">
                            @foreach($relatedCalculators as $calculator)
                                <li>
                                    <a href="{{ route('public.calculators.show', $calculator->slug) }}" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                        {{ $calculator->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                <x-public.related-resources :resources="$related" :currentType="'Reference Range'" stacked />
            </aside>
        </div>
    </div>

@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "MedicalScholarlyArticle",
    "name": "{{ $referenceRange->name }}",
    "headline": "{{ $referenceRange->name }}",
    "description": "{{ $referenceRange->short_description ?? $referenceRange->name }}",
    "url": "{{ url()->current() }}",
    "author": {
        "@type": "Organization",
        "name": "{{ config('app.name') }}"
    }
}
</script>
@endpush
