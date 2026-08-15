@extends('layouts.admin')

@section('title', 'New Announcement - ' . config('app.name'))

@section('header', 'New Announcement')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.announcements.partials.form', [
        'action' => route('admin.announcements.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Announcement',
    ])
</div>
@endsection
