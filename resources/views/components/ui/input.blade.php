@props(['type' => 'text', 'error' => null, 'disabled' => false])

<div>
    <input
        {{ $attributes->merge([
            'class' => 'block w-full rounded-md border bg-white px-3 py-2 text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 sm:text-sm' .
                ($error
                    ? ' border-danger-500 focus:border-danger-500 focus:ring-danger-500/20'
                    : ' border-neutral-300 focus:border-primary-500 focus:ring-primary-500/20') .
                ($disabled ? ' cursor-not-allowed bg-neutral-100 text-neutral-500' : ''),
        ]) }}
        type="{{ $type }}"
        @if($disabled) disabled @endif
    >
    @if($error)
        <p class="mt-1 text-sm text-danger-600">{{ $error }}</p>
    @endif
</div>
