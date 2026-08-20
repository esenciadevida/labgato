@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('content')
<section class="bg-primary-950 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950 border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'Reference Ranges']]" dark />

        <p class="eyebrow text-primary-300">Reference Ranges</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">Reference Ranges</h1>
        <p class="mt-3 text-white/70 max-w-3xl">
            Reference ranges for common laboratory analytes, broken down by demographic group. For educational reference only.
        </p>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-public.reference-range-disclaimer class="mb-8" />
        <livewire:public-site.resource-browser type="reference-range" />
    </div>
</section>
@endsection
