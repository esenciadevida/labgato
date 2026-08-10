@props(['title' => null, 'description' => null, 'canonical' => null, 'ogType' => 'website', 'ogImage' => null, 'noindex' => false])

<title>{{ $title ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
<meta name="description" content="{{ $description ?? config('app.name') . ' - Professional Laboratory Toolkit' }}">

<link rel="canonical" href="{{ $canonical ?? url(request()->url()) }}">

<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:description" content="{{ $description ?? config('app.name') . ' - Professional Laboratory Toolkit' }}">
<meta property="og:url" content="{{ $canonical ?? url(request()->url()) }}">
<meta property="og:type" content="{{ $ogType }}">
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

@if($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow">
@endif
