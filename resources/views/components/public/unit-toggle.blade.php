@props(['range'])

@if($range->conversion_factor !== null && $range->si_unit !== null)
<div x-data="{
    showSI: false,
    factor: {{ $range->conversion_factor }},
    conventionalUnit: '{{ addslashes($range->unit) }}',
    siUnit: '{{ addslashes($range->si_unit) }}',
    lowerLimit: {{ $range->lower_limit ?? 'null' }},
    upperLimit: {{ $range->upper_limit ?? 'null' }},
    criticalLow: {{ $range->critical_low ?? 'null' }},
    criticalHigh: {{ $range->critical_high ?? 'null' }},
    formatVal(v) {
        if (v === null) return null;
        const val = this.showSI ? (v * this.factor) : v;
        return val % 1 === 0 ? val.toString() : val.toFixed(2).replace(/\.?0+$/, '');
    },
    currentUnit() { return this.showSI ? this.siUnit : this.conventionalUnit; }
}" class="no-print mb-4">
    <button
        type="button"
        @click="showSI = !showSI"
        :aria-pressed="showSI.toString()"
        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
    >
        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
        </svg>
        <span x-show="!showSI">Show SI Units</span>
        <span x-show="showSI">Show Conventional Units</span>
    </button>
    <span class="sr-only" x-show="showSI" x-text="'Displaying SI units: ' + siUnit"></span>
    <span class="sr-only" x-show="!showSI" x-text="'Displaying conventional units: ' + conventionalUnit"></span>
</div>
@else
<div class="no-print mb-4"></div>
@endif
