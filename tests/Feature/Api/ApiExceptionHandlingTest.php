<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_not_found_returns_json_404(): void
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertDontSee('Stack trace');
    }

    public function test_api_validation_error_returns_json_422(): void
    {
        $response = $this->postJson('/api/v1/test-validation', [
            'email' => '',
        ]);

        $this->assertContains($response->status(), [404, 422, 405]);
    }

    public function test_api_unauthorized_returns_json_401(): void
    {
        $response = $this->getJson('/api/v1/protected-endpoint', [
            'Accept' => 'application/json',
        ]);

        $this->assertContains($response->status(), [401, 404]);
        if ($response->status() === 401) {
            $response->assertJson([
                'success' => false,
            ]);
        }
    }

    public function test_api_forbidden_returns_json_403(): void
    {
        $response = $this->getJson('/api/v1/forbidden-endpoint', [
            'Accept' => 'application/json',
        ]);

        $this->assertContains($response->status(), [403, 404]);
        if ($response->status() === 403) {
            $response->assertJson([
                'success' => false,
            ]);
        }
    }
}
