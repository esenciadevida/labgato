@extends('layouts.admin')

@section('title', 'New Knowledge Resource - ' . config('app.name'))

@section('header', 'New Knowledge Resource')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.knowledge.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.knowledge.partials.form', [
        'action' => route('admin.knowledge.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Knowledge Resource',
        'showSaveAndCreate' => true,
        'statuses' => $statuses,
    ])
</div>
@endsection
