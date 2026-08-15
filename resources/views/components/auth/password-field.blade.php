@props([
    'id' => 'password',
    'name' => 'password',
    'label' => 'Password',
    'autocomplete' => 'current-password',
    'value' => '',
    'readonly' => false,
    'showToggle' => true,
])

<div x-data="{ show: false }">
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
    <div class="relative mt-1.5">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="password"
            :type="show ? 'text' : 'password'"
            value="{{ $value }}"
            required
            @if($readonly) readonly @endif
            @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            class="input-base @if($showToggle) pr-11 @endif @error($name) input-error @enderror"
            @error($name) aria-invalid="true" @enderror
        >
        @if($showToggle)
        <button
            type="button"
            @click="show = !show"
            :aria-pressed="show"
            aria-label="Show password"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition-colors hover:text-slate-600 dark:text-slate-300 focus:outline-none focus:text-primary-600 dark:hover:text-slate-300"
        >
            <svg x-show="!show" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg x-show="show" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                <path d="M6.61 6.61A13.53 13.53 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                <line x1="2" y1="2" x2="22" y2="22"/>
            </svg>
        </button>
        @endif
    </div>
    @error($name)
        <p class="mt-1.5 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
    @enderror
</div>
