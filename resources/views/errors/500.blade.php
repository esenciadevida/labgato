@extends('layouts.error')

@section('title', 'Something Went Wrong')

@section('content')
<div class="max-w-md mx-auto text-center">
    <h1 class="text-6xl sm:text-7xl font-bold text-primary-400">500</h1>
    <p class="text-xl font-semibold text-white mt-4">Something Went Wrong</p>
    <p class="text-slate-400 mt-2">Something went wrong on our end. Please try again shortly.</p>
</div>
@endsection
