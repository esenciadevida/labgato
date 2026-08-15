@extends('layouts.public')

@section('title', $resource->name . ' - Contribution - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="mb-8">
            <a href="{{ route('account.contributions.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                &larr; Back to my contributions
            </a>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-700 border border-primary-600/20 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-600/30">
                    {{ $type->label() }}
                </span>
                <x-ui.badge :variant="match($resource->status->color()) {
                    'green' => 'success',
                    'red' => 'danger',
                    'yellow' => 'warning',
                    'blue' => 'info',
                    default => 'default',
                }">
                    {{ $resource->status->label() }}
                </x-ui.badge>
            </div>

            <h1 class="mt-3 font-display text-3xl font-bold text-slate-900 dark:text-slate-100">
                {{ $resource->name }}
            </h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400">{{ $resource->short_description }}</p>

            @if($resource->status === \App\Enums\ResourceStatus::REJECTED && $resource->rejection_reason)
                <div class="mt-6 rounded-lg border border-danger-200 bg-danger-50 p-4 dark:border-danger-700 dark:bg-danger-900/20">
                    <h2 class="text-sm font-semibold text-danger-700 dark:text-danger-300">Rejection reason</h2>
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-300">{{ $resource->rejection_reason }}</p>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if($resource->status->canMemberEdit())
                    <x-ui.button href="{{ route('account.contributions.edit', ['type' => $type->value, 'id' => $resource->id]) }}" variant="secondary">
                        Edit
                    </x-ui.button>
                @endif

                @if($resource->status === \App\Enums\ResourceStatus::DRAFT)
                    <form method="POST" action="{{ route('account.contributions.submit', ['type' => $type->value, 'id' => $resource->id]) }}">
                        @csrf
                        <x-ui.button type="submit">
                            Submit for Review
                        </x-ui.button>
                    </form>
                @endif

                @if($resource->status === \App\Enums\ResourceStatus::REJECTED)
                    <form method="POST" action="{{ route('account.contributions.resubmit', ['type' => $type->value, 'id' => $resource->id]) }}">
                        @csrf
                        <x-ui.button type="submit">
                            Resubmit for Review
                        </x-ui.button>
                    </form>
                @endif

                @if($resource->status->canMemberWithdraw())
                    <form method="POST" action="{{ route('account.contributions.withdraw', ['type' => $type->value, 'id' => $resource->id]) }}">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">
                            Withdraw to Draft
                        </x-ui.button>
                    </form>
                @endif

                @if($resource->status->canMemberDelete())
                    <form method="POST" action="{{ route('account.contributions.destroy', ['type' => $type->value, 'id' => $resource->id]) }}"
                          data-confirm="Are you sure you want to delete this contribution? This cannot be undone.">
                        @csrf
                        @method('DELETE')
                        <x-ui.button type="submit" variant="danger">
                            Delete
                        </x-ui.button>
                    </form>
                @endif

                @if($resource->status === \App\Enums\ResourceStatus::PUBLISHED)
                    <x-ui.button
                        href="{{ match ($type) {
                            'sop' => route('public.sops.show', $resource->slug),
                            'knowledge' => route('public.knowledge.show', $resource->slug),
                            'interpretation' => route('public.interpretation.show', $resource->slug),
                        } }}"
                        variant="ghost"
                    >
                        View on public site
                    </x-ui.button>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-panel dark:border-slate-800 dark:bg-slate-800">
            <article class="prose prose-neutral max-w-none dark:prose-invert prose-h1:text-2xl prose-h2:text-xl">
                {!! $content !!}
            </article>
        </div>
    </div>
</section>
@endsection
