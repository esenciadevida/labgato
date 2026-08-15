<?php

namespace App\Support;

use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\User;

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

        $generator->addUrl('/calculators', 'weekly', 0.8);
        $generator->addUrl('/reference-ranges', 'weekly', 0.8);
        $generator->addUrl('/interpretation', 'weekly', 0.8);
        $generator->addUrl('/knowledge', 'weekly', 0.8);
        $generator->addUrl('/tools', 'weekly', 0.8);
        $generator->addUrl('/sops', 'weekly', 0.8);
        $generator->addUrl('/contact', 'monthly', 0.5);

        foreach (Calculator::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $resource) {
            $generator->addUrl(
                '/calculators/'.$resource->slug,
                'weekly',
                0.7,
                $resource->updated_at?->toDateString(),
            );
        }

        foreach (ReferenceRange::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $resource) {
            $generator->addUrl(
                '/reference-ranges/'.$resource->slug,
                'weekly',
                0.7,
                $resource->updated_at?->toDateString(),
            );
        }

        foreach (InterpretationResource::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $resource) {
            $generator->addUrl(
                '/interpretation/'.$resource->slug,
                'weekly',
                0.7,
                $resource->updated_at?->toDateString(),
            );
        }

        foreach (KnowledgeResource::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $resource) {
            $generator->addUrl(
                '/knowledge/'.$resource->slug,
                'weekly',
                0.7,
                $resource->updated_at?->toDateString(),
            );
        }

        foreach (LaboratoryTool::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $resource) {
            $generator->addUrl(
                '/tools/'.$resource->slug,
                'weekly',
                0.7,
                $resource->updated_at?->toDateString(),
            );
        }

        foreach (Sop::query()->published()->orderByDesc('updated_at')->get(['slug', 'updated_at']) as $resource) {
            $generator->addUrl(
                '/sops/'.$resource->slug,
                'weekly',
                0.7,
                $resource->updated_at?->toDateString(),
            );
        }

        $contributorIds = Sop::query()->published()->pluck('contributed_by')
            ->merge(KnowledgeResource::query()->published()->pluck('contributed_by'))
            ->merge(InterpretationResource::query()->published()->pluck('contributed_by'))
            ->filter()
            ->unique()
            ->values();

        $contributors = User::query()
            ->whereIn('id', $contributorIds)
            ->where('is_active', true)
            ->whereNotNull('profile_slug')
            ->orderBy('name')
            ->get(['profile_slug', 'updated_at']);

        foreach ($contributors as $contributor) {
            $generator->addUrl(
                '/contributors/'.$contributor->profile_slug,
                'weekly',
                0.6,
                $contributor->updated_at?->toDateString(),
            );
        }

        return $generator->saveToPublic();
    }
}
