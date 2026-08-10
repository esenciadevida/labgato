@php $metaNoindex = true; @endphp

@extends('layouts.public')

@section('title', $pageTitle . ' - ' . config('app.name'))

@section('content')
<section class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6">Search LabGato</h1>

        <x-public.search.form
            :q="request()->input('q')"
            :type="request()->input('type', 'all')"
        />
    </div>
</section>

<section class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(request()->filled('q'))
            <p class="text-sm text-gray-600 mb-6" role="status">
                {{ $results->total() }} {{ Str::plural('result', $results->total()) }} for &ldquo;{{ request()->input('q') }}&rdquo;
            </p>

            @if($results->isEmpty())
                <div class="text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">No results found</h2>
                    <p class="text-gray-600 mb-6">Try different keywords, or check the spelling of your search terms.</p>
                    <a href="{{ route('public.search') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Start a new search
                    </a>
                </div>
            @else
                <div class="space-y-4" aria-label="Search results">
                    @foreach($results as $result)
                        <x-public.search.result-item :result="$result" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $results->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-16">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">What are you looking for?</h2>
                <p class="text-gray-600">
                    Search calculators, tools, reference ranges, and laboratory resources.
                </p>
            </div>
        @endif
    </div>
</section>
@endsection
