<?php

namespace App\Services;

use App\Enums\ContributionType;
use App\Enums\ResourceStatus;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContributionService
{
    /**
     * Merge a member's contributions across the three resource tables.
     */
    public function memberContributions(int $userId, ?string $type = null, ?string $status = null): LengthAwarePaginator
    {
        $entries = collect();

        foreach (ContributionType::cases() as $contributionType) {
            if ($type !== null && $type !== '' && $contributionType->value !== $type) {
                continue;
            }

            $query = $contributionType->model()::query()
                ->where('contributed_by', $userId);

            if ($status !== null && $status !== '') {
                $query->where('status', $status);
            }

            foreach ($query->get() as $resource) {
                $entries->push($this->entry($contributionType, $resource));
            }
        }

        return $this->paginate($entries->sortByDesc('updated_at')->values());
    }

    /**
     * Merge pending (under_review) contributions for the admin review queue.
     */
    public function pendingContributions(?string $type = null): LengthAwarePaginator
    {
        $entries = collect();

        foreach (ContributionType::cases() as $contributionType) {
            if ($type !== null && $type !== '' && $contributionType->value !== $type) {
                continue;
            }

            $query = $contributionType->model()::query()
                ->with(['contributedBy'])
                ->where('status', ResourceStatus::UNDER_REVIEW->value)
                ->whereNotNull('contributed_by');

            foreach ($query->get() as $resource) {
                $entries->push($this->entry($contributionType, $resource));
            }
        }

        return $this->paginate($entries->sortBy('created_at')->values());
    }

    public function pendingCount(): int
    {
        $count = 0;

        foreach (ContributionType::cases() as $contributionType) {
            $count += $contributionType->model()::query()
                ->where('status', ResourceStatus::UNDER_REVIEW->value)
                ->whereNotNull('contributed_by')
                ->count();
        }

        return $count;
    }

    /**
     * Count a member's contributions grouped by status (across all resource types).
     */
    public function memberStatusCounts(int $userId): array
    {
        $counts = [];

        foreach (ResourceStatus::cases() as $status) {
            $counts[$status->value] = 0;
        }

        foreach (ContributionType::cases() as $contributionType) {
            $rows = $contributionType->model()::query()
                ->selectRaw('status, COUNT(*) as total')
                ->where('contributed_by', $userId)
                ->groupBy('status')
                ->pluck('total', 'status');

            foreach ($rows as $status => $total) {
                if (array_key_exists($status, $counts)) {
                    $counts[$status] += (int) $total;
                }
            }
        }

        return $counts;
    }

    /**
     * A member's most recently updated contributions (across all resource types).
     */
    public function recentMemberContributions(int $userId, int $limit = 5): Collection
    {
        $entries = collect();

        foreach (ContributionType::cases() as $contributionType) {
            $query = $contributionType->model()::query()
                ->where('contributed_by', $userId);

            foreach ($query->get() as $resource) {
                $entries->push($this->entry($contributionType, $resource));
            }
        }

        return $entries->sortByDesc('updated_at')->values()->take($limit);
    }

    private function entry(ContributionType $type, $resource): object
    {
        return (object) [
            'type' => $type->value,
            'type_label' => $type->label(),
            'id' => $resource->id,
            'name' => $resource->name,
            'slug' => $resource->slug,
            'status' => $resource->status,
            'created_at' => $resource->created_at,
            'updated_at' => $resource->updated_at,
            'contributor' => $resource->relationLoaded('contributedBy') ? $resource->contributedBy : null,
        ];
    }

    private function paginate(Collection $items): LengthAwarePaginator
    {
        $perPage = (int) config('labgoto.pagination.per_page');
        $page = LengthAwarePaginator::resolveCurrentPage();
        $sliced = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($sliced, $items->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);
    }
}
