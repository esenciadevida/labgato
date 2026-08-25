@extends('layouts.admin')

@section('title', 'Questions - ' . config('app.name'))

@section('header', 'Question Bank')

@php
    $statusTabs = [
        '' => 'All',
        'active' => 'Active',
        'draft' => 'Draft',
        'archived' => 'Archived',
    ];

    $filterUrl = fn ($status) => route('admin.questions.index', array_filter([
        'search' => $search,
        'category' => $categoryId,
        'difficulty' => $difficulty,
        'type' => $type,
        'status' => $status ?: null,
    ]));
@endphp

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-ui.card class="!p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($stats['total']) }}</p>
        </x-ui.card>
        <x-ui.card class="!p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Active</p>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ number_format($stats['active']) }}</p>
        </x-ui.card>
        <x-ui.card class="!p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Draft</p>
            <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ number_format($stats['draft']) }}</p>
        </x-ui.card>
        <x-ui.card class="!p-4">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Archived</p>
            <p class="mt-1 text-2xl font-bold text-slate-500 dark:text-slate-400">{{ number_format($stats['archived']) }}</p>
        </x-ui.card>
    </div>

    @if($errors->any())
        <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <nav class="flex flex-wrap gap-1" aria-label="Status filter">
            @foreach($statusTabs as $value => $label)
                <a href="{{ $filterUrl($value) }}"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors
                        {{ $status === $value ? 'bg-primary-500 text-white shadow-card' : 'text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800' }}"
                    @if($status === $value) aria-current="page" @endif
                >{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->hasPermission('questions.import'))
                <x-ui.button href="{{ route('admin.questions.import') }}" variant="secondary">Import CSV</x-ui.button>
            @endif
            @if(auth()->user()->hasPermission('categories.manage'))
                <x-ui.button href="{{ route('admin.question-categories.index') }}" variant="secondary">Subjects</x-ui.button>
            @endif
            @if(auth()->user()->hasPermission('questions.manage'))
                <x-ui.button href="{{ route('admin.questions.create') }}">New Question</x-ui.button>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('admin.questions.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center" role="search">
        <x-ui.input type="search" name="search" value="{{ $search }}" placeholder="Search question text..." aria-label="Search questions" />
        <select name="category" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" aria-label="Filter by subject">
            <option value="">All subjects</option>
            @foreach(\App\Models\QuestionCategory::orderBy('sort_order')->get() as $categoryOption)
                <option value="{{ $categoryOption->id }}" @selected((string) $categoryId === (string) $categoryOption->id)>{{ $categoryOption->name }}</option>
            @endforeach
        </select>
        <select name="difficulty" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" aria-label="Filter by difficulty">
            <option value="">All difficulties</option>
            @foreach(\App\Models\Question::DIFFICULTIES as $difficultyOption)
                <option value="{{ $difficultyOption }}" @selected($difficulty === $difficultyOption)>{{ ucfirst($difficultyOption) }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200" aria-label="Filter by type">
            <option value="">All types</option>
            @foreach(\App\Models\Question::TYPES as $typeOption)
                <option value="{{ $typeOption }}" @selected($type === $typeOption)>{{ ucfirst(str_replace('_', ' ', $typeOption)) }}</option>
            @endforeach
        </select>
        <input type="hidden" name="status" value="{{ $status }}">
        <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
        <x-ui.button href="{{ route('admin.questions.index') }}" variant="secondary">Clear</x-ui.button>
    </form>

    <div class="flex flex-wrap gap-1.5">
        @foreach($categoryCounts as $categoryCount)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                {{ $categoryCount->name }}
                <span class="rounded-full bg-white/80 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 dark:bg-slate-900/60 dark:text-slate-400">{{ $categoryCount->questions_count }}</span>
            </span>
        @endforeach
    </div>

    @if($batchProgress)
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($batchProgress as $batch)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-center justify-between gap-2">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ str_replace('_', ' ', $batch['label']) }}</p>
                        <span class="shrink-0 text-xs font-medium text-slate-500 dark:text-slate-400">
                            {{ $batch['activated'] }}/{{ $batch['total'] }} activated
                        </span>
                    </div>
                    <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-success-500 transition-all" style="width: {{ $batch['percent'] }}%;"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                        Review, attach images, then Activate to publish.
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <x-ui.card>
        @if($questions->isEmpty())
            <x-ui.empty-state title="No questions found" description="Adjust your filters or create the first question for this bank.">
                @if(auth()->user()->hasPermission('questions.manage'))
                    <x-slot:action>
                        <x-ui.button href="{{ route('admin.questions.create') }}">New Question</x-ui.button>
                    </x-slot:action>
                @endif
            </x-ui.empty-state>
        @else
            <div
                x-data="{
                    selected: [],
                    allIds: @js($questions->getCollection()->pluck('id')->all()),
                    selectAll: false,
                    toggle(id) {
                        if (this.selected.includes(id)) {
                            this.selected = this.selected.filter((i) => i !== id);
                        } else {
                            this.selected.push(id);
                        }
                        this.selectAll = this.allIds.length > 0 && this.selected.length === this.allIds.length;
                    },
                    toggleAll() {
                        this.selectAll = ! this.selectAll;
                        this.selected = this.selectAll ? [...this.allIds] : [];
                    },
                }"
                class="space-y-4"
            >
                @if(auth()->user()->hasPermission('questions.manage'))
                <div
                    x-show="selected.length > 0"
                    x-cloak
                    class="flex flex-wrap items-center gap-3 rounded-lg border border-primary-200 bg-primary-50 px-4 py-2.5 dark:border-primary-800 dark:bg-primary-900/30"
                >
                    <span class="text-sm font-medium text-primary-700 dark:text-primary-300">
                        <span x-text="selected.length"></span> selected
                    </span>
                    @if(auth()->user()->hasPermission('questions.manage'))
                        <form method="POST" action="{{ route('admin.questions.bulk') }}">
                            @csrf
                            <template x-for="id in selected" :key="'bulk-id-' + id">
                                <input type="hidden" name="ids[]" :value="id">
                            </template>
                            <div class="flex gap-2">
                                <button type="submit" name="action" value="archive" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Archive selected</button>
                                <button type="submit" name="action" value="activate" class="rounded-lg border border-success-300 bg-white px-3 py-1.5 text-xs font-medium text-success-700 hover:bg-success-50 dark:border-success-700 dark:bg-slate-800 dark:text-success-400 dark:hover:bg-slate-700">Activate selected</button>
                                <button type="submit" name="action" value="delete"
                                    data-confirm="Delete the selected questions? Questions used in exams will be skipped."
                                    class="rounded-lg border border-danger-300 bg-white px-3 py-1.5 text-xs font-medium text-danger-700 hover:bg-danger-50 dark:border-danger-700 dark:bg-slate-800 dark:text-danger-400 dark:hover:bg-slate-700">Delete selected</button>
                            </div>
                        </form>
                    @endif
                </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                @if(auth()->user()->hasPermission('questions.manage'))
                                    <th scope="col" class="w-10 px-4 py-3">
                                        <input type="checkbox" :checked="selectAll" @change="toggleAll()" aria-label="Select all questions on this page" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                                    </th>
                                @endif
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">#</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Question</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Subject</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Difficulty</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Options</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Updated</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                            @foreach($questions as $index => $question)
                                <tr>
                                    @if(auth()->user()->hasPermission('questions.manage'))
                                        <td class="px-4 py-3">
                                            <input type="checkbox" :checked="selected.includes({{ $question->id }})" @change="toggle({{ $question->id }})"
                                                aria-label="Select question {{ $question->id }}"
                                                class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-sm text-slate-400">{{ ($questions->currentPage() - 1) * $questions->perPage() + $index + 1 }}</td>
                                    <td class="max-w-md px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            @if($question->image_path)
                                                <img src="{{ $question->image_url }}" alt="" class="h-9 w-9 shrink-0 rounded object-cover" loading="lazy">
                                            @endif
                                            <a href="{{ route('admin.questions.show', $question) }}" class="line-clamp-2 text-sm text-slate-700 hover:text-primary-600 dark:text-slate-300 dark:hover:text-primary-400">
                                                {{ Str::limit(strip_tags($question->stem), 110) }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $question->category?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm capitalize text-slate-500 dark:text-slate-400">{{ str_replace('_', ' ', $question->question_type) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize
                                            {{ $question->difficulty === 'easy' ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400' : '' }}
                                            {{ $question->difficulty === 'intermediate' ? 'bg-warning-50 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' : '' }}
                                            {{ $question->difficulty === 'advanced' ? 'bg-danger-50 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : '' }}">
                                            {{ $question->difficulty }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize
                                            {{ $question->status === 'active' ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400' : '' }}
                                            {{ $question->status === 'draft' ? 'bg-warning-50 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' : '' }}
                                            {{ $question->status === 'archived' ? 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' : '' }}">
                                            {{ $question->status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $question->options_count }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $question->updated_at->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @if(auth()->user()->hasPermission('questions.manage'))
                                                @if($question->status !== 'archived')
                                                    <form method="POST" action="{{ route('admin.questions.archive', $question) }}" class="inline-block">
                                                        @csrf
                                                        <button type="submit" title="Archive this question" aria-label="Archive this question" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-warning-50 hover:text-warning-600 dark:hover:bg-slate-700">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2M5 15a2 2 0 012 2h6a2 2 0 012-2m-13-5h14"/></svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.questions.restore', $question) }}" class="inline-block">
                                                        @csrf
                                                        <button type="submit" title="Restore this question" aria-label="Restore this question" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-success-50 hover:text-success-600 dark:hover:bg-slate-700">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="inline-block" data-confirm="Delete this question permanently? Used questions are protected automatically.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete this question" aria-label="Delete this question" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-danger-50 hover:text-danger-600 dark:hover:bg-slate-700">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(auth()->user()->hasPermission('questions.manage'))
                                                <a href="{{ route('admin.questions.edit', $question) }}" title="Edit this question" aria-label="Edit this question" class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-slate-700">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($questions->hasPages())
                    <div class="border-t border-slate-200 px-4 py-3 dark:border-slate-700">
                        {{ $questions->links() }}
                    </div>
                @endif
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
