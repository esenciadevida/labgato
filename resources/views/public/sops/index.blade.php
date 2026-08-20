@php $metaNoindex = $metaNoindex ?? false; @endphp

@extends('layouts.public')

@section('content')
<section class="bg-primary-950 bg-linear-to-b from-primary-950 via-primary-950/60 to-slate-950 border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <x-public.breadcrumbs :items="[['label' => 'Home', 'url' => route('home')], ['label' => 'SOPs']]" dark />

        <p class="eyebrow text-primary-300">Standard Operating Procedures</p>
        <h1 class="mt-3 font-display text-3xl sm:text-4xl font-bold tracking-tight">Laboratory SOPs</h1>
        <p class="mt-3 text-white/70 max-w-3xl">
            Standard operating procedures for laboratory professionals, from specimen collection to instrument quality control.
        </p>
    </div>
</section>

<section class="py-10 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <livewire:public-site.resource-browser type="sop" />
    </div>
</section>
@endsection
