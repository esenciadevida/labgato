<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogService
{
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'remember_token',
        'token',
        'api_key',
        'secret',
    ];

    public function log(
        string $action,
        ?object $subject = null,
        ?array $changes = null,
        ?Request $request = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource_type' => $subject ? get_class($subject) : null,
            'resource_id' => $subject?->id,
            'changes' => $changes ? $this->sanitize($changes) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function sanitize(array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $key) => ! in_array($key, self::SENSITIVE_FIELDS))
            ->toArray();
    }
}
