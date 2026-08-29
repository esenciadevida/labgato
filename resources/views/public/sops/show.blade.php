@extends('layouts.public')

@section('content')
<x-public.resource-hero
    :title="$sop->name"
    type="SOPs"
    typeLabel="SOP"
    :shortDescription="$sop->short_description"
    :categories="$sop->categories"
    :tags="$sop->tags"
    :contributor="$sop->contributor"
    :lastVerifiedAt="$sop->last_verified_at"
    :publishedAt="$sop->published_at"
    :coverImage="$sop->featured_image_url"
    :readingTime="$readingTime"
    resourceType="sops"
>
    <x-public.share-button :url="url()->current()" />
    <button type="button" onclick="window.print()" class="no-print inline-flex items-center gap-1.5 rounded-lg border border-white/25 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-white/10">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5z" />
        </svg>
        Print
    </button>
</x-public.resource-hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    @if($heroImageSrc)
        <figure class="mx-auto mb-8 max-w-3xl overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 shadow-card">
            <x-public.img
                :src="$heroImageSrc"
                :alt="$heroImageAlt ?? ''"
                :eager="true"
                :priority="true"
                class="w-full h-auto"
            />
        </figure>
    @endif
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
        <div class="lg:col-span-8 space-y-6">

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-700 dark:bg-slate-900">
                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                        Document Control
                    </div>
                    <dl class="grid grid-cols-2 divide-x divide-slate-200 sm:grid-cols-4 dark:divide-slate-700">
                        <div class="px-4 py-3">
                            <dt class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Version</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $sop->version ?: '1.0' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Effective</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $sop->published_at?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Reviewed</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ ($sop->reviewed_date ?? $sop->last_reviewed_at)?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div class="px-4 py-3">
                            <dt class="text-[11px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Department</dt>
                            <dd class="mt-0.5 truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $sop->department ?: 'General' }}</dd>
                        </div>
                    </dl>
                </div>

                @if($sop->principle)
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-400">
                            Principle
                        </div>
                        <div class="rich-content px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                            {!! \App\Support\RichTextSanitizer::render($sop->principle) !!}
                        </div>
                    </div>
                @endif

                <article id="article-content" class="rich-content max-w-3xl">
                    {!! $content !!}
                </article>

                @if($sop->referenceRanges->isNotEmpty())
                    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-2 dark:border-slate-700 dark:bg-slate-800/60">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Reference Ranges</h2>
                            <a href="{{ route('public.reference-ranges.index') }}" class="text-xs font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                Browse all ranges
                            </a>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($sop->referenceRanges->sortBy('analyte_name') as $rangeRef)
                                @php
                                    $refLower = $rangeRef->lower_limit;
                                    $refUpper = $rangeRef->upper_limit;
                                    $refFmt = fn ($value) => $value === null ? null : (floor($value) == $value ? number_format($value, 0, '.', '') : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.'));
                                    $refLabel = trim(($refLower !== null ? $refFmt($refLower) : '') . ($refLower !== null && $refUpper !== null ? '–' : '') . ($refUpper !== null ? $refFmt($refUpper) : ''));
                                @endphp
                                <div class="flex items-start justify-between gap-4 px-4 py-3">
                                    <div>
                                        <a href="{{ route('public.reference-ranges.show', $rangeRef->slug) }}" class="text-sm font-semibold text-slate-900 hover:text-primary-700 dark:text-slate-100 dark:hover:text-primary-400">
                                            {{ $rangeRef->name }}
                                        </a>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            {{ collect([$rangeRef->population_group, $rangeRef->sex ? ucfirst($rangeRef->sex) : null])->filter()->implode(' · ') }}
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $refLabel }}
                                            @if($rangeRef->unit)
                                                <span class="font-normal text-slate-500 dark:text-slate-400">{{ $rangeRef->unit }}</span>
                                            @endif
                                        </p>
                                        @if($rangeRef->critical_low !== null || $rangeRef->critical_high !== null)
                                            <p class="mt-0.5 text-[11px] font-semibold text-amber-700 dark:text-amber-300">Critical values apply</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <x-public.prev-next
                    :previous="$previous"
                    :next="$next"
                    :previous-url="$previous ? route('public.sops.show', $previous->slug) : null"
                    :next-url="$next ? route('public.sops.show', $next->slug) : null"
                />

                @if($revisions->isNotEmpty())
                    <x-ui.card>
                        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Revision History</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                        <th scope="col" class="py-2 pr-4 font-medium">Version</th>
                                        <th scope="col" class="py-2 pr-4 font-medium">Date</th>
                                        <th scope="col" class="py-2 font-medium">Summary of changes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($revisions as $revision)
                                        <tr>
                                            <td class="py-2 pr-4 font-medium text-slate-900 dark:text-slate-100">{{ $revision->version_number }}</td>
                                            <td class="py-2 pr-4 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $revision->created_at->format('M j, Y') }}</td>
                                            <td class="py-2 text-slate-600 dark:text-slate-300">{{ $revision->change_summary ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card>
                @endif

                <x-public.faq-section :faq="$sop->faq" />
        </div>

        <aside class="lg:col-span-4 space-y-6">
                <x-public.eeat-trust-box :resource="$sop" />

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
