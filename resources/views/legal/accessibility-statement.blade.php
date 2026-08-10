@extends('layouts.public')

@section('title', 'Accessibility Statement - ' . config('app.name'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-4">Accessibility Statement</h1>
    <p class="text-sm text-gray-500 mb-8">Last updated: January 1, 2024</p>

    <div class="prose prose-blue max-w-none text-gray-600 space-y-6">
        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Our Commitment</h2>
            <p>
                LabGato is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Standards</h2>
            <p>
                We aim to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA. These guidelines explain how to make web content more accessible for people with disabilities.
            </p>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Measures Taken</h2>
            <ul class="list-disc pl-6 space-y-2">
                <li>Semantic HTML structure with proper headings</li>
                <li>Skip to main content links</li>
                <li>Keyboard navigable interface</li>
                <li>Visible focus states</li>
                <li>ARIA labels where appropriate</li>
                <li>Sufficient color contrast</li>
            </ul>
        </section>

        <section>
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Feedback</h2>
            <p>
                We welcome your feedback on the accessibility of LabGato. If you encounter accessibility barriers, please let us know so we can address the issue.
            </p>
        </section>
    </div>
</div>
@endsection
