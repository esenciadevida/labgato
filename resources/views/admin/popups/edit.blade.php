@extends('layouts.admin')

@section('title', 'Edit Pop-up - ' . config('app.name'))

@section('header', 'Edit Pop-up')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.popups.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.popups.partials.form', [
        'popup' => $popup,
        'action' => route('admin.popups.update', $popup),
        'method' => 'PUT',
        'submitLabel' => 'Update Pop-up',
    ])
</div>
@endsection
