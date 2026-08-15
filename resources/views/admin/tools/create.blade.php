@extends('layouts.admin')

@section('title', 'New Tool - ' . config('app.name'))

@section('header', 'New Tool')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.tools.partials.form', [
        'action' => route('admin.tools.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Tool',
        'statuses' => $statuses,
    ])
</div>
@endsection
