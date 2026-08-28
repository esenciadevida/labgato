<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_successful_response(): void
    {
        $response = $this->getJson(route('api.v1.health'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'ok',
            ],
            'message' => 'API is operational.',
        ]);
    }

    public function test_health_endpoint_does_not_require_authentication(): void
    {
        $response = $this->getJson(route('api.v1.health'));

        $response->assertStatus(200);
    }

    public function test_health_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('api.v1.health'));

        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_health_endpoint_reports_scheduler_heartbeat_freshness(): void
    {
        $path = storage_path('app/scheduler-heartbeat.txt');

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, now()->toIso8601String());

        $response = $this->getJson(route('api.v1.health'));

        $response->assertStatus(200);

        $age = $response->json('data.scheduler_heartbeat_age_seconds');
        $this->assertIsInt($age);
        $this->assertLessThanOrEqual(5, $age);
        $this->assertGreaterThanOrEqual(0, $age);
    }

    public function test_health_endpoint_reports_null_heartbeat_when_file_is_missing(): void
    {
        $path = storage_path('app/scheduler-heartbeat.txt');

        if (is_file($path)) {
            unlink($path);
        }

        $response = $this->getJson(route('api.v1.health'));

        $response->assertStatus(200);
        $this->assertNull($response->json('data.scheduler_heartbeat_age_seconds'));
    }
}
