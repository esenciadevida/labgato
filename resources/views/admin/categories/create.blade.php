@extends('layouts.admin')

@section('title', 'New Category - ' . config('app.name'))

@section('header', 'New Category')

@section('content')
<div class="mx-auto max-w-2xl">
    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-6">
        @csrf

        <x-ui.card>
            <div class="space-y-4">
                <div x-data="{ slug: '' }">
                    <div>
                        <x-form.label for="name" required>Name</x-form.label>
                        <x-ui.input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            :error="$errors->first('name')"
                            aria-required="true"
                            @input="if (slug === '') slug = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')"
                        />
                    </div>

                    <div class="mt-4">
                        <x-form.label for="slug" required>Slug</x-form.label>
                        <x-ui.input
                            id="slug"
                            name="slug"
                            x-model="slug"
                            value="{{ old('slug') }}"
                            :error="$errors->first('slug')"
                            aria-required="true"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auto-generated from the name. Leave blank to generate.</p>
                    </div>
                </div>

                <div>
                    <x-form.label for="parent_id">Parent category</x-form.label>
                    <x-ui.select id="parent_id" name="parent_id">
                        <option value="">No parent</option>
                        @foreach($parentOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('parent_id') == $option->id)>
                                {{ str_repeat('— ', max(0, $option->depth)) }}{{ $option->name }}
                            </option>
                        @endforeach
                    </x-ui.select>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave empty for a top-level category.</p>
                </div>

                <div>
                    <x-form.label for="description">Description</x-form.label>
                    <x-ui.textarea id="description" name="description" rows="4">{{ old('description') }}</x-ui.textarea>
                    <x-form.error for="description" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-form.label for="sort_order">Sort order</x-form.label>
                        <x-ui.input
                            type="number"
                            id="sort_order"
                            name="sort_order"
                            value="{{ old('sort_order', 0) }}"
                            min="0"
                            :error="$errors->first('sort_order')"
                        />
                    </div>

                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked(old('is_active', true))
                                class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"
                            >
                            Active
                        </label>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <x-ui.button href="{{ route('admin.categories.index') }}" variant="ghost">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit">
                Create Category
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
