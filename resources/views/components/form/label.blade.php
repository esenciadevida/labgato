@props(['for' => null, 'required' => false])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-medium text-neutral-700']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-danger-600" aria-hidden="true">*</span>
    @endif
</label>
