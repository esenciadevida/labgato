@extends('layouts.public')

@section('title', 'Member Dashboard - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">
                    {{ $greeting }}, {{ auth()->user()->name }}
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    This is your contribution hub. Share laboratory knowledge and track the status of your submissions.
                </p>
            </div>
        </div>

        <x-member.navigation class="mt-8" />

        <x-ui.card class="mt-8" data-tour="submit">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Submit a Contribution</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Create a new resource to share with the laboratory community.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button href="{{ route('account.contributions.create', ['type' => 'sop']) }}" size="sm">New SOP</x-ui.button>
                    <x-ui.button href="{{ route('account.contributions.create', ['type' => 'knowledge']) }}" size="sm">New Knowledge</x-ui.button>
                    <x-ui.button href="{{ route('account.contributions.create', ['type' => 'interpretation']) }}" size="sm">New Interpretation</x-ui.button>
                </div>
            </div>
        </x-ui.card>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-tour="stats">
            @foreach([
                'draft' => 'Drafts',
                'under_review' => 'Under Review',
                'published' => 'Published',
                'rejected' => 'Rejected',
            ] as $statusValue => $label)
                <a href="{{ route('account.contributions.index', ['status' => $statusValue]) }}"
                   class="rounded-2xl border border-slate-200 bg-white p-6 shadow-panel transition-colors hover:border-primary-600/40 hover:shadow-lg dark:border-slate-800 dark:bg-slate-800 dark:hover:border-primary-600/40">
                    <div class="text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $stats[$statusValue] ?? 0 }}</div>
                    <div class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</div>
                </a>
            @endforeach
        </div>

        @php $remaining = collect($checklist)->where('done', false)->count(); @endphp
        @if($remaining > 0)
            <div class="mt-8" data-tour="checklist">
                <x-ui.card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Getting started</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ collect($checklist)->where('done', true)->count() }} of {{ count($checklist) }} complete
                                &mdash; finish these to get the most out of {{ config('app.name') }}.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                            x-data
                            x-on:click="if (window.LabgotoTour) window.LabgotoTour.start('member-dashboard')"
                        >
                            Replay tour
                        </button>
                    </div>

                    <div class="mt-4 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700" role="progressbar"
                         aria-valuenow="{{ $remaining ? round((count($checklist) - $remaining) / count($checklist) * 100) : 100 }}"
                         aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-primary-600 transition-all duration-500 dark:bg-primary-500"
                             style="width: {{ $remaining ? round((count($checklist) - $remaining) / count($checklist) * 100) : 100 }}%"></div>
                    </div>

                    <ul class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3" role="list">
                        @foreach($checklist as $item)
                            <li class="flex items-start justify-between gap-3 rounded-xl border p-4 {{ $item['done']
                                ? 'border-primary-200 bg-primary-50/60 dark:border-primary-800/60 dark:bg-primary-900/20'
                                : 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800' }}">
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $item['done']
                                        ? 'bg-primary-600 text-white dark:bg-primary-500'
                                        : 'border border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-700' }}">
                                        @if($item['done'])
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium {{ $item['done'] ? 'text-primary-800 line-through decoration-primary-400 dark:text-primary-200' : 'text-slate-900 dark:text-slate-100' }}">
                                            {{ $item['label'] }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $item['description'] }}</p>
                                    </div>
                                </div>
                                @unless($item['done'])
                                    <a href="{{ $item['href'] }}"
                                       class="shrink-0 rounded-lg px-2.5 py-1.5 text-xs font-medium text-primary-700 transition-colors hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-primary-300 dark:hover:bg-primary-900/40">
                                        Go
                                    </a>
                                @endunless
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </div>
        @endif

        @if($recentlyViewed->isNotEmpty())
            <div class="mt-8">
                <x-ui.card>
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Recently Viewed</h2>
                    </div>
                    <ul class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3" role="list" aria-label="Recently viewed resources">
                        @foreach($recentlyViewed->take(8) as $item)
                            <li>
                                <a href="{{ $item->url ?? '#' }}" class="flex flex-col h-full p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-medium mb-2">
                                        {{ $item->resource_type ?? 'Resource' }}
                                    </span>
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 line-clamp-2">{{ $item->name ?? 'Unknown resource' }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </div>
        @endif

        @if($recommended->isNotEmpty())
            <div class="mt-6">
                <x-ui.card>
                    <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Recommended for you</h2>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($recommended as $resource)
                            <a href="{{ $resource->url ?? '#' }}" class="flex flex-col h-full p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-medium mb-2 w-fit">
                                    {{ class_basename($resource->resource_type ?? 'Resource') }}
                                </span>
                                <h3 class="font-display text-base font-bold text-slate-900 dark:text-slate-100 group-hover:text-primary-700 transition-colors">
                                    {{ $resource->name }}
                                </h3>
                                @if($resource->short_description)
                                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300 line-clamp-2">{{ $resource->short_description }}</p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>
        @endif

        <div class="mt-6" data-tour="recent">
            <x-ui.card>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-slate-100">Recent Contributions</h2>
                    <x-ui.button href="{{ route('account.contributions.index') }}" variant="secondary" size="sm">View All</x-ui.button>
                </div>

                @if($recent->isEmpty())
                    <div class="mt-6">
                        <x-ui.empty-state
                            title="No contributions yet"
                            description="Create your first contribution to share laboratory knowledge with the community."
                        >
                            <x-slot:action>
                                <x-ui.button href="{{ route('account.contributions.create', ['type' => 'sop']) }}">
                                    New Contribution
                                </x-ui.button>
                            </x-slot:action>
                        </x-ui.empty-state>
                    </div>
                @else
                    <div class="mt-4 divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($recent as $entry)
                            <div class="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('account.contributions.show', ['type' => $entry->type, 'id' => $entry->id]) }}"
                                       class="text-sm font-medium text-slate-900 hover:text-primary-600 dark:text-slate-100 dark:hover:text-primary-400">
                                        {{ $entry->name }}
                                    </a>
                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-700 border border-primary-600/20 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-600/30">
                                        {{ $entry->type_label }}
                                    </span>
                                    <x-ui.badge :variant="match($entry->status->color()) {
                                        'green' => 'success',
                                        'red' => 'danger',
                                        'yellow' => 'warning',
                                        'blue' => 'info',
                                        default => 'default',
                                    }">
                                        {{ $entry->status->label() }}
                                    </x-ui.badge>
                                </div>
                                <div class="flex items-center gap-3 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                    <span>{{ $entry->updated_at?->format('M j, Y') ?? '—' }}</span>
                                    <x-ui.button
                                        href="{{ route('account.contributions.show', ['type' => $entry->type, 'id' => $entry->id]) }}"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        View
                                    </x-ui.button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</section>

<x-onboarding.tour tour-key="member-dashboard" :steps="$tourSteps" :autostart="auth()->user()->needsTour('member-dashboard')" />
@endsection
