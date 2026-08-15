@extends('layouts.admin')

@section('title', 'Edit SOP - ' . config('app.name'))

@section('header', 'Edit SOP')

@section('content')
<div class="mx-auto max-w-3xl">

    @include('admin.sops.partials.form', [
        'sop' => $sop,
        'statuses' => $statuses,
        'action' => route('admin.sops.update', $sop),
        'method' => 'PUT',
        'submitLabel' => 'Update SOP',
    ])

    @if(! $sop->isPublished())
        <form method="POST" action="{{ route('admin.sops.publish', $sop) }}" class="mt-4">
            @csrf
            @method('PATCH')
            <x-ui.button type="submit" variant="success">
                Publish SOP
            </x-ui.button>
        </form>
    @endif
</div>
@endsection
