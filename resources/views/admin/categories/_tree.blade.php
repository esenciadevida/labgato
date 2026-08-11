@foreach($nodes as $node)
    <li>
        <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 {{ $depth > 0 ? 'bg-neutral-50' : 'bg-white' }}">
            <div class="flex min-w-0 items-center gap-3" style="padding-left: {{ $depth * 24 }}px;">
                @if($depth > 0)
                    <svg class="h-4 w-4 shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/>
                    </svg>
                @endif
                <span class="font-medium text-neutral-900">{{ $node->name }}</span>
                <code class="hidden text-sm text-neutral-500 sm:inline">{{ $node->slug }}</code>
                @if(! $node->is_active)
                    <x-ui.badge variant="warning">Inactive</x-ui.badge>
                @endif
            </div>

            @can('manage', $node)
                <div class="flex items-center gap-2">
                    <x-ui.button href="{{ route('admin.categories.edit', $node) }}" variant="secondary" size="sm">
                        Edit
                    </x-ui.button>
                    <form method="POST" action="{{ route('admin.categories.destroy', $node) }}" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger" size="sm" onclick="return confirm('Are you sure you want to delete this category?');">
                            Delete
                        </x-ui.button>
                    </form>
                </div>
            @endcan
        </div>

        @if($node->children->isNotEmpty())
            <ul class="border-t border-neutral-200">
                @include('admin.categories._tree', ['nodes' => $node->children, 'depth' => $depth + 1])
            </ul>
        @endif
    </li>
@endforeach
