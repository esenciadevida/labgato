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
}
