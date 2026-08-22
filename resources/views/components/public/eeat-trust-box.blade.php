@props(['resource'])

@php
    $authorName = $resource->display_author_name ?? ($resource->author_name ?? null);
    $reviewerName = $resource->reviewer_name;
    $credentials = $resource->reviewer_credentials;
    $lastReviewed = $resource->last_reviewed_at;
    $publishedAt = $resource->published_at;

    $hasAny = $authorName || $reviewerName || $lastReviewed || $publishedAt;
@endphp

@if($hasAny)
    <aside class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5" aria-label="Editorial and review information">
        <h2 class="flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
            Reviewed &amp; Trusted
        </h2>

        <dl class="mt-4 space-y-3 text-sm">
            @if($authorName)
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Author</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">{{ $authorName }}</dd>
                </div>
            @endif

            @if($reviewerName)
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Reviewed by</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100 text-right">
                        {{ $reviewerName }}@if($credentials)<span class="block text-xs font-normal text-slate-500 dark:text-slate-400">{{ $credentials }}</span>@endif
                    </dd>
                </div>
            @endif

            @if($lastReviewed)
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Last reviewed</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $lastReviewed->format('M j, Y') }}</dd>
                </div>
            @elseif($publishedAt)
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-slate-500 dark:text-slate-400">Published</dt>
                    <dd class="font-medium text-slate-900 dark:text-slate-100">{{ $publishedAt->format('M j, Y') }}</dd>
                </div>
            @endif
        </dl>

        <p class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
            Content on Labgoto is provided for educational purposes only and is not a substitute for professional medical advice, diagnosis, or treatment.
        </p>
    </aside>
@endif
