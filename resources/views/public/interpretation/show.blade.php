@extends('layouts.public')

@section('content')
<x-public.resource-hero
    :title="$interpretation->name"
    :type="'Interpretations'"
    :typeLabel="'Interpretation'"
    :shortDescription="$interpretation->short_description"
    :categories="$interpretation->categories"
    :tags="$interpretation->tags"
    :contributor="$interpretation->contributor"
    :publishedAt="$interpretation->published_at"
    resourceType="interpretation"
>
    <x-public.share-button :url="url()->current()" />
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-6">

                @if($interpretation->featured_image)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($interpretation->featured_image) }}"
                        alt="{{ $interpretation->name }}"
                        loading="lazy"
                        class="w-full h-auto rounded-xl shadow-card border border-slate-200 dark:border-slate-700"
                    >
                @endif

                <x-public.interpretation-disclaimer :text="$interpretation->mandatory_disclaimer" />

                @if($interpretation->toc_headings)
                    <x-public.table-of-contents content-selector="#article-content" />
                @endif

                <x-ui.card>
                    <div class="rich-content max-w-3xl">
                        {!! $content !!}
                    </div>
                </x-ui.card>

                @if($interpretation->clinical_context)
                    <x-ui.card>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Clinical Context</h2>
                        <div class="rich-content">
                            {!! nl2br(e($interpretation->clinical_context)) !!}
                        </div>
                    </x-ui.card>
                @endif

                @if(is_array($interpretation->related_analytes) && count($interpretation->related_analytes) > 0)
                    <x-ui.card>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Related Analytes</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach($interpretation->related_analytes as $analyte)
                                <li>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-semibold">
                                        {{ $analyte }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif
            </div>

            <aside class="lg:col-span-4 space-y-6">
                <x-ui.card>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Details</h2>
                    <dl class="space-y-3 text-sm">
                        @if($interpretation->reading_time_minutes)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Reading time</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $interpretation->reading_time_minutes }} min</dd>
                            </div>
                        @endif
                        @if($interpretation->author_name)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Author</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $interpretation->author_name }}</dd>
                            </div>
                        @endif
                        @if($interpretation->published_at)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Published</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $interpretation->published_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>

                @if($interpretation->tags->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Tags</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach($interpretation->tags as $tag)
                                <li>
                                    <a href="{{ route('public.interpretation.index', ['tag' => $tag->slug]) }}" class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        #{{ $tag->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                <x-public.related-resources :resources="$related" :currentType="'Interpretation'" stacked />
            </aside>
        </div>
    </div>

@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "MedicalScholarlyArticle",
    "name": "{{ $interpretation->name }}",
    "headline": "{{ $interpretation->name }}",
    "description": "{{ $interpretation->short_description ?? $interpretation->name }}",
    "url": "{{ url()->current() }}",
    "author": @if($interpretation->contributor && $interpretation->contributor->is_active)
    {
        "@type": "Person",
        "name": "{{ $interpretation->contributor->name }}",
        "url": "{{ route('contributors.show', $interpretation->contributor->profile_slug) }}"
    }
    @else
    {
        "@type": "Organization",
        "name": "{{ config('app.name') }}"
    }
    @endif
}
</script>
@endpush