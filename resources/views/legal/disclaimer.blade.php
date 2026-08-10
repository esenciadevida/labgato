@extends('layouts.public')

@section('title', 'Disclaimer - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold mb-8">Disclaimer</h1>
    <div class="prose prose-blue max-w-none">
        <p class="text-gray-600">Disclaimer content will be added here.</p>
    </div>
</div>
@endsection
