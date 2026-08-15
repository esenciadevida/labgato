@extends('layouts.public')

@section('title', 'New ' . $type->label() . ' Contribution - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-4xl px-4 sm:px-6">
        <div class="mb-8">
            <a href="{{ route('account.contributions.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">
                &larr; Back to my contributions
            </a>
            <h1 class="mt-2 font-display text-2xl font-bold text-slate-900 dark:text-slate-100">
                New {{ $type->label() }} Contribution
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Your contribution will be reviewed by our team before it is published.
            </p>
        </div>

        @if($errors->any())
            <x-ui.alert type="error">Please correct the errors below and try again.</x-ui.alert>
        @endif

        <div class="mt-6">
            @include('member.contributions.partials.' . $type->value . '-form', [
                'action' => route('account.contributions.store', ['type' => $type->value]),
                'method' => 'POST',
            ])
        </div>
    </div>
</section>
@endsection
