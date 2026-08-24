@extends('layouts.admin')

@section('title', 'Question Subjects - ' . config('app.name'))

@section('header', 'Question Subjects')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif

    <x-ui.card title="Add subject">
        <form method="POST" action="{{ route('admin.question-categories.store') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <x-form.label for="name">Name</x-form.label>
                <input type="text" id="name" name="name" required maxlength="255"
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    value="{{ old('name') }}">
                <x-form.error for="name" />
            </div>
            <div class="sm:w-32">
                <x-form.label for="sort_order">Sort</x-form.label>
                <input type="number" id="sort_order" name="sort_order" min="0" max="9999"
                    class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    value="{{ old('sort_order', 0) }}">
                <x-form.error for="sort_order" />
            </div>
            <div>
                <x-ui.button type="submit">Add subject</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Name</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Slug</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Questions</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sort</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                    @forelse($categories as $category)
                        <tr x-data="{ editing: false }">
                            <td class="px-4 py-3">
                                <span x-show="!editing" class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $category->name }}</span>
                                <form method="POST" action="{{ route('admin.question-categories.update', $category) }}" x-show="editing" x-cloak class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $category->name }}" required maxlength="255"
                                        aria-label="Subject name"
                                        class="block w-48 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <input type="number" name="sort_order" value="{{ $category->sort_order }}" min="0" max="9999"
                                        aria-label="Sort order"
                                        class="block w-20 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100">
                                    <button type="submit" class="rounded-lg bg-primary-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-600">Save</button>
                                </form>
                            </td>
                            <td class="px-4 py-3"><code class="text-xs text-slate-500 dark:text-slate-400">{{ $category->slug }}</code></td>
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $category->questions_count }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $category->sort_order }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $category->is_active ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="editing = ! editing"
                                        class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-slate-700"
                                        :aria-expanded="editing.toString()" title="Edit subject" aria-label="Edit {{ $category->name }}">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    @if($category->questions_count > 0 && $category->is_active)
                                        <span title="Has active questions — cannot be deactivated" class="inline-flex items-center rounded-lg p-2 text-slate-300 dark:text-slate-600" aria-hidden="true">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </span>
                                        <span class="sr-only">This subject has active questions and cannot be deactivated.</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.question-categories.toggle', $category) }}" class="inline-block"
                                            data-confirm="{{ $category->is_active ? 'Deactivate this subject?' : 'Activate this subject?' }}">
                                            @csrf
                                            <button type="submit"
                                                title="{{ $category->is_active ? 'Deactivate' : 'Activate' }} this subject"
                                                aria-label="{{ $category->is_active ? 'Deactivate' : 'Activate' }} {{ $category->name }}"
                                                class="rounded-lg p-2 transition-colors {{ $category->is_active ? 'text-warning-500 hover:bg-warning-50 dark:hover:bg-slate-700' : 'text-success-500 hover:bg-success-50 dark:hover:bg-slate-700' }}">
                                                @if($category->is_active)
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                No subjects yet. Add the first one above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @if($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif
</div>
@endsection
