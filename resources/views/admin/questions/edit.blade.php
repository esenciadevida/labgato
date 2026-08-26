@extends('layouts.admin')

@section('title', 'Edit Question - ' . config('app.name'))

@section('header', 'Edit Question')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.questions.show', $question) }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<form method="POST" action="{{ route('admin.questions.update', $question) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    @include('admin.questions.partials.question-form')
</form>
@endsection
