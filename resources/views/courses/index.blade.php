@extends('layouts.public')

@section('title', 'Courses - ' . config('app.name'))

@section('content')
<section class="bg-gradient-to-r from-[#0b2545] to-blue-800 dark:from-slate-900 dark:to-blue-900">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <p class="eyebrow text-blue-200">Learning Programs</p>
        <h1 class="mt-3 font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">Courses</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100 sm:text-base">
            Structured learning programs made up of linked challenge assessments. Pass every required assessment to earn a verifiable course completion certificate.
        </p>
    </div>
</section>

<main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    @if($courses->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">No courses are published yet. Check back soon.</p>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($courses as $course)
                <a href="{{ route('courses.show', $course) }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-primary-300 hover:shadow-card-hover dark:border-slate-700 dark:bg-slate-900 dark:hover:border-primary-700">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Course</span>
                        <span class="text-xs font-medium tabular-nums text-slate-500 dark:text-slate-400">{{ $course->assessments_count }} assessment{{ $course->assessments_count === 1 ? '' : 's' }}</span>
                    </div>
                    <h2 class="mt-3 font-display text-lg font-bold text-slate-900 group-hover:text-primary-700 dark:text-slate-100 dark:group-hover:text-primary-300">{{ $course->title }}</h2>
                    @if($course->summary)
                        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $course->summary }}</p>
                    @endif
                    <p class="mt-4 text-sm font-semibold text-primary-700 group-hover:underline dark:text-primary-300">
                        View course
                        <svg class="ml-0.5 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </p>
                </a>
            @endforeach
        </div>
    @endif
</main>
@endsection