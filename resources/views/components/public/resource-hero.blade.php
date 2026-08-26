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
    'coverImage' => null,
])

<section class="relative bg-primary-950 text-white overflow-hidden">
    @if($coverImage)
        <img
            src="{{ $coverImage }}"
            alt=""
            class="absolute inset-0 h-full w-full object-cover"
            loading="eager"
            fetchpriority="high"
        >
        <div class="absolute inset-0 bg-linear-to-t from-primary-950 via-primary-950/60 to-primary-950/20"></div>
        <div class="absolute inset-0 bg-linear-to-r from-primary-950/40 to-transparent"></div>
    @else
        <div class="absolute inset-0 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950"></div>
    @endif

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
        <x-public.breadcrumbs dark :items="[
            ['label' => 'Home', 'url' => route('home')],
            ['label' => ucfirst($type), 'url' => null],
            ['label' => $title, 'url' => null],
        ]" />

        <p class="eyebrow text-primary-300 mt-4">{{ ucfirst($typeLabel ?? $type) }}</p>

        <h1 class="mt-3 font-display text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-white max-w-4xl leading-tight">
            {{ $title }}
        </h1>

        @if($shortDescription)
            <p class="mt-4 text-lg text-slate-300 max-w-3xl leading-relaxed">
                {{ $shortDescription }}
            </p>
        @endif

        @if($contributor || $publishedAt || $lastVerifiedAt || $categories->isNotEmpty() || $tags->isNotEmpty() || !empty($slot))
        <div class="mt-6 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
            @if($contributor)
                <div class="flex items-center gap-2">
                    <x-public.avatar :name="$contributor->name" :user-id="$contributor->id" size="sm" />
                    @if($contributor->is_active)
                        <a href="{{ route('contributors.show', $contributor->profile_slug) }}" class="font-medium text-white hover:text-primary-300 transition-colors">Contributed by {{ $contributor->name }}</a>
                    @else
                        <span class="font-medium text-white">Contributed by {{ $contributor->name }}</span>
                    @endif
                </div>
                <span class="text-white/30" aria-hidden="true">&middot;</span>
            @endif

            @if($publishedAt)
                <time datetime="{{ $publishedAt->toIso8601String() }}" class="text-slate-300">
                    {{ $publishedAt->format('M j, Y') }}
                </time>
            @endif

            @if($lastVerifiedAt)
                <span class="text-slate-400">Last verified {{ $lastVerifiedAt->diffForHumans() }}</span>
                <span class="text-white/30" aria-hidden="true">&middot;</span>
            @endif

            @foreach($categories as $category)
                <a
                    href="{{ $resourceType ? route("public.{$resourceType}.index", ['category' => $category->slug]) : '#' }}"
                    class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium border border-white/20 hover:bg-white/20 transition-colors"
                >
                    {{ str_replace('-', ' ', $category->name) }}
                </a>
            @endforeach

            @foreach($tags as $tag)
                <a
                    href="{{ $resourceType ? route("public.{$resourceType}.index", ['tag' => $tag->slug]) : '#' }}"
                    class="inline-flex items-center rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium border border-white/20 hover:bg-white/20 transition-colors"
                >
                    #{{ $tag->name }}
                </a>
            @endforeach

            @if(!empty($slot))
                <span class="text-white/30" aria-hidden="true">&middot;</span>
                {{ $slot }}
            @endif
        </div>
        @endif
    </div>
</section>
