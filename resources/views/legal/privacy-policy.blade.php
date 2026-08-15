@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-slate-900 dark:text-slate-100 mb-4">Privacy Policy</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">Last updated: January 1, 2024</p>

    <div class="prose max-w-none text-slate-600 dark:text-slate-300 space-y-6">
        <div class="bg-warning-50 border border-warning-600/20 rounded-lg p-4 mb-6">
            <p class="text-sm text-warning-600">
                <strong>Disclaimer:</strong> This is a placeholder privacy policy. The final legal text must be reviewed and approved by a qualified legal professional before production deployment.
            </p>
        </div>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">1. Introduction</h2>
            <p>
                Welcome to Labgoto. This Privacy Policy explains how we collect, use, and protect information when you use our laboratory toolkit platform. By using Labgoto, you agree to the collection and use of information in accordance with this policy.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">2. Information We Collect</h2>
            <p>
                We may collect information you provide directly, such as when you create an account, use our tools, or contact us for support. This may include your name, email address, and professional credentials.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">3. How We Use Your Information</h2>
            <p>
                We use the information we collect to provide, maintain, and improve our services, to send you technical notices and support messages, and to comply with legal obligations.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">4. Cookies</h2>
            <p>
                We use cookies and similar technologies to improve your experience, remember your preferences (such as theme and consent choices), and analyze site traffic. You can control cookies through your browser settings. See our <a href="{{ route('legal.cookie-policy') }}" class="text-primary-700 dark:text-primary-400 underline">Cookie Policy</a> for details.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">5. Your Rights</h2>
            <p>
                Depending on your jurisdiction, you may have the right to access, correct, or delete the personal information we hold about you, to restrict or object to certain processing, and to data portability. To exercise any of these rights, contact us through the platform.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">6. Data Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100 mb-3">7. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy, please <a href="{{ route('contact.create') }}" class="text-primary-700 dark:text-primary-400 underline">contact us</a>.
            </p>
        </section>
    </div>
</div>
@endsection
