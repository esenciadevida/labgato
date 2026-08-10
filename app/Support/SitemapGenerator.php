<?php

namespace App\Support;

class SitemapGenerator
{
    private string $baseUrl;

    private array $urls = [];

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.url', 'https://localhost'), '/');
    }

    public function addUrl(string $path, string $changefreq = 'monthly', float $priority = 0.5, ?string $lastmod = null): void
    {
        $this->urls[] = [
            'loc' => $this->baseUrl.$path,
            'changefreq' => $changefreq,
            'priority' => $priority,
            'lastmod' => $lastmod ?? now()->toDateString(),
        ];
    }

    public function generate(): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($this->urls as $url) {
            $urlNode = $xml->addChild('url');
            $urlNode->addChild('loc', htmlspecialchars($url['loc']));
            $urlNode->addChild('lastmod', $url['lastmod']);
            $urlNode->addChild('changefreq', $url['changefreq']);
            $urlNode->addChild('priority', $url['priority']);
        }

        return $xml->asXML();
    }

    public function saveToPublic(string $filename = 'sitemap.xml'): bool
    {
        $content = $this->generate();
        $path = public_path($filename);

        return file_put_contents($path, $content) !== false;
    }

    public static function generateDefault(): bool
    {
        $generator = new self;

        $generator->addUrl('/', 'daily', 1.0);
        $generator->addUrl('/legal/privacy-policy', 'monthly', 0.3);
        $generator->addUrl('/legal/terms-of-service', 'monthly', 0.3);
        $generator->addUrl('/legal/disclaimer', 'monthly', 0.3);
        $generator->addUrl('/legal/accessibility-statement', 'monthly', 0.3);
        $generator->addUrl('/legal/cookie-policy', 'monthly', 0.3);

        return $generator->saveToPublic();
    }
}
