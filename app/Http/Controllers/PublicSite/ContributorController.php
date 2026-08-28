<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ContributorController extends Controller
{
    private const PER_PAGE = 12;

    public function show(string $profileSlug)
    {
        $user = User::query()
            ->where('profile_slug', $profileSlug)
            ->where('is_active', true)
            ->with('profile')
            ->first();

        if ($user === null) {
            abort(404);
        }

        $contributions = $this->publishedContributions($user->id);

        $pageTitle = $user->name.' | '.config('app.name').' Contributor';
        $metaDescription = $user->bio ?: 'Contributions by '.$user->name.' on '.config('app.name').'.';

        return view('public.contributors.show', compact(
            'user',
            'contributions',
            'pageTitle',
            'metaDescription',
        ));
    }

    private function publishedContributions(int $userId): LengthAwarePaginator
    {
        $query = $this->contributionQuery(Sop::class, 'sop', $userId)
            ->unionAll($this->contributionQuery(KnowledgeResource::class, 'knowledge', $userId))
            ->unionAll($this->contributionQuery(InterpretationResource::class, 'interpretation', $userId));

        return $query
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE, ['type', 'id', 'slug', 'name', 'short_description', 'published_at'])
            ->through(fn ($row) => $row->only(['type', 'id', 'slug', 'name', 'short_description', 'published_at']));
    }

    private function contributionQuery(string $model, string $type, int $userId): Builder
    {
        return $model::query()
            ->published()
            ->where('contributed_by', $userId)
            ->selectRaw('? as type', [$type])
            ->addSelect(['id', 'slug', 'name', 'short_description', 'published_at']);
    }
}
