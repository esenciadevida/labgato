@props(['disabled' => false, 'rows' => 4])

<textarea
    {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 sm:text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500' .
            ($disabled ? ' cursor-not-allowed bg-slate-100 text-slate-500 dark:text-slate-400 shadow-none dark:bg-slate-800 dark:text-slate-500 dark:text-slate-400' : ''),
        'rows' => $rows,
    ]) }}
    @if($disabled) disabled @endif
>{{ $slot }}</textarea>
