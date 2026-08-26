@extends('layouts.admin')

@section('title', 'Edit ' . $interpretation->name . ' - ' . config('app.name'))

@section('header', 'Edit Interpretation Resource')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.interpretation.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">

    @include('admin.interpretation.partials.form', [
        'action' => route('admin.interpretation.update', $interpretation),
        'method' => 'PUT',
        'submitLabel' => 'Save Interpretation Resource',
        'showSaveAndCreate' => false,
        'statuses' => $statuses,
    ])

    @if(! $interpretation->isPublished())
        <form method="POST" action="{{ route('admin.interpretation.publish', $interpretation) }}" class="mt-4">
            @csrf
            @method('PATCH')
            <x-ui.button type="submit" variant="success">
                Publish Interpretation Resource
            </x-ui.button>
        </form>
    @endif
</div>
@endsection
