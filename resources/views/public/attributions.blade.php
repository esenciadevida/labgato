@extends('layouts.public')

@section('title', 'Image Attributions')

@section('content')
<section class="py-12 sm:py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <header>
            <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">Image Attributions</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Images used in LabGOTO assessment questions are sourced from public-domain and Creative-Commons licensed repositories. Credits are listed below.
            </p>
        </header>

        @forelse($grouped as $source => $questions)
            <section class="mt-10">
                <h2 class="font-display text-lg font-bold text-slate-900 dark:text-slate-100">{{ $source }}</h2>
                <div class="mt-4 space-y-6">
                    @foreach($questions as $question)
                        <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-start dark:border-slate-700 dark:bg-slate-900">
                            @if($question->image_url)
                                <img src="{{ $question->image_url }}" alt="{{ $question->image_alt ?? 'Question image' }}"
                                     class="h-24 w-24 shrink-0 rounded-lg border border-slate-200 object-contain dark:border-slate-700">
                            @else
                                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                    No image
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ \Illuminate\Support\Str::limit($question->stem, 120) }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    {{ $question->image_source }} &middot; {{ $question->image_license }}
                                    @if($question->image_attribution)
                                        &middot; {{ $question->image_attribution }}
                                    @endif
                                    @if($question->image_source_url)
                                        &middot;
                                        <a href="{{ $question->image_source_url }}" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline hover:text-primary-700 dark:text-primary-400">
                                            Source
                                        </a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="mt-10 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">No Creative-Commons licensed images are in use yet.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
