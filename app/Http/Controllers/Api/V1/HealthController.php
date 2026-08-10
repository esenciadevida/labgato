<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            data: [
                'status' => 'ok',
                'timestamp' => now()->toIso8601String(),
                'version' => config('app.version', '1.0.0'),
            ],
            message: 'API is operational.'
        );
    }
}
