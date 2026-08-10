@extends('layouts.admin')

@section('title', 'Edit Tag - ' . config('app.name'))

@section('header', 'Edit Tag')

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card>
            <div class="space-y-4" x-data="{ slug: {{ Js::from(old('slug', $tag->slug)) }} }">
                <div>
                    <x-form.label for="name" required>Name</x-form.label>
                    <x-ui.input
                        id="name"
                        name="name"
                        value="{{ old('name', $tag->name) }}"
                        :error="$errors->first('name')"
                        aria-required="true"
                        @input="if (slug === '') slug = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                    />
                    <p class="mt-1 text-xs text-gray-500">Tags are normalized to lowercase.</p>
                </div>

                <div>
                    <x-form.label for="slug" required>Slug</x-form.label>
                    <x-ui.input
                        id="slug"
                        name="slug"
                        x-model="slug"
                        value="{{ old('slug', $tag->slug) }}"
                        :error="$errors->first('slug')"
                        aria-required="true"
                    />
                    <p class="mt-1 text-xs text-gray-500">Leave blank to regenerate from the name.</p>
                </div>
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <x-ui.button href="{{ route('admin.tags.index') }}" variant="ghost">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit">
                Update Tag
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
