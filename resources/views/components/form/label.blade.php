@props(['for' => null, 'required' => false])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-700']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500" aria-hidden="true">*</span>
    @endif
</label>
