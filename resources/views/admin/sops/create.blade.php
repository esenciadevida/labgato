@extends('layouts.admin')

@section('title', 'New SOP - ' . config('app.name'))

@section('header', 'New SOP')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.sops.partials.form', [
        'action' => route('admin.sops.store'),
        'method' => 'POST',
        'submitLabel' => 'Save SOP',
        'showSaveAndCreate' => true,
        'statuses' => $statuses,
    ])
</div>
@endsection
