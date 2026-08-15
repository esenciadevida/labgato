@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('content')
<section class="bg-primary-950 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950 border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Calculators']]" dark />

        <p class="eyebrow text-primary-300">Calculators</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">Calculators</h1>
        <p class="mt-3 text-white/70 max-w-3xl">
            Clinical calculators for laboratory professionals, covering electrolyte balance, acid-base status, and more.
        </p>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-public.search.live-results
            baseUrl="{{ route('public.calculators.index') }}"
            type="calculator"
            :initialQ="$query"
            :initialCategory="$categorySlug"
            :initialTag="$tagSlug"
            inputId="q"
            label="Search calculators"
            placeholder="Search calculators..."
            :limit="12"
        >

        @if($calculators->isEmpty())
            <div class="text-center py-16">
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">No calculators found</h2>
                <p class="text-slate-600 dark:text-slate-300">
                    {{ $hasFilters ? 'No published calculators match your filters.' : 'Calculators are coming soon.' }}
                </p>
                @if($hasFilters)
                    <a href="{{ route('public.calculators.index') }}" class="mt-6 inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                        Clear filters
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($calculators as $calculator)
                    <a href="{{ route('public.calculators.show', $calculator->slug) }}" class="group block bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition flex flex-col">
                        <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">
                            <span class="text-primary-700 group-hover:text-primary-800 transition-colors">
                                {{ $calculator->name }}
                            </span>
                        </h2>

                        @if($calculator->short_description)
                            <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">{{ $calculator->short_description }}</p>
                        @endif

                        <div class="mt-auto pt-4 flex flex-wrap items-center gap-2">
                            @if($calculator->categories->isNotEmpty())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-medium">
                                    {{ $calculator->categories->pluck('name')->implode(', ') }}
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

            @if($calculators->hasPages())
                <div class="mt-8">
                    {{ $calculators->links() }}
                </div>
            @endif
        @endif
        </x-public.search.live-results>
    </div>
</section>
@endsection
