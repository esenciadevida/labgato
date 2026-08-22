@extends('layouts.public')

@section('content')
<x-public.resource-hero
    :title="$sop->name"
    :type="'SOPs'"
    :typeLabel="'SOP'"
    :shortDescription="$sop->short_description"
    :categories="$sop->categories"
    :tags="$sop->tags"
    :contributor="$sop->contributor"
    :lastVerifiedAt="$sop->last_verified_at"
    :publishedAt="$sop->published_at"
    resourceType="sops"
>
    <x-public.share-button :url="url()->current()" />
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-6">

                @if($sop->featured_image)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($sop->featured_image) }}"
                        alt="{{ $sop->name }}"
                        loading="lazy"
                        class="w-full h-auto rounded-xl shadow-card border border-slate-200 dark:border-slate-700"
                    >
                @endif

                <x-ui.card>
                    <div class="rich-content">
                        {!! $content !!}
                    </div>
                </x-ui.card>

                <x-public.faq-section :faq="$sop->faq" />
        </div>

        <aside class="lg:col-span-4 space-y-6">
                <x-public.eeat-trust-box :resource="$sop" />

                <x-ui.card>
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Details</h2>
                    <dl class="space-y-3 text-sm">
                        @if($sop->department)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Department</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $sop->department }}</dd>
                            </div>
                        @endif
                        @if($sop->version)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Version</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $sop->version }}</dd>
                            </div>
                        @endif
                        @if($sop->reviewed_date)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Reviewed</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $sop->reviewed_date->format('M j, Y') }}</dd>
                            </div>
                        @endif
                        @if($sop->published_at)
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-slate-500 dark:text-slate-400">Published</dt>
                                <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $sop->published_at->format('M j, Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-3">Disclaimer</h2>
                    <div class="prose prose-sm max-w-none text-slate-700 dark:text-slate-300">
                        {{ $sop->mandatory_disclaimer ?: 'Standard operating procedures are provided for educational and informational purposes only. Always follow your institution\'s approved procedures and current regulatory requirements.' }}
                    </div>
                </x-ui.card>

                @if($sop->tags->isNotEmpty())
                    <x-ui.card>
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Tags</h2>
                        <ul class="flex flex-wrap gap-1.5">
                            @foreach($sop->tags as $tag)
                                <li>
                                    <a href="{{ route('public.sops.index', ['tag' => $tag->slug]) }}" class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs font-medium hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        #{{ $tag->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.card>
                @endif

                <x-public.related-resources :resources="$related" :currentType="'SOP'" stacked />
            </aside>
        </div>
    </div>
</div>

@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "MedicalScholarlyArticle",
    "name": "{{ $sop->name }}",
    "headline": "{{ $sop->name }}",
    "description": "{{ $sop->effectiveSeoDescription() }}",
    "url": "{{ url()->current() }}",@if($sop->published_at)
    "datePublished": "{{ $sop->published_at->toIso8601String() }}",@endif
@if($sop->updated_at)
    "dateModified": "{{ $sop->updated_at->toIso8601String() }}",@endif
    "author": @if($sop->contributor && $sop->contributor->is_active)
    {
        "@type": "Person",
        "name": "{{ $sop->contributor->name }}",
        "url": "{{ route('contributors.show', $sop->contributor->profile_slug) }}"
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