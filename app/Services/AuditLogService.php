<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'remember_token',
        'token',
        'api_key',
        'secret',
        'authorization',
        'cookie',
    ];

    public function log(
        string $action,
        ?string $resourceType = null,
        ?int $resourceId = null,
        array $changes = [],
        ?int $userId = null,
    ): void {
        try {
            $userId = $userId ?? Auth::id();
            $request = request();

            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'changes' => $this->sanitize($changes),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create audit log entry: '.$e->getMessage());
        }
    }

    public function logWithSubject(
        string $action,
        ?object $subject = null,
        ?array $changes = null,
        ?Request $request = null,
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'resource_type' => $subject ? get_class($subject) : null,
                'resource_id' => $subject?->id,
                'changes' => $changes ? $this->sanitize($changes) : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create audit log entry: '.$e->getMessage());
        }
    }

    private function sanitize(array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $key) => ! in_array($key, self::SENSITIVE_FIELDS))
            ->toArray();
    }
}
