@extends('layouts.admin')

@section('title', 'Certificate Preview - ' . $assessment->title . ' - ' . config('app.name'))

@section('header', 'Certificate Preview')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3">
    <x-ui.button href="{{ route('admin.assessments.edit', $assessment) }}" variant="ghost" size="sm">
        &larr; Back to {{ $assessment->title }}
    </x-ui.button>
    <x-ui.button href="{{ route('admin.certificates.template') }}" variant="secondary" size="sm" target="_blank">
        Edit global template
    </x-ui.button>
</div>

<div class="mx-auto max-w-3xl space-y-6">
    @if(! $preview['enabled'])
        <div class="rounded-xl border border-warning-300 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-700 dark:bg-warning-900/30 dark:text-warning-300">
            Certificates are currently <strong>disabled</strong> for this assessment. Enable &ldquo;Issue certificates&rdquo; on the assessment to give passing participants a certificate.
        </div>
    @endif

    <x-ui.card title="Preview" class="!p-0 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50 px-4 py-2 dark:border-slate-800 dark:bg-slate-800/60">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Shown with sample data. Values use this assessment&rsquo;s overrides, falling back to the global template.</span>
        </div>
        <div class="p-4">
            <div class="mx-auto" style="max-width: 700px;">
                <div class="relative overflow-hidden rounded-lg border-2 bg-white" style="aspect-ratio: 280/184;">
                    <div class="absolute inset-1 border pointer-events-none" style="border-color: {{ $preview['secondaryColor'] }}33;"></div>
                    <div class="relative flex h-full flex-col items-center justify-center p-6 text-center">
                        @if($preview['showLogo'])
                            <div class="mb-3">
                                <div class="text-xl font-extrabold tracking-wider" style="color: {{ $preview['primaryColor'] }};">LabGOTO</div>
                                <div class="text-[8px] font-semibold uppercase tracking-widest text-slate-400">Medical Laboratory Knowledge Platform</div>
                            </div>
                        @endif
                        <div class="mb-3 inline-block rounded-full border px-4 py-1 text-[10px] font-extrabold uppercase tracking-[3px]" style="color: {{ $preview['secondaryColor'] }}; border-color: {{ $preview['secondaryColor'] }}55;">
                            Achievement
                        </div>
                        <p class="text-[11px] italic text-slate-400">This certificate is proudly presented to</p>
                        <p class="mt-1 font-serif text-2xl font-bold" style="color: {{ $preview['primaryColor'] }};">{{ $preview['sample']['{name}'] }}</p>
                        <div class="mt-1 h-px w-2/3" style="background: {{ $preview['secondaryColor'] }};"></div>
                        <p class="mt-2 text-xs text-slate-600">{!! $preview['bodyHtml'] !!}</p>
                        <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between border-t pt-2">
                            <div class="text-[9px] text-slate-400">
                                <div class="font-serif font-bold text-slate-600">{{ $preview['signatoryName'] }}</div>
                                <div class="uppercase tracking-wider">{{ $preview['signatoryTitle'] }}</div>
                            </div>
                            @if($preview['showQr'])
                                <div class="text-center">
                                    <div class="mx-auto mb-0.5 flex h-10 w-10 items-center justify-center rounded border border-dashed border-slate-300 text-[7px] text-slate-400">QR</div>
                                    <div class="text-[7px] text-slate-400">Scan to verify</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-[9px] leading-relaxed text-slate-500 text-justify">{!! $preview['disclaimerHtml'] !!}</div>
            </div>
        </div>
    </x-ui.card>

    <div class="flex items-center justify-end gap-3">
        <x-ui.button href="{{ route('admin.assessments.edit', $assessment) }}" variant="ghost">
            Done
        </x-ui.button>
    </div>
</div>
@endsection
