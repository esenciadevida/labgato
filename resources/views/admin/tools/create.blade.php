@extends('layouts.admin')

@section('title', 'New Tool - ' . config('app.name'))

@section('header', 'New Tool')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.tools.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.tools.partials.form', [
        'action' => route('admin.tools.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Tool',
        'statuses' => $statuses,
    ])
</div>
@endsection
