@extends('layouts.public')

@section('title', 'Professional Laboratory Toolkit - ' . config('app.name'))

@section('content')
<section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold mb-6">LabGato</h1>
        <p class="text-xl sm:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
            Your professional laboratory toolkit. Essential tools, calculators, reference ranges, and knowledge resources for laboratory professionals.
        </p>
        <div class="flex justify-center">
            <a href="#resources" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 transition-colors">
                Explore Resources
            </a>
        </div>
    </div>
</section>

<section id="resources" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Laboratory Resources</h2>
            <p class="mt-2 text-gray-600 max-w-2xl mx-auto">
                Comprehensive resources designed to support your laboratory workflow and professional development.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Laboratory Tools</h3>
                <p class="text-gray-600 text-sm">Essential tools and utilities for daily laboratory operations.</p>
                <p class="mt-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Coming Soon</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Calculators</h3>
                <p class="text-gray-600 text-sm">Clinical calculators for common laboratory calculations.</p>
                <p class="mt-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Coming Soon</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Reference Ranges</h3>
                <p class="text-gray-600 text-sm">Comprehensive reference ranges for clinical laboratory testing.</p>
                <p class="mt-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Coming Soon</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-yellow-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">SOP Resources</h3>
                <p class="text-gray-600 text-sm">Standard operating procedures and protocol documentation.</p>
                <p class="mt-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Coming Soon</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Result Interpretation</h3>
                <p class="text-gray-600 text-sm">Guidance for interpreting laboratory results and clinical significance.</p>
                <p class="mt-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Coming Soon</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Knowledge & Education</h3>
                <p class="text-gray-600 text-sm">Educational resources and continuing education materials.</p>
                <p class="mt-3 text-xs font-medium text-gray-400 uppercase tracking-wider">Coming Soon</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">About LabGato</h2>
        <p class="text-gray-600 max-w-3xl mx-auto">
            LabGato is designed to be a comprehensive resource for laboratory professionals. Whether you need quick reference ranges, clinical calculators, or standard operating procedures, LabGato aims to provide the tools you need in one place.
        </p>
    </div>
</section>
@endsection
