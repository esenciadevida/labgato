@extends('layouts.admin')

@section('title', 'New Assessment - ' . config('app.name'))

@section('header', 'New Assessment')

@section('content')
<div class="mx-auto max-w-4xl">
    @include('admin.assessments.partials.form', [
        'action' => route('admin.assessments.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Assessment',
    ])
</div>
@endsection
