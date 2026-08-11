@extends('layouts.error')

@section('title', 'Something Went Wrong')

@section('content')
<div class="max-w-md mx-auto text-center">
    <h1 class="text-6xl font-bold text-neutral-900">500</h1>
    <p class="text-xl font-semibold text-neutral-700 mt-4">Something Went Wrong</p>
    <p class="text-neutral-500 mt-2">An unexpected error occurred. Please try again later.</p>
    <div class="mt-6">
        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Return Home
        </a>
    </div>
</div>
@endsection
