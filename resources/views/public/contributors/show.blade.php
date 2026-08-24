@extends('layouts.public')

@section('content')
@php $profile = $user->profile; @endphp
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <header class="flex items-start gap-6">
            <x-public.avatar :name="$profile->display_name ?: $user->name" :src="$profile->photo_url" size="lg" />
            <div>
                <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $profile->display_name ?: $user->name }}</h1>

                @if($profile->statusLabel())
                    <p class="mt-2">
                        <span class="inline-flex items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">{{ $profile->statusLabel() }}</span>
                    </p>
                @endif

                @if($profile->show_country && $profile->country)
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $profile->country }}</p>
                @endif

                @if($profile->show_institution)
                    @php
                        $affiliation = $profile->isStudent()
                            ? trim(($profile->institution ?? '').($profile->programme ? ' - '.$profile->programme : ''))
                            : trim(($profile->employer ?? '').($profile->job_title ? ' - '.$profile->job_title : ''));
                    @endphp
                    @if($affiliation !== '')
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $affiliation }}</p>
                    @endif
                @endif

                @if($user->bio)
                    <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-400">{{ $user->bio }}</p>
                @endif

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Member since {{ $user->created_at->format('F Y') }}
                </p>
            </div>
        </header>

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
