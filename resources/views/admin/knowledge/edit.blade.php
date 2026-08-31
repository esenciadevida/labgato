@extends('layouts.admin')

@section('title', 'Edit ' . $knowledge->name . ' - ' . config('app.name'))

@section('header', 'Edit Knowledge Resource')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3">
    <x-ui.button href="{{ route('admin.knowledge.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
    <x-ui.button href="{{ route('admin.knowledge.preview', $knowledge) }}" variant="secondary" size="sm" target="_blank">
        Preview
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">

    @include('admin.knowledge.partials.form', [
        'action' => route('admin.knowledge.update', $knowledge),
        'method' => 'PUT',
        'submitLabel' => 'Save Knowledge Resource',
        'showSaveAndCreate' => false,
        'statuses' => $statuses,
    ])

    @if(! $knowledge->isPublished())
        <form method="POST" action="{{ route('admin.knowledge.publish', $knowledge) }}" class="mt-4">
            @csrf
            @method('PATCH')
            <x-ui.button type="submit" variant="success">
                Publish Knowledge Resource
            </x-ui.button>
        </form>
    @endif
</div>
@endsection
