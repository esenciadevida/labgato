@extends('layouts.admin')

@section('title', "Version {$version->version_number} - {$sop->name} - " . config('app.name'))

@section('header', "Version {$version->version_number}")

@section('content')
@php
    $render = fn ($value) => $value !== null && trim((string) $value) !== ''
        ? \App\Support\RichTextSanitizer::render((string) $value)
        : null;
@endphp

<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Snapshot of <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $sop->name }}</span> saved
            <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $version->created_at?->format('M j, Y g:i A') }}</span>
        </p>
        <div class="flex flex-wrap items-center gap-2">
            <x-ui.button href="{{ route('admin.sops.versions.index', $sop) }}" variant="secondary" size="sm">
                Back to History
            </x-ui.button>
            @if($version->version_number !== $sop->version)
                <form method="POST" action="{{ route('admin.sops.versions.restore', [$sop, $version]) }}" data-confirm="Restore version {{ $version->version_number }}? This creates a new version from its content.">
                    @csrf
                    <x-ui.button type="submit" variant="danger" size="sm">
                        Restore Version
                    </x-ui.button>
                </form>
            @endif
        </div>
    </div>

    <x-ui.card>
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Version</dt>
                <dd class="mt-1 text-sm font-medium text-slate-900 dark:text-slate-100">
                    {{ $version->version_number }}
                    @if($version->version_number === $sop->version)
                        <x-ui.badge variant="success">Current</x-ui.badge>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saved by</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $version->creator?->name ?? 'System' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saved at</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $version->created_at?->format('M j, Y g:i A') }}</dd>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <dt class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Document code</dt>
                <dd class="mt-1 text-sm text-slate-900 dark:text-slate-100">{{ $version->document_code ?? '—' }}</dd>
            </div>
        </dl>

        @if($version->change_summary)
            <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Change summary</h2>
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">{{ $version->change_summary }}</p>
            </div>
        @endif

        <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-700 space-y-6">
            @if($render($version->content))
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Content</h2>
                    <div class="prose prose-slate dark:prose-invert max-w-none mt-2">
                        {!! $render($version->content) !!}
                    </div>
                </div>
            @endif

            @foreach([
                'Purpose' => $version->purpose,
                'Scope' => $version->scope,
                'Procedure' => $version->procedure_content,
                'Quality control' => $version->quality_control,
                'Safety information' => $version->safety_information,
                'References' => $version->references,
            ] as $label => $field)
                @if($render($field))
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $label }}</h2>
                        <div class="prose prose-slate dark:prose-invert max-w-none mt-2">
                            {!! $render($field) !!}
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </x-ui.card>
</div>
@endsection
