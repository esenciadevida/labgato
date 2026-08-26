@extends('layouts.admin')

@section('title', 'Edit Role - ' . config('app.name'))

@section('header', 'Edit Role')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.roles.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-ui.card>
            <div class="space-y-4">
                <div>
                    <x-form.label for="name" required>Name</x-form.label>
                    <x-ui.input
                        id="name"
                        name="name"
                        value="{{ old('name', $role->name) }}"
                        :error="$errors->first('name')"
                        aria-required="true"
                    />
                    <x-form.error for="name" />
                </div>

                <div>
                    <x-form.label for="slug" required>Slug</x-form.label>
                    <x-ui.input
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $role->slug) }}"
                        :error="$errors->first('slug')"
                        aria-required="true"
                    />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Used internally. Use lowercase letters and hyphens only.</p>
                    <x-form.error for="slug" />
                </div>

                <div>
                    <x-form.label for="description">Description</x-form.label>
                    <x-ui.textarea id="description" name="description" rows="3">{{ old('description', $role->description) }}</x-ui.textarea>
                    <x-form.error for="description" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Permissions">
            <x-admin.permission-matrix :grouped="$grouped" :selected="$role->permissions->pluck('id')->all()" />
            <x-form.error for="permissions" />
        </x-ui.card>

        <div class="flex items-center justify-end gap-3">
            <x-ui.button href="{{ route('admin.roles.index') }}" variant="ghost">
                Cancel
            </x-ui.button>
            <x-ui.button type="submit">
                Update Role
            </x-ui.button>
        </div>
    </form>
</div>
@endsection
