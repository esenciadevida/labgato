@extends('layouts.admin')

@section('title', 'Edit ' . $assessment->title . ' - ' . config('app.name'))

@section('header', 'Edit Assessment')

@section('content')
<div class="mx-auto max-w-4xl">
    @include('admin.assessments.partials.form', [
        'assessment' => $assessment,
        'categories' => $categories,
        'action' => route('admin.assessments.update', $assessment),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
</div>
@endsection
