@foreach($nodes as $node)
    <li>
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 {{ $depth > 0 ? 'bg-slate-50 dark:bg-slate-800' : 'bg-white dark:bg-slate-800' }}">
            <div class="flex min-w-0 items-center gap-3" style="padding-left: {{ $depth * 24 }}px;">
                @if($depth > 0)
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                    </svg>
                @endif
                <span class="font-medium text-slate-900 dark:text-slate-100">{{ $node->name }}</span>
                <code class="hidden text-sm text-slate-500 dark:text-slate-400 sm:inline">{{ $node->slug }}</code>
                @if(! $node->is_active)
                    <x-ui.badge variant="warning">Inactive</x-ui.badge>
                @endif
            </div>

            @can('manage', $node)
                <div class="flex items-center gap-2">
                    <x-ui.button href="{{ route('admin.categories.edit', $node) }}" variant="secondary" size="sm">
                        Edit
                    </x-ui.button>
                    <form method="POST" action="{{ route('admin.categories.destroy', $node) }}" class="inline-block" data-confirm="Are you sure you want to delete this category? This action cannot be undone.">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" size="sm">
                            Delete
                        </x-ui.button>
                    </form>
                </div>
            @endcan
        </div>

        @if($node->children->isNotEmpty())
            <ul class="border-t border-slate-200 dark:border-slate-700">
                @include('admin.categories._tree', ['nodes' => $node->children, 'depth' => $depth + 1])
            </ul>
        @endif
    </li>
@endforeach
