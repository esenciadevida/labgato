@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('content')
<section class="bg-primary-950 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950 border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Interpretations']]" dark />

        <p class="eyebrow text-primary-300">Interpretations</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">Interpretations</h1>
        <p class="mt-3 text-white/70 max-w-3xl">
            Guides for understanding common laboratory result patterns. Educational only — never a substitute for clinical judgment.
        </p>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-public.interpretation-disclaimer class="mb-8" />

        <x-public.search.live-results
            baseUrl="{{ route('public.interpretation.index') }}"
            type="interpretation"
            :initialQ="$query"
            :initialCategory="$categorySlug"
            :initialTag="$tagSlug"
            inputId="q"
            label="Search interpretations"
            placeholder="Search by topic or analyte..."
            :limit="12"
        >

        @if($interpretations->isEmpty())
            <div class="text-center py-16">
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">No interpretations found</h2>
                <p class="text-slate-600 dark:text-slate-300">
                    {{ $hasFilters ? 'No published interpretations match your filters.' : 'Interpretation guides are coming soon.' }}
                </p>
                @if($hasFilters)
                    <a href="{{ route('public.interpretation.index') }}" class="mt-6 inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                        Clear filters
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($interpretations as $interpretation)
                    <a href="{{ route('public.interpretation.show', $interpretation->slug) }}" class="group block bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition flex flex-col">
                        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">
                            <span class="text-primary-700 group-hover:text-primary-800 transition-colors">
                                {{ $interpretation->name }}
                            </span>
                        </h2>

                        @if($interpretation->related_analytes)
                            <p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider mb-3">
                                {{ collect($interpretation->related_analytes)->take(4)->implode(' · ') }}
                            </p>
                        @endif

                        @if($interpretation->short_description)
                            <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">{{ $interpretation->short_description }}</p>
                        @endif

                        <div class="mt-auto pt-4 flex flex-wrap items-center gap-2">
                            @if($interpretation->reading_time_minutes)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-medium">
                                    {{ $interpretation->reading_time_minutes }} min read
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 uppercase tracking-wider ml-auto">
                                Open
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($interpretations->hasPages())
                <div class="mt-8">
                    {{ $interpretations->links() }}
                </div>
            @endif
        @endif
        </x-public.search.live-results>
    </div>
</section>
@endsection
