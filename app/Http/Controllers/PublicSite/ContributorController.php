<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ContributorController extends Controller
{
    private const PER_PAGE = 12;

    public function show(string $profileSlug)
    {
        $user = User::query()
            ->where('profile_slug', $profileSlug)
            ->where('is_active', true)
            ->first();

        if ($user === null) {
            abort(404);
        }

        $items = $this->publishedContributions($user->id);

        if ($items->isEmpty()) {
            abort(404);
        }

        $page = Paginator::resolveCurrentPage('page');

        $contributions = new LengthAwarePaginator(
            $items->forPage($page, self::PER_PAGE)->values(),
            $items->count(),
            self::PER_PAGE,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $pageTitle = $user->name.' | '.config('app.name').' Contributor';
        $metaDescription = $user->bio ?: 'Contributions by '.$user->name.' on '.config('app.name').'.';

        return view('public.contributors.show', compact(
            'user',
            'contributions',
            'pageTitle',
            'metaDescription',
        ));
    }

    private function publishedContributions(int $userId): Collection
    {
        return collect()
            ->concat($this->published(Sop::class, $userId, 'sop'))
            ->concat($this->published(KnowledgeResource::class, $userId, 'knowledge'))
            ->concat($this->published(InterpretationResource::class, $userId, 'interpretation'))
            ->sortByDesc('published_at')
            ->values();
    }

    private function published(string $model, int $userId, string $type): array
    {
        return $model::query()
            ->published()
            ->where('contributed_by', $userId)
            ->get(['id', 'slug', 'name', 'short_description', 'published_at'])
            ->map(fn ($resource) => [
                'type' => $type,
                'slug' => $resource->slug,
                'name' => $resource->name,
                'short_description' => $resource->short_description,
                'published_at' => $resource->published_at,
            ])
            ->all();
    }
}
