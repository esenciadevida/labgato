@extends('layouts.public')

@section('content')
@php
    $profile = $user->profile;
    $displayName = $profile->display_name ?: $user->name;
    $initials = collect(preg_split('/\s+/', trim($displayName)))
        ->filter()
        ->map(fn (string $word) => strtoupper(mb_substr($word, 0, 1)))
        ->take(2)
        ->implode('') ?: '?';
@endphp
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-card dark:border-slate-800 dark:bg-slate-800">
            <div class="h-24 bg-gradient-to-r from-primary-50 via-slate-50 to-primary-50 border-b border-slate-200 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800 dark:border-slate-700" aria-hidden="true"></div>

            <div class="px-6 pb-6 -mt-12">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end">
                    <div class="shrink-0">
                        <div class="h-24 w-24 overflow-hidden rounded-full bg-slate-100 ring-4 ring-white shadow-md dark:bg-slate-700 dark:ring-slate-800">
                            @if($profile->photo_url)
                                <img src="{{ $profile->photo_url }}" alt="{{ $displayName }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <span class="flex h-full w-full items-center justify-center bg-primary-600 font-semibold text-white">{{ $initials }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-1.5 pb-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="font-display text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $displayName }}</h1>
                            @if($profile->statusLabel())
                                <span class="inline-flex items-center rounded-full border border-primary-100 bg-primary-50 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:border-primary-800 dark:bg-primary-900/30 dark:text-primary-300">
                                    {{ $profile->statusLabel() }}
                                </span>
                            @endif
                        </div>

                        @if($profile->show_country && $profile->country)
                            <p class="text-sm text-slate-600 dark:text-slate-300">{{ $profile->country }}</p>
                        @endif

                        @if($profile->show_institution)
                            @php
                                $affiliation = $profile->isStudent()
                                    ? trim(($profile->institution ?? '').($profile->programme ? ' - '.$profile->programme : ''))
                                    : trim(($profile->employer ?? '').($profile->job_title ? ' - '.$profile->job_title : ''));
                            @endphp
                            @if($affiliation !== '')
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $affiliation }}</p>
                            @endif
                        @endif

                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Member since {{ $user->created_at->format('F Y') }}
                        </p>
                    </div>
                </div>

                @if($user->bio)
                    <p class="mt-4 max-w-2xl border-t border-slate-100 pt-4 text-sm leading-relaxed text-slate-600 dark:border-slate-700 dark:text-slate-400">{{ $user->bio }}</p>
                @endif
            </div>
        </div>

        <div class="mt-12">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4">Contributions</h2>

            @if($contributions->isEmpty())
                <x-ui.empty-state
                    title="No published contributions"
                    description="This contributor has no published contributions yet."
                />
            @else
                <ul class="space-y-4">
                    @foreach($contributions as $contribution)
                        @php
                            $url = match ($contribution['type']) {
                                'sop' => route('public.sops.show', $contribution['slug']),
                                'knowledge' => route('public.knowledge.show', $contribution['slug']),
                                'interpretation' => route('public.interpretation.show', $contribution['slug']),
                                default => null,
                            };
                            [$badgeVariant, $typeLabel] = match ($contribution['type']) {
                                'sop' => ['danger', 'SOP'],
                                'knowledge' => ['warning', 'Knowledge'],
                                'interpretation' => ['info', 'Interpretation'],
                                default => ['default', ucfirst($contribution['type'])],
                            };
                        @endphp
                        <li>
                            <x-ui.card>
                                <a href="{{ $url }}" class="block rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $contribution['name'] }}</h3>
                                        <x-ui.badge :variant="$badgeVariant">{{ $typeLabel }}</x-ui.badge>
                                    </div>

                                    @if($contribution['short_description'])
                                        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ $contribution['short_description'] }}</p>
                                    @endif

                                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                        Published {{ $contribution['published_at']->format('M j, Y') }}
                                    </p>
                                </a>
                            </x-ui.card>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    {{ $contributions->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "ProfilePage",
    "mainEntity": {
        "@type": "Person",
        "name": "{{ $profile->display_name ?: $user->name }}",
        "description": "{{ $user->bio }}",
        @if($profile->show_country && $profile->country)
        "homeLocation": { "@@type": "Place", "address": "{{ $profile->country }}" },
        @endif
        "url": "{{ url()->current() }}"
    }
}
</script>
@endpush
