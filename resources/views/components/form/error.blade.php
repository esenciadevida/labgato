@props(['for' => null])

@if($errors->has($for))
    <p id="{{ $for }}-error" class="mt-1 text-sm text-danger-600" role="alert">
        {{ $errors->first($for) }}
    </p>
@endif
