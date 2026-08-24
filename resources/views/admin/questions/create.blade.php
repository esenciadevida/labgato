@extends('layouts.admin')

@section('title', 'New Question - ' . config('app.name'))

@section('header', 'New Question')

@section('content')
<form method="POST" action="{{ route('admin.questions.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    @include('admin.questions.partials.question-form')
</form>
@endsection
