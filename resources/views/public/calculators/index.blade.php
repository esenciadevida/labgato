@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('title', $pageTitle . ' - ' . config('app.name'))

@section('content')
<section class="bg-white border-b border-neutral-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Calculators']]" />

        <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 mb-2">Calculators</h1>
        <p class="text-neutral-600 max-w-3xl">
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
                class="flex-1 rounded-md border border-neutral-300 bg-white shadow-card text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
            >
            <select name="category" class="rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach(\App\Models\Category::query()->where('is_active', true)->orderBy('name')->get() as $category)
                    <option value="{{ $category->slug }}" @selected($categorySlug === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <select name="tag" class="rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20" aria-label="Filter by tag">
                <option value="">All tags</option>
                @foreach(\App\Models\Tag::query()->orderBy('name')->get() as $tag)
                    <option value="{{ $tag->slug }}" @selected($tagSlug === $tag->slug)>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-md shadow-card text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                Filter
            </button>
        </form>

        @if($calculators->isEmpty())
            <div class="text-center py-16">
                <h2 class="text-xl font-semibold text-neutral-900 mb-2">No calculators found</h2>
                <p class="text-neutral-600">
                    {{ $hasFilters ? 'No published calculators match your filters.' : 'Calculators are coming soon.' }}
                </p>
                @if($hasFilters)
                    <a href="{{ route('public.calculators.index') }}" class="mt-6 inline-flex items-center px-4 py-2 border border-neutral-300 rounded-md text-sm font-medium text-neutral-700 bg-white hover:bg-neutral-50 transition-colors">
                        Clear filters
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($calculators as $calculator)
                    <article class="bg-white rounded-lg shadow-card border border-neutral-200 p-6 hover:border-primary-300 hover:shadow-card transition flex flex-col">
                        <h2 class="text-lg font-semibold text-neutral-900 mb-1">
                            <a href="{{ route('public.calculators.show', $calculator->slug) }}" class="text-primary-700 hover:text-primary-800 transition-colors">
                                {{ $calculator->name }}
                            </a>
                        </h2>

                        @if($calculator->short_description)
                            <p class="text-neutral-600 text-sm leading-relaxed">{{ $calculator->short_description }}</p>
                        @endif

                        @if($calculator->categories->isNotEmpty())
                            <p class="mt-3 text-xs text-neutral-500">
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
