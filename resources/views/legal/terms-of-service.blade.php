@extends('layouts.public')

@section('title', 'Terms of Service - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">Terms of Service</h1>
    <p class="text-sm text-gray-500 mb-8">Last updated: January 1, 2024</p>

    <div class="prose prose-blue max-w-none text-gray-600 space-y-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800">
                <strong>Disclaimer:</strong> This is a placeholder terms of service document. The final legal text must be reviewed and approved by a qualified legal professional before production deployment.
            </p>
        </div>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Acceptance of Terms</h2>
            <p>
                By accessing and using LabGato, you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the platform.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Use of the Platform</h2>
            <p>
                LabGato is provided as a resource for laboratory professionals. You agree to use the platform only for lawful purposes and in accordance with these terms.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Intellectual Property</h2>
            <p>
                All content, features, and functionality of LabGato are owned by us and are protected by copyright, trademark, and other intellectual property laws.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Limitation of Liability</h2>
            <p>
                LabGato is provided "as is" without warranties of any kind. We shall not be liable for any indirect, incidental, special, consequential, or punitive damages.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Changes to Terms</h2>
            <p>
                We reserve the right to modify these terms at any time. Changes will be effective when posted. Your continued use constitutes acceptance of the modified terms.
            </p>
        </section>
    </div>
</div>
@endsection
