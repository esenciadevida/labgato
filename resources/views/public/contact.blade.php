@extends('layouts.public')

@section('title', 'Contact Us - ' . config('app.name'))

@section('content')
<section class="bg-primary-950 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <p class="eyebrow text-primary-300">Get in touch</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">Contact Us</h1>
        <p class="mt-4 max-w-2xl text-primary-100/80">
            Questions, feedback, or suggestions about LabGato? Send us a message and our team will get back to you.
        </p>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-panel sm:p-8 dark:border-slate-800 dark:bg-slate-800">
            @if($errors->any())
                <div class="mb-6 rounded-lg border border-danger-600/20 bg-danger-50 p-4 dark:border-danger-600/30 dark:bg-danger-900/20">
                    <h2 class="text-sm font-semibold text-danger-700 dark:text-danger-300">There {{ $errors->count() === 1 ? 'was a problem' : 'were problems' }} with your submission:</h2>
                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-danger-600 dark:text-danger-300">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('contact.store') }}" class="space-y-6" novalidate>
                @csrf

                <x-ui.honeypot />

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                        <x-ui.input
                            id="name"
                            name="name"
                            value="{{ old('name', auth()->check() ? auth()->user()->name : '') }}"
                            :error="$errors->first('name')"
                            autocomplete="name"
                            required
                        />
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                        <x-ui.input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                            :error="$errors->first('email')"
                            autocomplete="email"
                            required
                        />
                        @error('email')
                            <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Subject</label>
                    <x-ui.input
                        id="subject"
                        name="subject"
                        value="{{ old('subject') }}"
                        :error="$errors->first('subject')"
                        placeholder="What is your message about?"
                        required
                    />
                    @error('subject')
                        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Message</label>
                    <x-ui.textarea
                        id="message"
                        name="message"
                        rows="6"
                        placeholder="Write your message here..."
                        required
                    >{{ old('message') }}</x-ui.textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        We usually respond within 1–2 business days.
                    </p>
                    <x-ui.button type="submit" variant="primary" size="lg">Send Message</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
