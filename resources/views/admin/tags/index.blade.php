@extends('layouts.admin')

@section('title', 'Tags - ' . config('app.name'))

@section('header', 'Tags')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.tags.index') }}" class="flex gap-2" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search tags..."
                aria-label="Search tags"
            />
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
        </form>

        @can('manage', \App\Models\Tag::class)
            <x-ui.button href="{{ route('admin.tags.create') }}">
                New Tag
            </x-ui.button>
        @endcan
    </div>

    <x-ui.card>
        @if($tags->isEmpty())
            <x-ui.empty-state
                title="No tags found"
                description="{{ $search !== '' ? 'No tags match your search.' : 'Create your first tag to label resources.' }}"
            >
                @can('manage', \App\Models\Tag::class)
                    @if($search === '')
                        <x-slot:action>
                            <x-ui.button href="{{ route('admin.tags.create') }}">
                                New Tag
                            </x-ui.button>
                        </x-slot:action>
                    @endif
                @endcan
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Slug</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 bg-white">
                        @foreach($tags as $tag)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900">{{ $tag->name }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-500"><code>{{ $tag->slug }}</code></td>
                                <td class="px-4 py-3 text-right">
                                    @can('manage', $tag)
                                        <div class="inline-flex items-center gap-2">
                                            <x-ui.button href="{{ route('admin.tags.edit', $tag) }}" variant="secondary" size="sm">
                                                Edit
                                            </x-ui.button>
                                            <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="danger" size="sm" onclick="return confirm('Are you sure you want to delete this tag?');">
                                                    Delete
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tags->hasPages())
                <div class="border-t border-neutral-200 px-4 py-3">
                    {{ $tags->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@endsection
