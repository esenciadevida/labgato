@extends('layouts.admin')

@section('title', 'Edit ' . $calculator->name . ' - ' . config('app.name'))

@section('header', 'Edit Calculator')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.calculators.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">

    @include('admin.calculators.partials.form', [
        'action' => route('admin.calculators.update', $calculator),
        'method' => 'PUT',
        'submitLabel' => 'Save Calculator',
        'statuses' => $statuses,
    ])

    @if(! $calculator->isPublished())
        <form method="POST" action="{{ route('admin.calculators.publish', $calculator) }}" class="mt-4">
            @csrf
            @method('PATCH')
            <x-ui.button type="submit" variant="success">
                Publish Calculator
            </x-ui.button>
        </form>
    @endif
</div>
@endsection
