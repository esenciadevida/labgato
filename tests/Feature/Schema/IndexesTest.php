<?php

namespace Tests\Feature\Schema;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IndexesTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_attempts_has_leaderboard_indexes(): void
    {
        $this->assertTrue(Schema::hasIndex('exam_attempts', ['assessment_id', 'status']));
        $this->assertTrue(Schema::hasIndex('exam_attempts', ['user_id', 'status']));
    }

    public function test_app_key_is_not_a_placeholder(): void
    {
        $this->assertNotSame('', config('app.key'));
    }
}