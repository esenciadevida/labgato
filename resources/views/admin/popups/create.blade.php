@extends('layouts.admin')

@section('title', 'New Pop-up - ' . config('app.name'))

@section('header', 'New Pop-up')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.popups.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.popups.partials.form', [
        'action' => route('admin.popups.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Pop-up',
    ])
</div>
@endsection
