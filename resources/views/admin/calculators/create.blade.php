@extends('layouts.admin')

@section('title', 'New Calculator - ' . config('app.name'))

@section('header', 'New Calculator')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.calculators.partials.form', [
        'action' => route('admin.calculators.store'),
        'method' => 'POST',
        'submitLabel' => 'Create Calculator',
    ])
</div>
@endsection
