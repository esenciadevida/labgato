<?php

namespace App\Http\Controllers\PublicSite;

use App\Models\KnowledgeResource;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class RssController extends Controller
{
    public function knowledge(): Response
    {
        $items = KnowledgeResource::query()
            ->with(['categories', 'tags'])
            ->published()
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        $response = response()->view('rss.knowledge', [
            'items' => $items,
        ], 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);

        $response->setCache([
            'public' => true,
            'max_age' => 3600,
            's_maxage' => 3600,
        ]);

        return $response;
    }
}