@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'ogImageWidth' => 1200,
    'ogImageHeight' => 630,
    'ogImageAlt' => null,
    'publishedTime' => null,
    'modifiedTime' => null,
    'authorName' => null,
    'noindex' => false,
])

@php
    $siteName = config('app.name');
    $description = $description ?? config('app.name') . ' — Lab calculators, reference ranges, SOPs and interpretation guides for laboratory professionals.';
    $ogImage = $ogImage ?: config('labgoto.seo.default_og_image');
    if ($ogImage && ! str_starts_with($ogImage, 'http')) {
        $ogImage = url($ogImage);
    }
    $ogImageAlt = trim($ogImageAlt ?: ($title ? $title . ' — ' . $siteName : $siteName . ' — Professional Laboratory Toolkit'));
    $googleVerification = setting('seo.google_site_verification', '');

    $ogImageExtension = strtolower(pathinfo((string) parse_url($ogImage, PHP_URL_PATH) ?: $ogImage, PATHINFO_EXTENSION));
    $ogImageMime = match ($ogImageExtension) {
        'webp' => 'image/webp',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        default => null,
    };

    $ogImageDims = $ogImage ? \App\Support\StoredImage::resolve($ogImage) : null;
    if ($ogImageDims === null && $ogImage && str_starts_with($ogImage, url('/'))) {
        $localPath = public_path((string) substr($ogImage, strlen(url('/'))));
        if (is_file($localPath)) {
            $size = @getimagesize($localPath);
            if ($size !== false) {
                $ogImageDims = ['width' => $size[0], 'height' => $size[1]];
            }
        }
    }
    $ogImageWidth = $ogImageDims['width'] ?? $ogImageWidth;
    $ogImageHeight = $ogImageDims['height'] ?? $ogImageHeight;
@endphp

<title>{{ $title ? $title . ' - ' . $siteName : $siteName . ' - Professional Laboratory Toolkit' }}</title>
<meta name="description" content="{{ $description }}">

<link rel="canonical" href="{{ $canonical ?? url(request()->url()) }}">

<meta property="og:title" content="{{ $title ?? $siteName . ' — Professional Laboratory Toolkit' }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical ?? url(request()->url()) }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    @if($ogImageMime)
        <meta property="og:image:type" content="{{ $ogImageMime }}">
    @endif
    <meta property="og:image:width" content="{{ $ogImageWidth }}">
    <meta property="og:image:height" content="{{ $ogImageHeight }}">
    <meta property="og:image:alt" content="{{ $ogImageAlt }}">
@endif

@if($ogType === 'article')
    @if($publishedTime)
        <meta property="article:published_time" content="{{ $publishedTime }}">
    @endif
    @if($modifiedTime)
        <meta property="article:modified_time" content="{{ $modifiedTime }}">
    @endif
    @if($authorName)
        <meta property="article:author" content="{{ $authorName }}">
    @endif
@endif

<meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title ?? $siteName . ' — Professional Laboratory Toolkit' }}">
<meta name="twitter:description" content="{{ $description }}">
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $ogImageAlt }}">
@endif

@if($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow">
@endif

@if($googleVerification)
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif

@if($ogType === 'article')
@php
    $canonicalUrl = $canonical ?? url(request()->url());
    $articleSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title ?? $siteName,
        'description' => $description,
        'image' => $ogImage,
        'datePublished' => $publishedTime,
        'dateModified' => $modifiedTime ?? $publishedTime,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl,
        ],
        'author' => $authorName ? ['@type' => 'Person', 'name' => $authorName] : null,
        'publisher' => [
            '@type' => 'Organization',
            'name' => $siteName,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => url(asset('assets/LabGoTo_favicon_192.png')),
            ],
        ],
    ], fn ($value) => $value !== null);
@endphp
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
