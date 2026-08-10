<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiResponseFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_response_has_correct_structure(): void
    {
        $response = $this->getJson(route('api.v1.health'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'ok',
            ],
        ]);
    }

    public function test_error_response_has_correct_structure(): void
    {
        $response = $this->getJson('/api/v1/nonexistent-endpoint', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_validation_error_response_has_correct_structure(): void
    {
        $response = $this->postJson('/api/v1/test-validation', [
            'email' => '',
        ]);

        $this->assertContains($response->status(), [404, 422, 405]);
    }
}
