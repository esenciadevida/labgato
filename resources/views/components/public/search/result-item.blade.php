@props(['result'])

<div class="bg-white rounded-lg shadow-card border border-neutral-200 p-5 hover:border-primary-300 hover:shadow-card transition-shadow">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-neutral-900 mb-1">
                <a href="{{ $result->url }}" class="text-primary-700 hover:text-primary-800 transition-colors">{{ $result->title }}</a>
            </h3>

            @if($result->description)
                <p class="text-neutral-600 text-sm leading-relaxed">{{ $result->description }}</p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-neutral-500">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-50 text-primary-700 border border-primary-600/20 font-medium uppercase tracking-wider">
                    {{ ucfirst($result->type) }}
                </span>

                @if($result->categoryName)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-700 border border-neutral-200">
                        {{ $result->categoryName }}
                    </span>
                @endif

                @if($result->publishedAt)
                    <time datetime="{{ $result->publishedAt->format('Y-m-d') }}">
                        {{ $result->publishedAt->format('M j, Y') }}
                    </time>
                @endif
            </div>

            @if(count($result->tags))
                <ul class="mt-2 flex flex-wrap gap-1.5" aria-label="Tags">
                    @foreach($result->tags as $tag)
                        <li class="inline-flex items-center px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600 border border-neutral-200 text-xs">
                            #{{ $tag }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
