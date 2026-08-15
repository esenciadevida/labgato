@props(['title' => null, 'interactive' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-card text-slate-900 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-100' . ($interactive ? ' transition hover:border-primary-300 hover:shadow-card-hover dark:hover:border-primary-700' : '')]) }}>
    @if($title)
        <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h3>
        </div>
    @endif
    <div class="px-4 sm:px-6 py-4">
        {{ $slot }}
    </div>
</div>
