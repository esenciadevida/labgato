@props([
    'tourKey',
    'steps' => [],
    'autostart' => false,
])

<div
    data-tour-config
    data-tour-key="{{ $tourKey }}"
    data-autostart="{{ $autostart ? 'true' : 'false' }}"
    data-complete-url="{{ route('account.onboarding.tour.complete', ['key' => $tourKey]) }}"
    data-csrf="{{ csrf_token() }}"
    data-steps="{{ json_encode($steps) }}"
    class="hidden"
    aria-hidden="true"
></div>
