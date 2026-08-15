@extends('layouts.admin')

@section('title', 'Edit ' . $referenceRange->name . ' - ' . config('app.name'))

@section('header', 'Edit Reference Range')

@section('content')
<div class="mx-auto max-w-3xl">

    @include('admin.reference-ranges.partials.form', [
        'action' => route('admin.reference-ranges.update', $referenceRange),
        'method' => 'PUT',
        'submitLabel' => 'Save Reference Range',
        'showSaveAndCreate' => false,
        'statuses' => $statuses,
    ])

    @if(! $referenceRange->isPublished())
        <form method="POST" action="{{ route('admin.reference-ranges.publish', $referenceRange) }}" class="mt-4">
            @csrf
            @method('PATCH')
            <x-ui.button type="submit" variant="success">
                Publish Reference Range
            </x-ui.button>
        </form>
    @endif
</div>
@endsection
