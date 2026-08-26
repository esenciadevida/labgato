@extends('layouts.admin')

@section('title', 'New Assessment - ' . config('app.name'))

@section('header', 'New Assessment')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.assessments.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-4xl">
    @include('admin.assessments.partials.form', [
        'action' => route('admin.assessments.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Assessment',
    ])
</div>
@endsection
