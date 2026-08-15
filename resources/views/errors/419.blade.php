@extends('layouts.error')

@section('title', 'Session Expired')

@section('content')
<div class="max-w-md mx-auto text-center">
    <h1 class="text-6xl font-bold text-slate-900 dark:text-slate-100">419</h1>
    <p class="text-xl font-semibold text-slate-700 mt-4 dark:text-slate-300">Session Expired</p>
    <p class="text-slate-500 dark:text-slate-400 mt-2">Your session has expired. Please refresh the page and try again.</p>
    <div class="mt-6">
        <a href="/" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Return Home
        </a>
    </div>
</div>
@endsection
