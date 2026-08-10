@props(['title' => null, 'description' => null])

<meta name="description" content="{{ $description ?? config('app.name') }}">
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:description" content="{{ $description ?? config('app.name') }}">
<meta property="og:type" content="website">
