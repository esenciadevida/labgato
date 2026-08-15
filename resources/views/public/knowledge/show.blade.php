@extends('layouts.public')

@section('content')
<x-public.resource-hero :title="$knowledge->name" :type="'Knowledge'" :typeLabel="'Knowledge'" :shortDescription="$knowledge->short_description" :categories="$knowledge->categories" :tags="$knowledge->tags" :contributor="$knowledge->contributor" :publishedAt="$knowledge->published_at" resourceType="knowledge">
    <x-public.share-button :url="url()->current()" />
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-6">

                @if($knowledge->featured_image)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($knowledge->featured_image) }}"
                        alt="{{ $knowledge->name }}"
                        loading="lazy"
                        class="w-full h-auto rounded-xl shadow-card border border-slate-200 dark:border-slate-700"
                    >
                @endif

                @if($knowledge->learning_objectives)
                    <x-ui.card>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Learning Objectives</h2>
                        <div class="rich-content">
                            {!! $learningObjectives !!}
                        </div>
                    </x-ui.card>
                @endif

                <x-ui.card>
                    <div class="rich-content max-w-3xl">
                        {!! $content !!}
                    </div>
                </x-ui.card>
            </div>

            <aside class="lg:col-span-4 space-y-6">
                <x-public.table-of-contents content-selector="#article-content" />

                <x-ui.card>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Details</h2>
                    <dl class="space-y-3 text-sm">
                        @if($knowledge->topic_category)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Topic</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $knowledge->topic_category }}</dd>
                            </div>
                        @endif
                        @if($knowledge->difficulty_level)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Level</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $knowledge->difficulty_level }}</dd>
                            </div>
                        @endif
                        @if($knowledge->reading_time_minutes)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Reading time</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $knowledge->reading_time_minutes }} min</dd>
                            </div>
                        @endif
                        @if($knowledge->author_name)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Author</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $knowledge->author_name }}</dd>
                            </div>
                        @endif
                        @if($knowledge->published_at)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Published</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $knowledge->published_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>

                @if($knowledge->tags->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Tags</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach($knowledge->tags as $tag)
                                <li>
                                    <a href="{{ route('public.knowledge.index', ['tag' => $tag->slug]) }}" class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        #{{ $tag->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                <x-public.related-resources :resources="$related" :currentType="'Knowledge'" stacked />
            </aside>
        </div>
    </div>

@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "MedicalScholarlyArticle",
    "name": "{{ $knowledge->name }}",
    "headline": "{{ $knowledge->name }}",
    "description": "{{ $knowledge->short_description ?? $knowledge->name }}",
    "url": "{{ url()->current() }}",
    "author": @if($knowledge->contributor && $knowledge->contributor->is_active)
    {
        "@type": "Person",
        "name": "{{ $knowledge->contributor->name }}",
        "url": "{{ route('contributors.show', $knowledge->contributor->profile_slug) }}"
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