@props(['type' => 'info'])

@php
$classes = 'rounded-lg p-4';
$classes .= match($type) {
    'success' => ' bg-success-50 border border-success-600/20 text-success-700 dark:bg-success-900/20 dark:border-success-600/30 dark:text-success-300',
    'error' => ' bg-danger-50 border border-danger-600/20 text-danger-700 dark:bg-danger-900/20 dark:border-danger-600/30 dark:text-danger-300',
    'warning' => ' bg-warning-50 border border-warning-600/20 text-warning-700 dark:bg-warning-900/20 dark:border-warning-600/30 dark:text-warning-300',
    default => ' bg-info-50 border border-info-600/20 text-info-700 dark:bg-info-900/20 dark:border-info-600/30 dark:text-info-300',
};
$role = in_array($type, ['error', 'warning']) ? 'alert' : 'status';
$icon = match($type) {
    'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    'error' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="{{ $role }}">
    <div class="flex items-start gap-3">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
        </svg>
        <div class="text-sm">{{ $slot }}</div>
    </div>
</div>
