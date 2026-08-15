@extends('layouts.admin')

@section('title', 'Edit Tool - ' . config('app.name'))

@section('header', 'Edit Tool: ' . $tool->name)

@section('content')
<div class="mx-auto max-w-3xl">

    <div class="mb-6 rounded-lg border border-primary-200 bg-primary-50 p-4 dark:border-primary-900/30 dark:bg-primary-900/10">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-primary-900 dark:text-primary-100">Current Status</p>
                <p class="mt-1 text-lg font-semibold">
                    <x-ui.badge :variant="match($tool->status->color()) {
                        'green' => 'success',
                        'red' => 'danger',
                        'yellow' => 'warning',
                        'blue' => 'info',
                        default => 'default',
                    }">
                        {{ $tool->status->label() }}
                    </x-ui.badge>
                </p>
            </div>
            @if(!$tool->isPublished())
                <form method="POST" action="{{ route('admin.tools.publish', $tool) }}" class="shrink-0">
                    @csrf
                    @method('PATCH')
                    <x-ui.button type="submit" variant="success">
                        <svg class="mr-2 h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Publish Now
                    </x-ui.button>
                </form>
            @else
                <div class="shrink-0 rounded-lg bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:bg-success-900/20 dark:text-success-400">
                    <svg class="mr-2 h-4 w-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Published
                </div>
            @endif
        </div>
    </div>

    @include('admin.tools.partials.form', [
        'tool' => $tool,
        'engines' => $engines,
        'statuses' => $statuses,
        'action' => route('admin.tools.update', $tool),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
</div>
@endsection
