@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100 mb-4">Disclaimer</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">Last updated: January 1, 2024</p>

    <div class="prose max-w-none text-slate-600 dark:text-slate-300 space-y-6">
        <div class="bg-danger-50 border border-danger-600/20 rounded-lg p-4 mb-6">
            <p class="text-sm text-danger-600">
                <strong>Important:</strong> This disclaimer must be reviewed by a qualified legal professional before production deployment.
            </p>
        </div>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">Not a Diagnostic Tool</h2>
            <p>
                Labgoto is an educational and reference resource for laboratory professionals. <strong>Labgoto is not a diagnostic tool</strong> and should not be used as the sole basis for any clinical decision. The information provided through Labgoto is for informational and educational purposes only.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">Does Not Replace Professional Clinical Judgment</h2>
            <p>
                <strong>Labgoto does not replace professional clinical judgment.</strong> Healthcare professionals must exercise their own clinical judgment when interpreting laboratory results and making patient care decisions. Always consult current clinical guidelines, institutional protocols, and relevant clinical literature.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">No Warranty</h2>
            <p>
                While we strive to provide accurate and up-to-date information, Labgoto makes no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, or suitability of the information contained herein.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">Professional Responsibility</h2>
            <p>
                Users of Labgoto are responsible for verifying the accuracy and appropriateness of any information before applying it in a clinical setting. Always follow your institution's policies and procedures.
            </p>
        </section>
    </div>
</div>
@endsection
