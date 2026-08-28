<?php

namespace App\Services;

use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\RecentlyViewed;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class RecentlyViewedService
{
    public const ALLOWED_TYPES = [
        'calculator',
        'tool',
        'reference-range',
        'sop',
        'interpretation',
        'knowledge',
    ];

    public function track(User $user, string $resourceType, int $resourceId): void
    {
        if (! in_array($resourceType, self::ALLOWED_TYPES, true)) {
            return;
        }

        RecentlyViewed::updateOrCreate(
            [
                'user_id' => $user->id,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
            ],
            ['viewed_at' => now()]
        );

        $this->prune($user->id, 20);
    }

    public function getRecent(User $user, int $limit = 8): Collection
    {
        $rows = RecentlyViewed::where('user_id', $user->id)
            ->latest('viewed_at')
            ->limit($limit)
            ->get();

        foreach ($rows->groupBy('resource_type') as $type => $group) {
            $modelClass = $this->resolveModel($type);
            if (! $modelClass) {
                continue;
            }

            $records = $modelClass::query()
                ->findMany($group->pluck('resource_id')->unique()->values())
                ->keyBy('id');

            foreach ($group as $item) {
                $record = $records->get($item->resource_id);
                if (! $record) {
                    continue;
                }

                $item->setRelation('viewable', $record);
                $item->url = $this->getUrl($record, $item->resource_type);
                $item->name = $record->name ?? 'Unknown resource';
            }
        }

        return $rows;
    }

    private function prune(int $userId, int $keep): void
    {
        $count = RecentlyViewed::where('user_id', $userId)->count();

        if ($count > $keep) {
            $toDelete = RecentlyViewed::where('user_id', $userId)
                ->orderByDesc('viewed_at')
                ->offset($keep)
                ->limit($count - $keep)
                ->get(['id']);

            RecentlyViewed::whereIn('id', $toDelete->pluck('id'))->delete();
        }
    }

    private function resolveModel(string $type): ?string
    {
        return match ($type) {
            'calculator' => Calculator::class,
            'tool' => LaboratoryTool::class,
            'reference-range' => ReferenceRange::class,
            'sop' => Sop::class,
            'interpretation' => InterpretationResource::class,
            'knowledge' => KnowledgeResource::class,
            default => null,
        };
    }

    private function getUrl(object $record, string $type): string
    {
        return match ($type) {
            'calculator' => route('public.calculators.show', $record->slug),
            'tool' => route('public.tools.show', $record->slug),
            'reference-range' => route('public.reference-ranges.show', $record->slug),
            'sop' => route('public.sops.show', $record->slug),
            'interpretation' => route('public.interpretation.show', $record->slug),
            'knowledge' => route('public.knowledge.show', $record->slug),
            default => '#',
        };
    }

    public function clear(int $userId): int
    {
        return RecentlyViewed::where('user_id', $userId)->delete();
    }
}
