@php
    $siteName = config('app.name');
    $siteUrl = url('/');
    $orgId = $siteUrl.'/#organization';
    $logoUrl = url(asset('assets/LabGoTo_logo.svg'));
    $socialProfiles = collect(config('labgoto.seo.social_profiles', []))
        ->filter()
        ->values()
        ->all();

    $organization = [
        '@type' => 'Organization',
        '@id' => $orgId,
        'name' => $siteName,
        'url' => $siteUrl,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $logoUrl,
        ],
        'description' => 'Medical laboratory knowledge platform providing clinical calculators, reference ranges, standard operating procedures (SOPs), and lab result interpretation guides for medical laboratory scientists and laboratory professionals.',
    ];

    if ($socialProfiles !== []) {
        $organization['sameAs'] = $socialProfiles;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organization,
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl.'/#website',
                'name' => $siteName,
                'url' => $siteUrl,
                'publisher' => ['@id' => $orgId],
                'description' => 'Professional laboratory toolkit with calculators, reference ranges, SOPs, and knowledge resources.',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => [
                        '@type' => 'EntryPoint',
                        'urlTemplate' => url('/search?q={search_term_string}'),
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
