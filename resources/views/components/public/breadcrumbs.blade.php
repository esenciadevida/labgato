@props(['items' => [], 'dark' => false])

@php
    $muted = $dark ? 'text-white/60' : 'text-slate-500 dark:text-slate-400';
    $sep = $dark ? 'text-white/30' : 'text-slate-400';
    $last = $dark ? 'text-white' : 'text-slate-900 dark:text-slate-100';
    $link = $dark ? 'hover:text-white' : 'hover:text-primary-700';
@endphp

@if(count($items) > 0)
    @php
        $crumbs = [];
        foreach ($items as $index => $item) {
            $crumbs[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => isset($item['url']) ? $item['url'] : url()->current(),
            ];
        }

        // Concatenated to avoid the @context string being compiled
        // as Laravel's @context Blade directive.
        $contextKey = '@' . 'context';
    @endphp
    <script type="application/ld+json">{!! json_encode([
        $contextKey => 'https://schema.org',
        '@'.'type' => 'BreadcrumbList',
        'itemListElement' => $crumbs,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <nav aria-label="Breadcrumb" class="py-3">
        <ol class="flex items-center flex-wrap gap-1 text-sm {{ $muted }}">
            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if($index > 0)
                        <svg class="w-4 h-4 mx-1 {{ $sep }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif
                    @if(isset($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="{{ $link }}">{{ $item['label'] }}</a>
                    @else
                        <span class="{{ $last }} font-medium" @if($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
