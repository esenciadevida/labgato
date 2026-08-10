@extends('layouts.admin')

@section('title', 'Categories - ' . config('app.name'))

@section('header', 'Categories')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="flex gap-2" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search categories..."
                aria-label="Search categories"
            />
            <x-ui.button type="submit" variant="secondary">Search</x-ui.button>
        </form>

        @can('manage', \App\Models\Category::class)
            <x-ui.button href="{{ route('admin.categories.create') }}">
                New Category
            </x-ui.button>
        @endcan
    </div>

    <x-ui.card>
        @if($tree->isEmpty())
            <x-ui.empty-state
                title="No categories found"
                description="{{ $search !== '' ? 'No categories match your search.' : 'Create your first category to organize resources.' }}"
            >
                @can('manage', \App\Models\Category::class)
                    @if($search === '')
                        <x-slot:action>
                            <x-ui.button href="{{ route('admin.categories.create') }}">
                                New Category
                            </x-ui.button>
                        </x-slot:action>
                    @endif
                @endcan
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <ul class="divide-y divide-gray-200">
                    @include('admin.categories._tree', ['nodes' => $tree, 'depth' => 0])
                </ul>
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
