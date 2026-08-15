@props([
    'categories' => collect(),
    'tags' => collect(),
    'contributor' => null,
    'lastVerifiedAt' => null,
    'publishedAt' => null,
    'resourceType' => null,
    'variant' => 'default', // 'default' (light bg) or 'dark' (navy bg)
])

@php
    $hasContent = $categories->isNotEmpty() || $tags->isNotEmpty() || $contributor || $lastVerifiedAt || $publishedAt;
@endphp

@if($hasContent)
<div class="flex flex-col md:flex-row md:items-center gap-4 mb-6 {{ $variant === 'dark' ? 'pt-4 border-t border-white/20' : 'pt-6 border-t border-slate-200 dark:border-slate-700' }}">
    @if($categories->isNotEmpty())
    <div class="flex flex-wrap gap-2">
        @foreach($categories as $category)
            <a
                href="{{ $resourceType ? route("public.{$resourceType}.index", ['category' => $category->slug]) : '#' }}"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider border
                    {{ $variant === 'dark'
                        ? 'bg-white/10 text-white border-white/20 hover:bg-white/20'
                        : 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 border-primary-200 dark:border-slate-600' }}"
            >
                {{ str_replace('-', ' ', $category->name) }}
            </a>
        @endforeach
    </div>
    @endif

    @if($tags->isNotEmpty())
    <div class="flex flex-wrap gap-2">
        @foreach($tags as $tag)
            <a
                href="{{ $resourceType ? route("public.{$resourceType}.index", ['tag' => $tag->slug]) : '#' }}"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium uppercase tracking-wider border
                    {{ $variant === 'dark'
                        ? 'bg-white/10 text-white border-white/20 hover:bg-white/20'
                        : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600' }}"
            >
                #{{ $tag->name }}
            </a>
        @endforeach
    </div>
    @endif

    @if($contributor)
        <x-public.contributor-credit :contributor="$contributor" variant="{{ $variant }}" />
    @endif

    @if($lastVerifiedAt)
        <span class="text-sm {{ $variant === 'dark' ? 'text-white/70' : 'text-slate-500 dark:text-slate-400' }}">
            Last verified {{ $lastVerifiedAt->diffForHumans() }}
        </span>
    @endif

    @if($publishedAt)
        <span class="text-sm {{ $variant === 'dark' ? 'text-white/70' : 'text-slate-500 dark:text-slate-400' }}">{{ $publishedAt->format('M j, Y') }}</span>
    @endif
</div>
@endif
