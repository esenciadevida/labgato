@extends('layouts.error')

@section('title', 'Access Denied')

@section('content')
<div class="max-w-md mx-auto text-center">
    <h1 class="text-6xl sm:text-7xl font-bold text-primary-400">403</h1>
    <p class="text-xl font-semibold text-white mt-4">Access Denied</p>
    <p class="text-slate-400 mt-2">Access denied. You don't have permission to view this page.</p>
</div>
@endsection
