<div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
    @if(! $resource->isPublished())
        @can('update', $resource)
            <form method="POST" action="{{ route($route.'.publish', $resource) }}" class="inline-block">
                @csrf
                @method('PATCH')
                <x-ui.button type="submit" variant="success" size="sm" title="Publish this {{ $label }}" aria-label="Publish this {{ $label }}" class="w-8 h-8 px-0! py-0!">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </x-ui.button>
            </form>
        @endcan
    @else
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-success-50 text-success-700 border border-success-600/20 dark:bg-success-900/20 dark:text-success-400 dark:border-success-600/30" title="Published" role="img" aria-label="Published">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </span>
    @endif
    @if(! $resource->isArchived())
        @can('update', $resource)
            <form method="POST" action="{{ route($route.'.archive', $resource) }}" class="inline-block">
                @csrf
                @method('PATCH')
                <x-ui.button type="submit" variant="secondary" size="sm" title="Archive this {{ $label }}" aria-label="Archive this {{ $label }}" class="w-8 h-8 px-0! py-0!">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2M5 15a2 2 0 012 2h6a2 2 0 012-2m-13-5h14"/>
                    </svg>
                </x-ui.button>
            </form>
        @endcan
    @else
        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-warning-50 text-warning-700 border border-warning-600/20 dark:bg-warning-900/20 dark:text-warning-400 dark:border-warning-600/30" title="Archived" role="img" aria-label="Archived">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
        </span>
    @endif
    @can('update', $resource)
        <x-ui.button href="{{ route($route.'.edit', $resource) }}" variant="secondary" size="sm" title="Edit this {{ $label }}" aria-label="Edit this {{ $label }}" class="w-8 h-8 px-0! py-0!">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </x-ui.button>
    @endcan
    @can('delete', $resource)
        <form method="POST" action="{{ route($route.'.destroy', $resource) }}" class="inline-block" data-confirm="Are you sure you want to delete this {{ $label }}? This action cannot be undone.">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="danger" size="sm" title="Delete this {{ $label }}" aria-label="Delete this {{ $label }}" class="w-8 h-8 px-0! py-0!">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </x-ui.button>
        </form>
    @endcan
</div>
