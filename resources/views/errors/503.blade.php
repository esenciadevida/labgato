@extends('layouts.error')

@section('title', 'Service Unavailable')

@section('content')
<div class="max-w-md mx-auto text-center">
    <h1 class="text-6xl sm:text-7xl font-bold text-primary-400">503</h1>
    <p class="text-xl font-semibold text-white mt-4">Maintenance</p>
    <p class="text-slate-400 mt-2">We're undergoing maintenance. We'll be back shortly.</p>
</div>
@endsection
