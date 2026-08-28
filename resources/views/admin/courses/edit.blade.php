@extends('layouts.admin')

@section('title', 'Edit ' . $course->title . ' - ' . config('app.name'))

@section('header', 'Edit Course')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.courses.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-4xl">
    @include('admin.courses.partials.form', [
        'course' => $course,
        'assessments' => $assessments,
        'action' => route('admin.courses.update', $course),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
</div>
@endsection