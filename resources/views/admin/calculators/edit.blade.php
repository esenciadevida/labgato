@extends('layouts.admin')

@section('title', 'Edit ' . $calculator->name . ' - ' . config('app.name'))

@section('header', 'Edit Calculator')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.calculators.partials.form', [
        'action' => route('admin.calculators.update', $calculator),
        'method' => 'PUT',
        'submitLabel' => 'Save Calculator',
    ])
</div>
@endsection
