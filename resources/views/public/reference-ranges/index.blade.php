@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('content')
<section class="bg-primary-950 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950 border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Reference Ranges']]" dark />

        <p class="eyebrow text-primary-300">Reference Ranges</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">Reference Ranges</h1>
        <p class="mt-3 text-white/70 max-w-3xl">
            Reference ranges for common laboratory analytes, broken down by demographic group. For educational reference only.
        </p>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-public.reference-range-disclaimer class="mb-8" />

        <x-public.search.live-results
            baseUrl="{{ route('public.reference-ranges.index') }}"
            type="reference_range"
            :initialQ="$query"
            :initialCategory="$categorySlug"
            :initialTag="$tagSlug ?? ''"
            inputId="q"
            label="Search reference ranges"
            placeholder="Search by analyte or range name..."
            :limit="12"
        >

        @if($referenceRanges->isEmpty())
            <div class="text-center py-16">
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">No reference ranges found</h2>
                <p class="text-slate-600 dark:text-slate-300">
                    {{ $hasFilters ? 'No published reference ranges match your filters.' : 'Reference ranges are coming soon.' }}
                </p>
                @if($hasFilters)
                    <a href="{{ route('public.reference-ranges.index') }}" class="mt-6 inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                        Clear filters
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($referenceRanges as $referenceRange)
                    <a href="{{ route('public.reference-ranges.show', $referenceRange->slug) }}" class="group block bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition flex flex-col">
                        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">
                            <span class="text-primary-700 group-hover:text-primary-800 transition-colors">
                                {{ $referenceRange->name }}
                            </span>
                        </h2>

                        @php
                            $demographics = collect([
                                $referenceRange->sex !== 'any' ? ucfirst($referenceRange->sex) : null,
                                $referenceRange->population_group,
                                $referenceRange->age_min !== null && $referenceRange->age_max !== null
                                    ? $referenceRange->age_min.'–'.$referenceRange->age_max.' years'
                                    : null,
                            ])->filter();
                        @endphp
                        @if($demographics->isNotEmpty())
                            <p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider mb-3">
                                {{ $demographics->implode(' · ') }}
                            </p>
                        @endif

                        @if($referenceRange->lower_limit !== null || $referenceRange->upper_limit !== null)
                            <p class="text-2xl font-display font-bold text-slate-900 dark:text-slate-100">
                                {{ $referenceRange->lower_limit !== null ? rtrim(rtrim(number_format($referenceRange->lower_limit, 4, '.', ''), '0'), '.') : '≤' }}
                                – {{ $referenceRange->upper_limit !== null ? rtrim(rtrim(number_format($referenceRange->upper_limit, 4, '.', ''), '0'), '.') : '≥' }}
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $referenceRange->unit }}</span>
                            </p>
                        @endif

                        @if($referenceRange->short_description)
                            <p class="mt-2 text-slate-600 dark:text-slate-300 text-sm leading-relaxed">{{ $referenceRange->short_description }}</p>
                        @endif

                        <div class="mt-auto pt-4 flex flex-wrap items-center gap-2">
                            @if($referenceRange->categories->isNotEmpty())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-medium">
                                    {{ $referenceRange->categories->pluck('name')->implode(', ') }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 uppercase tracking-wider">
                                Open
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($referenceRanges->hasPages())
                <div class="mt-8">
                    {{ $referenceRanges->links() }}
                </div>
            @endif
        @endif
        </x-public.search.live-results>
    </div>
</section>
@endsection
