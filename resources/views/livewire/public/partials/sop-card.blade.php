@props(['resource'])

<a href="{{ route('public.sops.show', $resource->slug) }}" class="group block bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition flex flex-col">
    <div class="flex flex-wrap items-center gap-2 mb-3">
        @if($resource->department)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-semibold">
                {{ $resource->department }}
            </span>
        @endif
        @if($resource->version)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-medium">
                {{ $resource->version }}
            </span>
        @endif
    </div>

    <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100 mb-1">
        <span class="text-primary-700 group-hover:text-primary-800 transition-colors">
            {{ $resource->name }}
        </span>
    </h2>

    @if($resource->short_description)
        <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">{{ $resource->short_description }}</p>
    @endif

    <div class="mt-auto pt-4 flex flex-wrap items-center gap-2">
        @if($resource->reviewed_date)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 text-xs font-medium">
                Reviewed {{ $resource->reviewed_date->format('M Y') }}
            </span>
        @endif
        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 uppercase tracking-wider ml-auto">
            Open
            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </span>
    </div>
</a>
