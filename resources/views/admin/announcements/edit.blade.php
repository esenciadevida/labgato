@extends('layouts.admin')

@section('title', 'Edit Announcement - ' . config('app.name'))

@section('header', 'Edit Announcement')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.announcements.partials.form', [
        'announcement' => $announcement,
        'action' => route('admin.announcements.update', $announcement),
        'method' => 'PUT',
        'submitLabel' => 'Update Announcement',
    ])
</div>
@endsection
