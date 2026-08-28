<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ config('app.name') }} — Knowledge Hub</title>
    <link>{{ url('/knowledge') }}</link>
    <description>Knowledge articles for laboratory professionals from {{ config('app.name') }}.</description>
    <language>en-us</language>
    <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
    <atom:link href="{{ url('/rss/knowledge.xml') }}" rel="self" type="application/rss+xml"/>
    @foreach ($items as $item)
    <item>
        <title>{{ $item->name }}</title>
        <link>{{ route('public.knowledge.show', $item->slug) }}</link>
        <guid isPermaLink="true">{{ route('public.knowledge.show', $item->slug) }}</guid>
        <pubDate>{{ $item->published_at?->toRfc2822String() ?? $item->created_at?->toRfc2822String() }}</pubDate>
        <description>{{ $item->short_description }}</description>
        @foreach ($item->categories as $category)
        <category>{{ $category->name }}</category>
        @endforeach
    </item>
    @endforeach
</channel>
</rss>