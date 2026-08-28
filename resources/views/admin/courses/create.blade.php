@extends('layouts.admin')

@section('title', 'New Course - ' . config('app.name'))

@section('header', 'New Course')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.courses.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-4xl">
    @include('admin.courses.partials.form', [
        'action' => route('admin.courses.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Course',
    ])
</div>
@endsection