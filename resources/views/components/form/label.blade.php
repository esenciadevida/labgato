@props(['for' => null, 'required' => false])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-medium text-slate-700 dark:text-slate-300']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-danger-600" aria-hidden="true">*</span>
    @endif
</label>
