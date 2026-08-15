@props([
    'outputs' => [],
    'interpretation' => null,
])

@php
    $outputItems = array_values($outputs);
    $flagVariant = fn ($flag) => match ($flag) {
        'low', 'high' => 'danger',
        'normal' => 'success',
        default => null,
    };
    $flagLabel = fn ($flag) => match ($flag) {
        'low' => 'Low',
        'high' => 'High',
        'normal' => 'Normal',
        default => null,
    };
@endphp

@if(! empty($outputItems))
<div class="rounded-2xl border border-primary-200 dark:border-primary-500/30 bg-primary-50 dark:bg-primary-500/10 p-6 sm:p-8">
    <h3 class="text-sm font-semibold uppercase tracking-wide text-primary-700 dark:text-primary-400">Result</h3>

    @if(count($outputItems) === 1)
        @php $output = $outputItems[0]; @endphp
        <div class="mt-2 text-4xl font-bold text-primary-700 dark:text-primary-300">
            {{ $output['value'] }}
            @if(! empty($output['unit']))
                <span class="text-lg font-medium text-primary-600 dark:text-primary-400">{{ $output['unit'] }}</span>
            @endif
        </div>

        @if(! empty($output['reference']))
            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-400">Reference:</span>
                @if(! empty($output['reference']['url']))
                    <a href="{{ $output['reference']['url'] }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                        {{ $output['reference']['display'] }}
                    </a>
                @else
                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $output['reference']['display'] }}</span>
                @endif
                @if($flagVariant($output['reference']['flag'] ?? null))
                    <x-ui.badge :variant="$flagVariant($output['reference']['flag'])">
                        {{ $flagLabel($output['reference']['flag']) }}
                    </x-ui.badge>
                @endif
            </div>
        @endif
    @else
        <dl class="mt-4 space-y-4">
            @foreach($outputItems as $output)
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm font-medium text-primary-700 dark:text-primary-300">{{ $output['label'] ?? '' }}</dt>
                    <dd class="text-right">
                        <span class="text-2xl font-bold text-primary-700 dark:text-primary-400">
                            {{ $output['value'] }}
                            @if(! empty($output['unit']))
                                <span class="text-sm font-medium text-primary-600 dark:text-primary-400">{{ $output['unit'] }}</span>
                            @endif
                        </span>

                        @if(! empty($output['reference']))
                            <div class="mt-1 flex flex-wrap items-center justify-end gap-2 text-sm">
                                <span class="text-slate-600 dark:text-slate-400">Reference:</span>
                                @if(! empty($output['reference']['url']))
                                    <a href="{{ $output['reference']['url'] }}" class="font-medium text-primary-700 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">
                                        {{ $output['reference']['display'] }}
                                    </a>
                                @else
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $output['reference']['display'] }}</span>
                                @endif
                                @if($flagVariant($output['reference']['flag'] ?? null))
                                    <x-ui.badge :variant="$flagVariant($output['reference']['flag'])">
                                        {{ $flagLabel($output['reference']['flag']) }}
                                    </x-ui.badge>
                                @endif
                            </div>
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    @endif

    @if($interpretation)
        <p class="mt-3 text-slate-700 dark:text-slate-300">{{ $interpretation }}</p>
    @endif
</div>
@endif
