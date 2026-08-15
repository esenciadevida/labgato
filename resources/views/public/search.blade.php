@php $metaNoindex = true; @endphp

@extends('layouts.public')

@section('content')
<section class="bg-primary-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <p class="eyebrow text-primary-300">Discover</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">{{ $pageTitle }}</h1>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-public.search.live-results
            baseUrl="{{ route('public.search') }}"
            :showTypeSelect="true"
            :initialQ="request()->input('q', '')"
            :initialType="request()->input('type', 'all')"
            :initialCategory="request()->input('category', '')"
            :initialTag="request()->input('tag', '')"
            inputId="search-input"
            label="Search"
            placeholder="Search resources..."
            :limit="10"
        >
            @if(request()->filled('q'))
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-6" role="status">
                    {{ $results->total() }} {{ Str::plural('result', $results->total()) }} for &ldquo;{{ request()->input('q') }}&rdquo;
                </p>

                @if($results->isEmpty())
                    <div class="text-center py-16">
                        <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">No results found</h2>
                        <p class="text-slate-600 dark:text-slate-300 mb-6">Try different keywords, or check the spelling of your search terms.</p>
                        <a href="{{ route('public.search') }}" class="inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                            Start a new search
                        </a>
                    </div>
                @else
                    <div class="space-y-4" aria-label="Search results">
                        @foreach($results as $result)
                            <x-public.search.result-item :result="$result" :query="request()->input('q', '')" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $results->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-16">
                    <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">What are you looking for?</h2>
                    <p class="text-slate-600 dark:text-slate-300">
                        Search calculators, tools, reference ranges, and laboratory resources.
                    </p>
                </div>
            @endif
        </x-public.search.live-results>
    </div>
</section>
@endsection
