<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class CronTickController extends Controller
{
    private const TASKS = [
        'schedule' => 'schedule:run',
        'queue' => 'queue:work --stop-when-empty --max-time=50',
    ];

    public function __invoke(string $secret, string $task): JsonResponse
    {
        $expected = (string) config('labgoto.cron.secret');

        if ($expected === '' || ! hash_equals($expected, $secret)) {
            abort(404);
        }

        $command = self::TASKS[$task] ?? null;

        if ($command === null) {
            abort(404);
        }

        $exitCode = Artisan::call($command);

        return response()->json([
            'task' => $task,
            'exit_code' => $exitCode,
            'output' => substr(trim(Artisan::output()), 0, 2000),
        ]);
    }
}
