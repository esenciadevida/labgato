@props(['valid' => [], 'errors' => [], 'total' => 0])

@extends('layouts.admin')

@section('title', 'Import Preview - ' . config('app.name'))

@section('header', 'Import Reference Ranges')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-display font-bold text-slate-900 dark:text-slate-100">Import Preview</h2>
            <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                {{ $total }} total {{ $total === 1 ? 'row' : 'rows' }} processed. Review the results below before confirming.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-3 py-1 text-xs font-semibold text-success-700 dark:bg-success-900/30 dark:text-success-300">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ count($valid) }} valid
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-danger-50 px-3 py-1 text-xs font-semibold text-danger-700 dark:bg-danger-900/30 dark:text-danger-300">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ count($errors) }} {{ count($errors) === 1 ? 'error' : 'errors' }}
            </span>
        </div>
    </div>

    @if(count($errors))
        <x-ui.card title="Rows with errors">
            <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                These rows will be skipped and will not be imported. Fix them in your file and upload again if needed.
            </p>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-slate-400">
                            <th class="py-2.5 pr-4">Row</th>
                            <th class="py-2.5">Error</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach($errors as $row => $errorList)
                            @foreach((array) $errorList as $error)
                                <tr class="text-sm text-slate-700 dark:text-slate-300">
                                    <td class="py-2.5 pr-4 font-mono text-slate-500 dark:text-slate-400">{{ $row }}</td>
                                    <td class="py-2.5 text-danger-600 dark:text-danger-400">{{ $error }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

    <x-ui.card title="Valid rows — ready to import">
        <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
            {{ count($valid) }} {{ count($valid) === 1 ? 'row' : 'rows' }} will be imported when you confirm below.
        </p>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        <th class="py-2.5 pr-4">Row</th>
                        <th class="py-2.5 pr-4">Name</th>
                        <th class="py-2.5 pr-4">Analyte</th>
                        <th class="py-2.5 pr-4">Unit</th>
                        <th class="py-2.5 pr-4">Lower Limit</th>
                        <th class="py-2.5">Upper Limit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($valid as $row => $rowData)
                        <tr class="text-sm text-slate-700 dark:text-slate-300">
                            <td class="py-2.5 pr-4 font-mono text-slate-500 dark:text-slate-400">{{ $row }}</td>
                            <td class="py-2.5 pr-4 font-medium text-slate-900 dark:text-slate-100">{{ $rowData['name'] ?? '' }}</td>
                            <td class="py-2.5 pr-4">{{ $rowData['analyte_name'] ?? '' }}</td>
                            <td class="py-2.5 pr-4">{{ $rowData['unit'] ?? '' }}</td>
                            <td class="py-2.5 pr-4">{{ $rowData['lower_limit'] ?? '' }}</td>
                            <td class="py-2.5">{{ $rowData['upper_limit'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
        <a
            href="{{ route('admin.reference-ranges.import') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-card transition-colors hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 15.75L3 12m0 0l3.75-3.75M3 12h18"/>
            </svg>
            Choose a different file
        </a>

        <form action="{{ route('admin.reference-ranges.import.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="confirm" value="1">
            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-medium text-white shadow-card transition-colors hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 sm:w-auto"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Confirm Import
            </button>
        </form>
    </div>
</div>
@endsection
