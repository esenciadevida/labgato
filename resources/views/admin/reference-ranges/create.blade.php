@extends('layouts.admin')

@section('title', 'New Reference Range - ' . config('app.name'))

@section('header', 'New Reference Range')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.reference-ranges.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.reference-ranges.partials.form', [
        'action' => route('admin.reference-ranges.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Reference Range',
        'showSaveAndCreate' => true,
        'statuses' => $statuses,
    ])
</div>
@endsection
