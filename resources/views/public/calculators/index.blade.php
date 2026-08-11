@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('title', $pageTitle . ' - ' . config('app.name'))

@section('content')
<section class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Calculators']]" />

        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Calculators</h1>
        <p class="text-gray-600 max-w-3xl">
            Clinical calculators for laboratory professionals, covering electrolyte balance, acid-base status, and more.
        </p>
    </div>
</section>

<section class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('public.calculators.index') }}" class="mb-8 flex flex-col gap-3 sm:flex-row" role="search">
            <label for="q" class="sr-only">Search calculators</label>
            <input
                type="search"
                id="q"
                name="q"
                value="{{ $query }}"
                placeholder="Search calculators..."
                class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <select name="category" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach(\App\Models\Category::query()->where('is_active', true)->orderBy('name')->get() as $category)
                    <option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <select name="tag" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" aria-label="Filter by tag">
                <option value="">All tags</option>
                @foreach(\App\Models\Tag::query()->orderBy('name')->get() as $tag)
                    <option value="{{ $tag->slug }}" @selected($tagSlug === $tag->slug)>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                Filter
            </button>
        </form>

        @if($calculators->isEmpty())
            <div class="text-center py-16">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">No calculators found</h2>
                <p class="text-gray-600">
                    {{ $hasFilters ? 'No published calculators match your filters.' : 'Calculators are coming soon.' }}
                </p>
                @if($hasFilters)
                    <a href="{{ route('public.calculators.index') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        Clear filters
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($calculators as $calculator)
                    <article class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow flex flex-col">
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">
                            <a href="{{ route('public.calculators.show', $calculator->slug) }}" class="hover:text-blue-600 transition-colors">
                                {{ $calculator->name }}
                            </a>
                        </h2>

                        @if($calculator->short_description)
                            <p class="text-gray-600 text-sm leading-relaxed">{{ $calculator->short_description }}</p>
                        @endif

                        @if($calculator->categories->isNotEmpty())
                            <p class="mt-3 text-xs text-gray-500">
                                {{ $calculator->categories->pluck('name')->implode(', ') }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>

            @if($calculators->hasPages())
                <div class="mt-8">
                    {{ $calculators->links() }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
