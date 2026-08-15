@props([
    'title',
    'type',
    'typeLabel' => null,
    'shortDescription' => null,
    'categories' => collect(),
    'tags' => collect(),
    'contributor' => null,
    'lastVerifiedAt' => null,
    'publishedAt' => null,
    'resourceType' => null,
])

<section class="bg-primary-950 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950 border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <x-public.breadcrumbs dark :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => ucfirst($type), 'url' => null],
            ['label' => $title, 'url' => null],
        ]" />

        <p class="eyebrow text-primary-300 mt-3">{{ ucfirst($typeLabel ?? $type) }}</p>

        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight text-white">
            {{ $title }}
        </h1>

        @if($shortDescription)
            <p class="mt-3 text-slate-300 max-w-2xl">
                {{ $shortDescription }}
            </p>
        @endif

        @if($categories->isNotEmpty() || $tags->isNotEmpty() || $contributor || $lastVerifiedAt || $publishedAt)
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @foreach($categories as $category)
                <a
                    href="{{ $resourceType ? route("public.{$resourceType}.index", ['category' => $category->slug]) : '#' }}"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-white/10 text-white text-xs font-medium uppercase tracking-wider border border-white/20 hover:bg-white/20 transition-colors"
                >
                    {{ str_replace('-', ' ', $category->name) }}
                </a>
            @endforeach

            @foreach($tags as $tag)
                <a
                    href="{{ $resourceType ? route("public.{$resourceType}.index", ['tag' => $tag->slug]) : '#' }}"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-white/10 text-white text-xs font-medium uppercase tracking-wider border border-white/20 hover:bg-white/20 transition-colors"
                >
                    #{{ $tag->name }}
                </a>
            @endforeach

            @if($contributor)
                <x-public.contributor-credit :contributor="$contributor" variant="dark" />
            @endif

            @if($lastVerifiedAt)
                <span class="text-xs text-slate-400">Last verified {{ $lastVerifiedAt->diffForHumans() }}</span>
            @endif

            @if($publishedAt)
                <span class="text-xs text-slate-400">{{ $publishedAt->format('M j, Y') }}</span>
            @endif
        </div>
        @endif

        @if(! empty($slot))
            <div class="mt-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
