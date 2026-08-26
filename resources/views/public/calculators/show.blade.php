@extends('layouts.public')

@section('content')
<x-public.resource-hero
    :title="$calculator->name"
    :type="'calculators'"
    :typeLabel="'Calculator'"
    :shortDescription="$calculator->short_description"
    :categories="$calculator->categories"
    :tags="$calculator->tags"
    :publishedAt="$calculator->published_at"
    resourceType="calculators"
>
    <div class="flex flex-wrap items-center gap-3">
        <x-public.share-button :url="url()->current()" />
    </div>
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        <livewire:public-site.calculators.calculator-runner :calculator="$calculator" />

        @if($calculator->formula_display || $calculator->interpretation_notes)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white mb-4">Formula & Clinical Notes</h2>
                <div class="resource-content">
                    @if($calculator->formula_display)
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Formula</h3>
                            <p class="mt-1 font-mono text-sm text-slate-700 dark:text-slate-300">{{ $calculator->formula_display }}</p>
                        </div>
                    @endif

                    @if($calculator->interpretation_notes)
                        <div>
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Clinical Notes</h3>
                            <div class="mt-1 text-slate-700 dark:text-slate-300">
                                {!! nl2br(e($calculator->interpretation_notes)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @php
            $referenceList = json_decode((string) $calculator->references, true);
        @endphp
        @if(is_array($referenceList) && count($referenceList))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white mb-4">References</h2>
                <ul class="space-y-2">
                    @foreach($referenceList as $reference)
                        @php
                            $url = is_array($reference) ? ($reference['url'] ?? null) : null;
                            $title = is_array($reference) ? ($reference['title'] ?? $url) : $reference;
                        @endphp
                        <li>
                            @if($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                    {{ $title ?: $url }}
                                </a>
                            @else
                                <span class="text-sm text-slate-700 dark:text-slate-300">{{ $title }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @elseif(trim((string) $calculator->references) !== '' && trim((string) $calculator->references) !== '[]')
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white mb-4">References</h2>
                <div class="text-slate-700 dark:text-slate-300 whitespace-pre-line">
                    {!! nl2br(e($calculator->references)) !!}
                </div>
            </div>
        @endif

        <x-public.recent-calculations :calculator-slug="$calculator->slug" />
    </div>

    <aside class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
            <h3 class="font-semibold text-slate-900 dark:text-white mb-4">About</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Category</dt>
                    <dd class="text-slate-900 dark:text-slate-200">
                        {{ $calculator->categories->isNotEmpty() ? $calculator->categories->pluck('name')->implode(', ') : 'Uncategorized' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-slate-400">Last updated</dt>
                    <dd class="text-slate-900 dark:text-slate-200">{{ $calculator->updated_at ? $calculator->updated_at->format('M j, Y') : 'N/A' }}</dd>
                </div>
                @if($calculator->formula_display)
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Formula</dt>
                        <dd class="font-mono text-xs text-slate-700 dark:text-slate-300">{{ $calculator->formula_display }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <x-public.related-resources :resources="$related" :currentType="'Calculator'" stacked />
    </aside>
</div>

@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebApplication",
    "name": "{{ $calculator->name }}",
    "description": "{{ $calculator->short_description ?? $calculator->name }}",
    "url": "{{ url()->current() }}",
    "applicationCategory": "HealthApplication",
    "dateModified": "{{ $calculator->updated_at->toIso8601String() }}",
    "offers": {
        "@@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
@endpush
