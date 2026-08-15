@props(['resources'])

<div class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">Recommended for you</h2>
        </div>

        @if($resources->isEmpty())
            <x-ui.empty-state
                title="No recommendations yet"
                description="Start exploring resources to get personalized recommendations."
            />
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($resources as $resource)
                    <a href="{{ $resource->url ?? '#' }}" class="group block bg-white rounded-xl shadow-card border border-slate-200 dark:bg-slate-800 dark:border-slate-700 p-6 hover:border-primary-300 hover:shadow-card-hover transition flex flex-col">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-xs font-medium mb-3 w-fit">
                            {{ class_basename($resource->resource_type ?? 'Resource') }}
                        </span>
                        <h3 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100 mb-1 group-hover:text-primary-700 transition-colors">
                            {{ $resource->name }}
                        </h3>
                        @if($resource->short_description)
                            <p class="text-sm text-slate-600 dark:text-slate-300 line-clamp-2">{{ $resource->short_description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
