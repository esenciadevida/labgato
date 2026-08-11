@extends('layouts.public')

@section('title', 'Privacy Policy - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-neutral-900 mb-4">Privacy Policy</h1>
    <p class="text-sm text-neutral-500 mb-8">Last updated: January 1, 2024</p>

    <div class="prose max-w-none text-neutral-600 space-y-6">
        <div class="bg-warning-50 border border-warning-600/20 rounded-lg p-4 mb-6">
            <p class="text-sm text-warning-600">
                <strong>Disclaimer:</strong> This is a placeholder privacy policy. The final legal text must be reviewed and approved by a qualified legal professional before production deployment.
            </p>
        </div>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">1. Introduction</h2>
            <p>
                Welcome to LabGato. This Privacy Policy explains how we collect, use, and protect information when you use our laboratory toolkit platform. By using LabGato, you agree to the collection and use of information in accordance with this policy.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">2. Information We Collect</h2>
            <p>
                We may collect information you provide directly, such as when you create an account, use our tools, or contact us for support. This may include your name, email address, and professional credentials.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">3. How We Use Your Information</h2>
            <p>
                We use the information we collect to provide, maintain, and improve our services, to send you technical notices and support messages, and to comply with legal obligations.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">4. Data Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-neutral-900 mb-3">5. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy, please contact us through the platform.
            </p>
        </section>
    </div>
</div>
@endsection
