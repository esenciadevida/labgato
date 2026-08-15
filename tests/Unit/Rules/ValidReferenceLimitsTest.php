<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidReferenceLimits;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidReferenceLimitsTest extends TestCase
{
    private function validate(array $data): array
    {
        $this->app['request']->replace($data);

        $validator = Validator::make(['unit' => $data['unit'] ?? 'g/dL'], [
            'unit' => ['required', new ValidReferenceLimits],
        ]);

        return $validator->errors()->get('unit');
    }

    public function test_valid_limits_pass(): void
    {
        $errors = $this->validate([
            'lower_limit' => '13',
            'upper_limit' => '17',
            'critical_low' => '7',
            'critical_high' => '22',
            'age_min' => '18',
            'age_max' => '65',
        ]);

        $this->assertEmpty($errors);
    }

    public function test_lower_limit_must_be_less_than_upper_limit(): void
    {
        $errors = $this->validate([
            'lower_limit' => '20',
            'upper_limit' => '10',
        ]);

        $this->assertNotEmpty($errors);
    }

    public function test_critical_low_must_be_less_than_lower_limit(): void
    {
        $errors = $this->validate([
            'lower_limit' => '13',
            'critical_low' => '14',
        ]);

        $this->assertNotEmpty($errors);
    }

    public function test_critical_high_must_be_greater_than_upper_limit(): void
    {
        $errors = $this->validate([
            'upper_limit' => '17',
            'critical_high' => '16',
        ]);

        $this->assertNotEmpty($errors);
    }

    public function test_age_min_must_be_less_than_or_equal_to_age_max(): void
    {
        $errors = $this->validate([
            'age_min' => '65',
            'age_max' => '18',
        ]);

        $this->assertNotEmpty($errors);
    }

    public function test_partial_limits_are_allowed(): void
    {
        $errors = $this->validate([
            'lower_limit' => '13',
        ]);

        $this->assertEmpty($errors);
    }

    public function test_empty_values_are_allowed(): void
    {
        $errors = $this->validate([
            'lower_limit' => '',
            'upper_limit' => '',
            'critical_low' => '',
            'critical_high' => '',
            'age_min' => '',
            'age_max' => '',
        ]);

        $this->assertEmpty($errors);
    }
}
