@extends('layouts.admin')

@section('title', 'Edit ' . $knowledge->name . ' - ' . config('app.name'))

@section('header', 'Edit Knowledge Resource')

@section('content')
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
