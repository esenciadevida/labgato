@extends('layouts.admin')

@section('title', 'Import Questions - ' . config('app.name'))

@section('header', 'Import Questions')

@section('content')
<div class="space-y-6">
    @if($errors->any())
        <x-ui.alert type="error">
            {{ $errors->first() }}
        </x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-6 lg:col-span-3">
            <x-ui.card>
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Step 1 — Download the template</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">The template includes three example rows and the exact column structure, including the optional image columns.</p>
                    </div>
                </div>

                <a
                    href="{{ route('admin.questions.import.template') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-card transition-colors hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download CSV Template
                </a>
            </x-ui.card>

            <x-ui.card>
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Step 2 — Upload your CSV</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Rows are validated before anything is created.</p>
                    </div>
                </div>

                <form action="{{ route('admin.questions.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-form.label for="file">CSV File</x-form.label>
                            <input
                                type="file"
                                name="file"
                                id="file"
                                accept=".csv"
                                required
                                class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-card file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-600 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/25 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200"
                            >
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Max 5MB, CSV format only.</p>
                            <x-form.error for="file" />
                        </div>

                        <div>
                            <x-form.label for="import_as">Import as</x-form.label>
                            <div class="mt-2 flex gap-3">
                                <label class="flex flex-1 cursor-pointer select-none items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:has-[:checked]:border-primary-500 dark:has-[:checked]:bg-slate-700">
                                    <input type="radio" name="import_as" value="draft" checked class="text-primary-500 focus:ring-primary-500">
                                    Draft
                                </label>
                                <label class="flex flex-1 cursor-pointer select-none items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:has-[:checked]:border-primary-500 dark:has-[:checked]:bg-slate-700">
                                    <input type="radio" name="import_as" value="active" class="text-primary-500 focus:ring-primary-500">
                                    Active
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Draft keeps imported questions out of exams until reviewed.</p>
                        </div>

                        <div class="sm:col-span-2 mt-2">
                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-card transition-colors hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                            >
                                Upload &amp; Preview
                            </button>
                        </div>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Column reference">
                <ul class="space-y-2.5">
                    @foreach([
                        ['category', 'Subject name — must match an existing subject (case-insensitive)'],
                        ['type', 'One of: '.implode(', ', \App\Models\Question::TYPES).' — defaults to text'],
                        ['difficulty', 'One of: '.implode(', ', \App\Models\Question::DIFFICULTIES).' — defaults to intermediate'],
                        ['stem', 'The question text (required)'],
                        ['option_a..option_d', 'Option texts — at least A and B required'],
                        ['correct_option', 'Letter of the correct option: A, B, C or D'],
                        ['explanation', 'Optional rationale'],
                        ['image_url', 'Optional. A public http(s) link to the question image (png, jpg, gif, webp — max 8MB), which is downloaded and attached automatically. Rows with an image should use the text_image or image type; a type of text is upgraded automatically.'],
                        ['image_alt', 'Optional alt text describing the image for accessibility'],
                    ] as [$column, $hint])
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="min-w-0">
                                <code class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $column }}</code>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $hint }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
