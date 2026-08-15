@props(['type' => 'text', 'error' => null, 'disabled' => false])

<div>
    <input
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 sm:text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500' .
                ($error
                    ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/25'
                    : ' border-slate-300 focus:border-primary-500 focus:ring-primary-500/25 dark:focus:border-primary-500') .
                ($disabled ? ' cursor-not-allowed bg-slate-100 text-slate-500 dark:text-slate-400 shadow-none dark:bg-slate-800 dark:text-slate-500 dark:text-slate-400' : ''),
        ]) }}
        type="{{ $type }}"
        @if($disabled) disabled @endif
    >
    @if($error)
        <p class="mt-1 text-sm text-danger-600">{{ $error }}</p>
    @endif
</div>
