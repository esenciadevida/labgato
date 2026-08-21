@props(['title' => null, 'description' => null, 'canonical' => null, 'ogType' => 'website', 'ogImage' => null, 'noindex' => false])

@php
    $siteName = config('app.name');
    $description = $description ?? $siteName . ' - Professional Laboratory Toolkit';
    $ogImage = $ogImage ?: config('labgoto.seo.default_og_image');
    if ($ogImage && !str_starts_with($ogImage, 'http')) {
        $ogImage = url($ogImage);
    }
    $googleVerification = setting('seo.google_site_verification', '');
@endphp

<title>{{ $title ? $title . ' - ' . $siteName : $siteName }}</title>
<meta name="description" content="{{ $description }}">

<link rel="canonical" href="{{ $canonical ?? url(request()->url()) }}">

<meta property="og:title" content="{{ $title ?? $siteName }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical ?? url(request()->url()) }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $siteName }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="{{ $title ?? $siteName }}">
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title ?? $siteName }}">
<meta name="twitter:description" content="{{ $description }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

@if($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow">
@endif

@if($googleVerification)
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
