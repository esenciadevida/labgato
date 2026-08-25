@extends('layouts.admin')

@section('title', 'Preview Question - ' . config('app.name'))

@section('header', 'Question Preview')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <x-ui.button href="{{ route('admin.questions.index') }}" variant="secondary">
            Back to questions
        </x-ui.button>
        @if(auth()->user()->hasPermission('questions.manage'))
            <div class="flex gap-2">
                <x-ui.button href="{{ route('admin.questions.edit', $question) }}" variant="secondary">Edit</x-ui.button>
            </div>
        @endif
    </div>

    <x-ui.card>
        <div class="space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:bg-slate-700 dark:text-primary-300">
                    {{ $question->category?->name ?? 'Uncategorized' }}
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium capitalize text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    {{ str_replace('_', ' ', $question->question_type) }}
                </span>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize
                    {{ $question->difficulty === 'easy' ? 'bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400' : '' }}
                    {{ $question->difficulty === 'intermediate' ? 'bg-warning-50 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' : '' }}
                    {{ $question->difficulty === 'advanced' ? 'bg-danger-50 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : '' }}">
                    {{ $question->difficulty }}
                </span>
            </div>

            <p class="text-base font-medium leading-relaxed text-slate-900 dark:text-slate-100">
                {{ $question->stem }}
            </p>

            @if($question->image_url)
                <img src="{{ $question->image_url }}" alt="{{ $question->image_alt ?? 'Question image' }}" class="max-h-96 w-full rounded-lg border border-slate-200 object-contain dark:border-slate-700">
            @endif

            <ul class="space-y-3" role="list">
                @foreach($question->options as $index => $option)
                    <li class="flex items-start gap-3 rounded-lg border px-4 py-3 text-sm
                        {{ $option->is_correct ? 'border-success-400 bg-success-50/60 dark:border-success-700 dark:bg-success-900/20' : 'border-slate-200 dark:border-slate-700' }}">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                            {{ $option->label }}
                        </span>
                        <span class="text-slate-700 dark:text-slate-200">{{ $option->text }}</span>
                        @if($option->is_correct)
                            <svg class="ml-auto h-4 w-4 shrink-0 text-success-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span class="sr-only">(Correct answer)</span>
                        @endif
                    </li>
                @endforeach
            </ul>

            @if($question->explanation)
                <details class="rounded-lg border border-slate-200 dark:border-slate-700">
                    <summary class="cursor-pointer select-none px-4 py-3 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100">
                        Show explanation (hidden from candidates until results are released)
                    </summary>
                    <div class="border-t border-slate-200 px-4 py-3 text-sm leading-relaxed text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        {!! nl2br(e($question->explanation)) !!}
                    </div>
                </details>
            @endif

            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 border-t border-slate-200 pt-4 text-xs dark:border-slate-700 sm:grid-cols-4">
                <div>
                    <dt class="font-medium uppercase tracking-wide text-slate-400">Status</dt>
                    <dd class="mt-1 capitalize text-slate-600 dark:text-slate-300">{{ $question->status }}</dd>
                </div>
                <div>
                    <dt class="font-medium uppercase tracking-wide text-slate-400">Used in exams</dt>
                    <dd class="mt-1 text-slate-600 dark:text-slate-300">{{ $question->usedInExams() ? 'Yes — delete locked' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="font-medium uppercase tracking-wide text-slate-400">Created</dt>
                    <dd class="mt-1 text-slate-600 dark:text-slate-300">{{ $question->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="font-medium uppercase tracking-wide text-slate-400">Updated</dt>
                    <dd class="mt-1 text-slate-600 dark:text-slate-300">{{ $question->updated_at->format('M j, Y') }}</dd>
                </div>
            </dl>
        </div>
    </x-ui.card>
</div>
@endsection
