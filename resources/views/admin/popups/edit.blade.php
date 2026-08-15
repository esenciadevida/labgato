@extends('layouts.admin')

@section('title', 'Edit Pop-up - ' . config('app.name'))

@section('header', 'Edit Pop-up')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.popups.partials.form', [
        'popup' => $popup,
        'action' => route('admin.popups.update', $popup),
        'method' => 'PUT',
        'submitLabel' => 'Update Pop-up',
    ])
</div>
@endsection
