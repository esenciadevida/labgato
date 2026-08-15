<?php

namespace App\Services\Admin;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\ReferenceRange;
use App\Models\Sop;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulkActionService
{
    private const TYPE_TO_CONFIG = [
        'calculator' => ['model' => Calculator::class, 'permission' => 'calculators'],
        'tool' => ['model' => LaboratoryTool::class, 'permission' => 'tools'],
        'reference-range' => ['model' => ReferenceRange::class, 'permission' => 'reference-ranges'],
        'sop' => ['model' => Sop::class, 'permission' => 'sops'],
        'interpretation' => ['model' => InterpretationResource::class, 'permission' => 'interpretation'],
        'knowledge' => ['model' => KnowledgeResource::class, 'permission' => 'knowledge'],
    ];

    private const ACTION_PERMISSION = [
        'publish' => 'publish',
        'archive' => 'edit',
        'delete' => 'delete',
    ];

    public function publish(string $type, array $ids, ?User $user = null, ?Request $request = null): int
    {
        return $this->apply($type, $ids, 'publish', $user, $request);
    }

    public function archive(string $type, array $ids, ?User $user = null, ?Request $request = null): int
    {
        return $this->apply($type, $ids, 'archive', $user, $request);
    }

    public function delete(string $type, array $ids, ?User $user = null, ?Request $request = null): int
    {
        return $this->apply($type, $ids, 'delete', $user, $request);
    }

    public function apply(string $type, array $ids, string $action, ?User $user = null, ?Request $request = null): int
    {
        $config = self::TYPE_TO_CONFIG[$type] ?? null;

        if ($config === null || ! array_key_exists($action, self::ACTION_PERMISSION)) {
            abort(422, 'Unsupported resource type or action.');
        }

        $user = $user ?? Auth::user();

        if (! $user?->hasPermission($config['permission'].'.'.self::ACTION_PERMISSION[$action])) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $ids = array_values(array_unique(array_filter($ids, fn ($id) => is_int($id) && $id > 0)));

        if ($ids === []) {
            return 0;
        }

        $affected = 0;

        $config['model']::whereIn('id', $ids)->get()->each(function ($resource) use ($action, $config, $user, $request, &$affected) {
            $applied = match ($action) {
                'publish' => $this->publishResource($resource, $config['permission'], $user, $request),
                'archive' => $this->archiveResource($resource, $config['permission'], $user, $request),
                'delete' => $this->deleteResource($resource, $config['permission'], $user, $request),
            };

            if ($applied) {
                $affected++;
            }
        });

        return $affected;
    }

    private function publishResource($resource, string $eventPrefix, User $user, ?Request $request): bool
    {
        if ($resource->status === ResourceStatus::PUBLISHED || ! $resource->status->canTransitionTo(ResourceStatus::PUBLISHED)) {
            return false;
        }

        $resource->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $resource->published_at ?? now(),
            'updated_by' => $user->id,
        ]);

        app(AuditLogService::class)->logWithSubject(
            "{$eventPrefix}.published",
            $resource,
            ['status' => ResourceStatus::PUBLISHED->value],
            $request,
        );

        return true;
    }

    private function archiveResource($resource, string $eventPrefix, User $user, ?Request $request): bool
    {
        if ($resource->status === ResourceStatus::ARCHIVED || ! $resource->status->canTransitionTo(ResourceStatus::ARCHIVED)) {
            return false;
        }

        $resource->update([
            'status' => ResourceStatus::ARCHIVED,
            'updated_by' => $user->id,
        ]);

        app(AuditLogService::class)->logWithSubject(
            "{$eventPrefix}.archived",
            $resource,
            ['status' => ResourceStatus::ARCHIVED->value],
            $request,
        );

        return true;
    }

    private function deleteResource($resource, string $eventPrefix, User $user, ?Request $request): bool
    {
        $changes = ['name' => $resource->name, 'slug' => $resource->slug];
        $resource->delete();

        app(AuditLogService::class)->logWithSubject("{$eventPrefix}.deleted", $resource, $changes, $request);

        return true;
    }
}
