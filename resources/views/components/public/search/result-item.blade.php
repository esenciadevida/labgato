@props(['result', 'query' => ''])

@php
    $typeIcons = [
        'calculator' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'tool' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'reference_range' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'interpretation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',
        'knowledge' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
        'sop' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
    ];

    $typeColors = [
        'calculator' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
        'tool' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800',
        'reference_range' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
        'interpretation' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
        'knowledge' => 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-800',
        'sop' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800',
    ];

    $typeLabels = [
        'calculator' => 'Calculator',
        'tool' => 'Tool',
        'reference_range' => 'Reference Range',
        'interpretation' => 'Interpretation',
        'knowledge' => 'Knowledge',
        'sop' => 'SOP',
    ];

    $icon = $typeIcons[$result->type] ?? $typeIcons['knowledge'];
    $color = $typeColors[$result->type] ?? 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700';
    $label = $typeLabels[$result->type] ?? ucfirst($result->type);
@endphp

<article class="bg-white dark:bg-slate-800 rounded-xl shadow-card border border-slate-200 dark:border-slate-700 p-5 hover:border-primary-300 hover:shadow-card-hover transition-shadow">
    <div class="flex items-start gap-4">
        {{-- Type icon --}}
        <div class="shrink-0 mt-0.5">
            <div class="w-10 h-10 rounded-lg {{ $color }} border flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">{!! $icon !!}</svg>
            </div>
        </div>

        <div class="flex-1 min-w-0">
            {{-- Title --}}
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1 leading-snug">
                <a href="{{ $result->url }}" class="text-primary-700 hover:text-primary-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded">
                    {!! $query ? \App\Support\SearchResult::highlight($query, $result->title) : e($result->title) !!}
                </a>
            </h3>

            {{-- Description --}}
            @if($result->description)
                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed line-clamp-2">
                    {!! $query ? \App\Support\SearchResult::highlight($query, $result->description) : e($result->description) !!}
                </p>
            @endif

            {{-- Metadata row --}}
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                <span                     class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $color }} border font-medium uppercase tracking-wider">
                    {{ $label }}
                </span>

                @if($result->categoryName)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                        {{ $result->categoryName }}
                    </span>
                @endif

                @if($result->publishedAt)
                    <time datetime="{{ $result->publishedAt->format('Y-m-d') }}" class="text-slate-400">
                        {{ $result->publishedAt->format('M j, Y') }}
                    </time>
                @endif
            </div>

            {{-- Tags --}}
            @if(count($result->tags))
                <ul class="mt-2 flex flex-wrap gap-1.5" aria-label="Tags">
                    @foreach($result->tags as $tag)
                        <li class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-xs">
                            #{{ $tag }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</article>
