@extends('layouts.public')

@section('title', $pageTitle . ' - ' . config('app.name'))

@section('content')
<section class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <x-public.breadcrumbs :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Calculators', 'url' => route('public.calculators.index')],
            ['label' => $calculator->name],
        ]" />

        <div class="flex flex-wrap items-center gap-3 mb-3">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-medium uppercase tracking-wider">
                Calculator
            </span>
            @foreach($calculator->categories as $category)
                <a
                    href="{{ route('public.calculators.index', ['category' => $category->slug]) }}"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-medium hover:bg-gray-200 transition-colors"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $calculator->name }}</h1>

        @if($calculator->short_description)
            <p class="text-gray-600 max-w-3xl">{{ $calculator->short_description }}</p>
        @endif

        @if($calculator->formula_display)
            <div class="mt-4 inline-block rounded-md bg-blue-50 px-4 py-2 text-sm font-mono text-blue-800">
                {{ $calculator->formula_display }}
            </div>
        @endif
    </div>
</section>

<section class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <x-ui.card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Calculate</h2>
                <livewire:public-site.calculators.calculator-runner :calculator="$calculator" />
            </x-ui.card>

            @if($calculator->interpretation_notes)
                <x-ui.card class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Interpretation</h2>
                    <div class="prose prose-sm max-w-none text-gray-700">
                        {!! nl2br(e($calculator->interpretation_notes)) !!}
                    </div>
                </x-ui.card>
            @endif

            @if($calculator->references)
                <x-ui.card class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">References</h2>
                    <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">
                        {!! nl2br(e($calculator->references)) !!}
                    </div>
                </x-ui.card>
            @endif
        </div>

        <aside class="space-y-6">
            @if($calculator->tags->isNotEmpty())
                <x-ui.card>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Tags</h2>
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach($calculator->tags as $tag)
                            <li>
                                <a
                                    href="{{ route('public.calculators.index', ['tag' => $tag->slug]) }}"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs hover:bg-gray-200 transition-colors"
                                >
                                    #{{ $tag->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif

            @if($related->isNotEmpty())
                <x-ui.card>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Related Calculators</h2>
                    <ul class="space-y-2">
                        @foreach($related as $relatedCalculator)
                            <li>
                                <a href="{{ route('public.calculators.show', $relatedCalculator->slug) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                    {{ $relatedCalculator->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif
        </aside>
    </div>
</section>
@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "{{ $calculator->name }}",
    "description": "{{ $calculator->short_description ?? $calculator->name }}",
    "url": "{{ url()->current() }}",
    "applicationCategory": "HealthApplication",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
@endpush
