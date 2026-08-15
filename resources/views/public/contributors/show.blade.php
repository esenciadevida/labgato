@extends('layouts.public')

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <header class="flex items-start gap-6">
            <x-public.avatar :name="$user->name" :user-id="$user->id" size="lg" />
            <div>
                <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $user->name }}</h1>

                @if($user->bio)
                    <p class="mt-2 text-slate-600 dark:text-slate-400">{{ $user->bio }}</p>
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
        "name": "{{ $user->name }}",
        "description": "{{ $user->bio }}",
        "url": "{{ url()->current() }}"
    }
}
</script>
@endpush
