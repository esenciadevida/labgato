@extends('layouts.public')

@section('title', 'Cookie Policy - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 mb-4">Cookie Policy</h1>
    <p class="text-sm text-neutral-500 mb-8">Last updated: January 1, 2024</p>

    <div class="prose max-w-none text-neutral-600 space-y-6">
        <div class="bg-warning-50 border border-warning-600/20 rounded-lg p-4 mb-6">
            <p class="text-sm text-warning-600">
                <strong>Disclaimer:</strong> This is a placeholder cookie policy. The final legal text must be reviewed and approved by a qualified legal professional before production deployment.
            </p>
        </div>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">What Are Cookies</h2>
            <p>
                Cookies are small text files that are placed on your computer or mobile device when you visit a website. They are widely used to make websites work more efficiently and to provide information to website owners.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">How We Use Cookies</h2>
            <p>
                We use cookies to maintain your session, remember your preferences, and improve your experience on LabGato. We do not use cookies for tracking or advertising purposes.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">Types of Cookies We Use</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Session Cookies:</strong> Essential for the platform to function properly</li>
                <li><strong>Security Cookies:</strong> Used for CSRF protection</li>
                <li><strong>Preference Cookies:</strong> Remember your settings and preferences</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">Managing Cookies</h2>
            <p>
                You can control and manage cookies through your browser settings. Disabling certain cookies may affect the functionality of the platform.
            </p>
        </section>
    </div>
</div>
@endsection
