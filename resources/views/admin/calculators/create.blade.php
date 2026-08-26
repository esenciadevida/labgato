@extends('layouts.admin')

@section('title', 'New Calculator - ' . config('app.name'))

@section('header', 'New Calculator')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.calculators.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.calculators.partials.form', [
        'action' => route('admin.calculators.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Calculator',
        'statuses' => $statuses,
    ])
</div>
@endsection
