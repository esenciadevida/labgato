<div class="space-y-6">
    @php
        $inputSchema = is_array($calculator->input_schema) ? $calculator->input_schema : [];
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
                    <label for="{{ $key }}" class="block text-sm font-medium text-gray-700">
                        {{ $field['label'] ?? $key }}
                        @if($field['unit'] ?? null)
                            <span class="font-normal text-gray-500">({{ $field['unit'] }})</span>
                        @endif
                        @if($field['required'] ?? false)
                            <span class="text-red-500" aria-hidden="true">*</span>
                        @endif
                    </label>

                    @if($type === 'select')
                        <select
                            id="{{ $key }}"
                            name="{{ $key }}"
                            wire:model="values.{{ $key }}"
                            @if($fieldErrors) aria-describedby="{{ $errorId }}" @endif
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                            <option value="">Select...</option>
                            @foreach(($field['options'] ?? []) as $option)
                                <option value="{{ $option['value'] ?? '' }}">{{ $option['label'] ?? $option['value'] ?? '' }}</option>
                            @endforeach
                        </select>
                    @elseif($type === 'text')
                        <input
                            type="text"
                            id="{{ $key }}"
                            name="{{ $key }}"
                            wire:model="values.{{ $key }}"
                            @if($fieldErrors) aria-describedby="{{ $errorId }}" @endif
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
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
                            @if($fieldErrors) aria-describedby="{{ $errorId }}" @endif
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                        >
                    @endif

                    @foreach($fieldErrors as $message)
                        <p id="{{ $errorId }}" class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                class="inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Calculate
            </button>
            <span wire:loading wire:target="calculate" class="ml-3 text-sm text-gray-500">Calculating...</span>
        </div>
    </form>

    <div wire:key="result" class="rounded-lg border border-gray-200 bg-gray-50 p-5" aria-live="polite" role="status">
        @if(! $hasCalculated)
            <p class="text-sm text-gray-500">Enter your values and press Calculate to see the result.</p>
        @elseif($outputs === [])
            <p class="text-sm text-gray-600">No result could be produced. Check the values above and try again.</p>
        @else
            <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 mb-3">Result</h3>
            <dl class="space-y-2">
                @foreach($outputs as $output)
                    <div class="flex items-baseline justify-between gap-4">
                        <dt class="text-sm text-gray-700">{{ $output['label'] }}</dt>
                        <dd class="text-xl font-bold text-gray-900">
                            {{ $output['value'] }}
                            @if($output['unit'])
                                <span class="text-sm font-medium text-gray-500">{{ $output['unit'] }}</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</div>
