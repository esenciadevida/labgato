@extends('layouts.public')

@section('content')
<x-public.resource-hero
    :title="$tool->name"
    :type="'Laboratory Tools'"
    :typeLabel="'Laboratory Tool'"
    :shortDescription="$tool->short_description"
    :categories="$tool->categories"
    :tags="$tool->tags"
    :publishedAt="$tool->published_at"
    resourceType="tools"
>
    <x-public.share-button :url="url()->current()" />
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        <x-ui.card>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Calculate</h2>
            <livewire:public-site.tools.tool-runner :tool="$tool" />

            @if($tool->formula_display)
                <x-ui.card class="mt-6">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Formula</h2>
                    <p class="text-base font-medium text-slate-900 dark:text-slate-100">{{ $tool->formula_display }}</p>
                </x-ui.card>
            @endif

            @if($tool->interpretation_notes)
                <x-ui.card class="mt-6">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Notes</h2>
                    <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300">
                        {!! nl2br(e($tool->interpretation_notes)) !!}
                    </div>
                </x-ui.card>
            @endif

@php
                    $referenceList = json_decode((string) $tool->references, true);
                @endphp
                @if(is_array($referenceList) && count($referenceList))
                    <x-ui.card class="mt-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">References</h2>
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
                    </x-ui.card>
                @elseif(trim((string) $tool->references) !== '' && trim((string) $tool->references) !== '[]')
                    <x-ui.card class="mt-6">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">References</h2>
                        <div class="prose prose-sm max-w-none text-slate-700 whitespace-pre-line dark:text-slate-300">
                            {!! nl2br(e($tool->references)) !!}
                        </div>
                    </x-ui.card>
                @endif
            </x-ui.card>
        </div>

        <aside class="space-y-6">
            @if($tool->tags->isNotEmpty())
                <x-ui.card>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Tags</h2>
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach($tool->tags as $tag)
                            <li>
                                <a
                                    href="{{ route('public.tools.index', ['tag' => $tag->slug]) }}"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors"
                                >
                                    #{{ $tag->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif

            <x-public.related-resources :resources="$related" :currentType="'Laboratory Tool'" stacked />
        </aside>
    </div>
</div>

@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "WebApplication",
    "name": "{{ $tool->name }}",
    "description": "{{ $tool->short_description ?? $tool->name }}",
    "url": "{{ url()->current() }}",
    "applicationCategory": "BusinessApplication",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
}
</script>
@endpush