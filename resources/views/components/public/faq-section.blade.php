@props(['faq'])

@php
    $items = collect(is_array($faq) ? $faq : [])
        ->filter(fn ($item) => trim((string) ($item['question'] ?? '')) !== '' && trim((string) ($item['answer'] ?? '')) !== '')
        ->values();
@endphp

@if($items->isNotEmpty())
    <section class="mt-8" aria-labelledby="faq-heading" x-data="{ open: 0 }">
        <h2 id="faq-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
            Frequently Asked Questions
        </h2>

        <div class="divide-y divide-slate-200 dark:divide-slate-700 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
            @foreach($items as $index => $item)
                <div>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-medium text-slate-900 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors"
                        :aria-expanded="(open === {{ $index }}).toString()"
                        aria-controls="faq-panel-{{ $index }}"
                        @click="open = open === {{ $index }} ? null : {{ $index }}"
                    >
                        <span>{{ $item['question'] }}</span>
                        <svg
                            class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="open === {{ $index }} ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    <div
                        id="faq-panel-{{ $index }}"
                        x-show="open === {{ $index }}"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="px-5 pb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300"
                    >
                        {!! nl2br(e($item['answer'])) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    @push('head')
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $items->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($item['answer']),
                ],
            ])->values()->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
@endif
