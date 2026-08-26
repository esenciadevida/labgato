@extends('layouts.admin')

@section('title', 'New Announcement - ' . config('app.name'))

@section('header', 'New Announcement')

@section('content')
<div class="mb-6">
    <x-ui.button href="{{ route('admin.announcements.index') }}" variant="ghost" size="sm">
        &larr; Back
    </x-ui.button>
</div>
<div class="mx-auto max-w-3xl">
    @include('admin.announcements.partials.form', [
        'action' => route('admin.announcements.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Announcement',
    ])
</div>
@endsection
