@extends('layouts.admin')

@section('title', 'Import Reference Ranges - ' . config('app.name'))

@section('header', 'Import Reference Ranges')

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
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Step 1 — Download the template</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Start with the CSV template to get the exact column structure.</p>
                    </div>
                </div>

                <a
                    href="{{ route('admin.reference-ranges.import.template') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-card transition-colors hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download CSV Template
                </a>
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    The template includes a sample row so you can see the expected format before filling in your data.
                </p>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Step 2 — Upload your CSV</h3>
                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Upload the file to validate and preview your data before importing.</p>
                    </div>
                </div>

                <form action="{{ route('admin.reference-ranges.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-form.label for="file" required>
                                <span class="text-danger-500">*</span> CSV File
                            </x-form.label>
                            <input
                                type="file"
                                name="file"
                                id="file"
                                accept=".csv"
                                required
                                class="block w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-card file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-primary-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:file:bg-slate-700 dark:file:text-slate-200"
                            >
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Max 2MB, CSV format only.</p>
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
                                    <input type="radio" name="import_as" value="published" class="text-primary-500 focus:ring-primary-500">
                                    Published
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Draft keeps imported ranges hidden until reviewed.</p>
                        </div>

                        <div>
                            <x-form.label for="duplicate_handling">Duplicate handling</x-form.label>
                            <div class="mt-2 flex gap-3">
                                <label class="flex flex-1 cursor-pointer select-none items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:has-[:checked]:border-primary-500 dark:has-[:checked]:bg-slate-700">
                                    <input type="radio" name="duplicate_handling" value="skip" checked class="text-primary-500 focus:ring-primary-500">
                                    Skip
                                </label>
                                <label class="flex flex-1 cursor-pointer select-none items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition-colors has-[:checked]:border-primary-500 has-[:checked]:bg-primary-50 has-[:checked]:text-primary-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:has-[:checked]:border-primary-500 dark:has-[:checked]:bg-slate-700">
                                    <input type="radio" name="duplicate_handling" value="update" class="text-primary-500 focus:ring-primary-500">
                                    Update
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Skip leaves existing ranges untouched; Update overwrites them.</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button
                            type="submit"
                            name="preview"
                            value="1"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-card transition-colors hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>
                            Upload &amp; Preview
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Required columns">
                <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Every row must include these columns for a successful import.</p>
                <ul class="space-y-2.5">
                    @foreach([
                        ['name', 'Display name of the reference range'],
                        ['analyte_name', 'Analyte the range applies to'],
                        ['unit', 'Reporting unit (e.g. g/dL)'],
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

            <x-ui.card title="Optional columns">
                <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">Include these columns when you have the data available.</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach([
                        'specimen_type', 'methodology', 'population_group', 'age_min', 'age_max', 'sex',
                        'si_unit', 'lower_limit', 'upper_limit', 'critical_low', 'critical_high',
                        'conversion_factor', 'source_citation',
                    ] as $column)
                        <code class="rounded-md bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $column }}</code>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Good to know">
                <ul class="space-y-2.5 text-xs text-slate-500 dark:text-slate-400">
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Rows are validated before import. Invalid rows are reported with a reason and skipped.
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Duplicate detection matches existing ranges by analyte and sex.
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Sex accepts <code class="font-medium">any</code>, <code class="font-medium">male</code>, or <code class="font-medium">female</code>.
                    </li>
                </ul>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
