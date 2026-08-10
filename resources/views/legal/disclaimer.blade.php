@extends('layouts.public')

@section('title', 'Disclaimer - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">Disclaimer</h1>
    <p class="text-sm text-gray-500 mb-8">Last updated: January 1, 2024</p>

    <div class="prose prose-blue max-w-none text-gray-600 space-y-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-red-800">
                <strong>Important:</strong> This disclaimer must be reviewed by a qualified legal professional before production deployment.
            </p>
        </div>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Not a Diagnostic Tool</h2>
            <p>
                LabGato is an educational and reference resource for laboratory professionals. <strong>LabGato is not a diagnostic tool</strong> and should not be used as the sole basis for any clinical decision. The information provided through LabGato is for informational and educational purposes only.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Does Not Replace Professional Clinical Judgment</h2>
            <p>
                <strong>LabGato does not replace professional clinical judgment.</strong> Healthcare professionals must exercise their own clinical judgment when interpreting laboratory results and making patient care decisions. Always consult current clinical guidelines, institutional protocols, and relevant clinical literature.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">No Warranty</h2>
            <p>
                While we strive to provide accurate and up-to-date information, LabGato makes no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, or suitability of the information contained herein.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Professional Responsibility</h2>
            <p>
                Users of LabGato are responsible for verifying the accuracy and appropriateness of any information before applying it in a clinical setting. Always follow your institution's policies and procedures.
            </p>
        </section>
    </div>
</div>
@endsection
