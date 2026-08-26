@extends('layouts.admin')

@section('title', 'New Interpretation Resource - ' . config('app.name'))

@section('header', 'New Interpretation Resource')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.interpretation.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.interpretation.partials.form', [
        'action' => route('admin.interpretation.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Interpretation Resource',
        'showSaveAndCreate' => true,
        'statuses' => $statuses,
    ])
</div>
@endsection
