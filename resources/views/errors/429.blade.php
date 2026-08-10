@extends('layouts.error')

@section('title', 'Too Many Requests')

@section('content')
<div class="max-w-md mx-auto text-center">
    <h1 class="text-6xl font-bold text-gray-900">429</h1>
    <p class="text-xl font-semibold text-gray-700 mt-4">Too Many Requests</p>
    <p class="text-gray-500 mt-2">You have made too many requests. Please wait a moment and try again.</p>
    <div class="mt-6">
        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Return Home
        </a>
    </div>
</div>
@endsection
