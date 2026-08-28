<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $heartbeatPath = storage_path('app/scheduler-heartbeat.txt');
        $heartbeatAgeSeconds = null;

        if (is_file($heartbeatPath)) {
            $mtime = @filemtime($heartbeatPath);

            if ($mtime !== false) {
                $heartbeatAgeSeconds = max(0, (int) round(now()->getTimestamp() - (int) $mtime));
            }
        }

        return ApiResponse::success(
            data: [
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'version' => config('app.version', '1.0.0'),
                'scheduler_heartbeat_age_seconds' => $heartbeatAgeSeconds,
            ],
            message: 'API is operational.'
        );
    }
}
