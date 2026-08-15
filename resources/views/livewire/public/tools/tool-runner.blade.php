<div class="space-y-6">
    @php
        $inputSchema = is_array($tool->input_schema) ? $tool->input_schema : [];
    @endphp

    <form wire:submit="calculate" novalidate class="space-y-5">
        @foreach($inputSchema as $field)
            @php
                $key = $field['key'] ?? null;
                $type = $field['type'] ?? 'text';
                $fieldErrors = $validationErrors[$key] ?? [];
                $errorId = $key ? $key . '-error' : null;
            @endphp

            @if($key)
                <div>
                    <label for="{{ $key }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        {{ $field['label'] ?? $key }}
                        @if($field['unit'] ?? null)
                            <span class="font-normal text-slate-500 dark:text-slate-400">({{ $field['unit'] }})</span>
                        @endif
                        @if($field['required'] ?? false)
                            <span class="text-danger-600" aria-hidden="true">*</span>
                        @endif
                    </label>

                    @if($type === 'select')
                        <select
                            id="{{ $key }}"
                            name="{{ $key }}"
                            wire:model="values.{{ $key }}"
                            @if($fieldErrors) aria-describedby="{{ $errorId }}" aria-invalid="true" @endif
                            class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 sm:text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary-500"
                        >
                            <option value="">Select...</option>
                            @foreach(($field['options'] ?? []) as $option)
                                @php
                                    $optionValue = is_array($option) ? (string) ($option['value'] ?? '') : (string) $option;
                                    $optionLabel = is_array($option) ? (string) ($option['label'] ?? $optionValue) : ucfirst((string) $option);
                                @endphp
                                <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @elseif($type === 'text')
                        <input
                            type="text"
                            id="{{ $key }}"
                            name="{{ $key }}"
                            wire:model="values.{{ $key }}"
                            @if($fieldErrors) aria-describedby="{{ $errorId }}" aria-invalid="true" @endif
                            class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 sm:text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary-500"
                        >
                    @else
                        <input
                            type="number"
                            id="{{ $key }}"
                            name="{{ $key }}"
                            wire:model="values.{{ $key }}"
                            @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                            @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                            @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
                            inputmode="decimal"
                            @if($fieldErrors) aria-describedby="{{ $errorId }}" aria-invalid="true" @endif
                            class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 sm:text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:border-primary-500"
                        >
                    @endif

                    @foreach($fieldErrors as $message)
                        <p id="{{ $errorId }}" class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @endforeach
                </div>
            @endif
        @endforeach

        @if(($validationErrors['_calculation'] ?? []) !== [])
            <x-ui.alert type="error">
                {{ $validationErrors['_calculation'][0] }}
            </x-ui.alert>
        @endif

        <div>
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="calculate"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg shadow-card text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed dark:bg-primary-500 dark:hover:bg-primary-400"
            >
                Calculate
            </button>
            <span wire:loading wire:target="calculate" class="ml-3 text-sm text-slate-500 dark:text-slate-400" aria-live="polite">Calculating...</span>
        </div>
    </form>

    <div wire:key="result" class="rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800" aria-live="polite" role="status">
        <div wire:loading wire:target="calculate" class="space-y-3">
            <x-ui.skeleton class="h-4 w-1/3" />
            <x-ui.skeleton class="h-4 w-2/3" />
            <x-ui.skeleton class="h-4 w-1/2" />
        </div>

        @if(! $hasCalculated)
            <p class="text-sm text-slate-500 dark:text-slate-400">Enter your values and press Calculate to see the result.</p>
        @elseif($outputs === [])
            <p class="text-sm text-slate-600 dark:text-slate-300">No result could be produced. Check the values above and try again.</p>
        @else
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Result</h3>
            <dl class="space-y-2">
                @foreach($outputs as $output)
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-sm text-slate-700 dark:text-slate-300">{{ $output['label'] }}</dt>
                        <dd class="font-display text-xl font-bold text-slate-900 dark:text-slate-100">
                            {{ $output['value'] }}
                            @if($output['unit'])
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $output['unit'] }}</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</div>
