<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    private const TOUR_KEYS = [
        'member-dashboard',
    ];

    public function welcome(Request $request): JsonResponse
    {
        $request->user()->markOnboarded();

        return response()->json(['status' => 'ok']);
    }

    public function completeTour(Request $request, string $key): JsonResponse
    {
        if (! in_array($key, self::TOUR_KEYS, true)) {
            abort(404);
        }

        $request->user()->completeTour($key);
        $request->user()->markOnboarded();

        return response()->json(['status' => 'ok']);
    }
}
