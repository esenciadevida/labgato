@extends('layouts.public')

@section('title', 'Edit ' . $type->label() . ' Contribution - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="mb-8">
            <a href="{{ route('account.contributions.show', ['type' => $type->value, 'id' => $resource->id]) }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                &larr; Back to contribution
            </a>
            <h1 class="mt-2 font-display text-2xl font-bold text-slate-900 dark:text-slate-100">
                Edit {{ $type->label() }} Contribution
            </h1>
            @if($resource->status === \App\Enums\ResourceStatus::REJECTED)
                <div class="mt-4 rounded-lg border border-danger-200 bg-danger-50 p-4 dark:border-danger-700 dark:bg-danger-900/20">
                    <h2 class="text-sm font-semibold text-danger-700 dark:text-danger-300">Rejection reason</h2>
                    <p class="mt-1 text-sm text-danger-600 dark:text-danger-300">{{ $resource->rejection_reason }}</p>
                </div>
            @endif
        </div>

        @if($errors->any())
            <x-ui.alert type="error">Please correct the errors below and try again.</x-ui.alert>
        @endif

        <div class="mt-6">
            @include('member.contributions.partials.' . $type->value . '-form', [
                'contribution' => $resource,
                'action' => route('account.contributions.update', ['type' => $type->value, 'id' => $resource->id]),
                'method' => 'PUT',
            ])
        </div>
    </div>
</section>
@endsection
