<?php

namespace Database\Seeders;

use App\Models\Calculator;
use App\Models\Category;
use App\Services\Calculators\Engines\AnionGapCalculator;
use Illuminate\Database\Seeder;

class CalculatorSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $calculator = Calculator::firstOrCreate(
            ['slug' => 'anion-gap'],
            [
                'name' => 'Anion Gap',
                'slug' => 'anion-gap',
                'short_description' => 'Calculate the serum anion gap to assess acid-base disorders.',
                'formula_display' => 'Sodium - (Chloride + Bicarbonate)',
                'input_schema' => [
                    [
                        'key' => 'sodium',
                        'label' => 'Serum Sodium',
                        'type' => 'number',
                        'unit' => 'mEq/L',
                        'required' => true,
                        'min' => 100,
                        'max' => 200,
                        'step' => 0.1,
                    ],
                    [
                        'key' => 'chloride',
                        'label' => 'Serum Chloride',
                        'type' => 'number',
                        'unit' => 'mEq/L',
                        'required' => true,
                        'min' => 80,
                        'max' => 150,
                        'step' => 0.1,
                    ],
                    [
                        'key' => 'bicarbonate',
                        'label' => 'Serum Bicarbonate',
                        'type' => 'number',
                        'unit' => 'mEq/L',
                        'required' => true,
                        'min' => 5,
                        'max' => 50,
                        'step' => 0.1,
                    ],
                ],
                'output_schema' => [
                    [
                        'key' => 'anion_gap',
                        'label' => 'Anion Gap',
                        'unit' => 'mEq/L',
                        'rounding' => 'round_half_up',
                        'precision' => 1,
                    ],
                ],
                'engine' => 'php_class',
                'engine_reference' => AnionGapCalculator::class,
                'interpretation_notes' => 'The normal anion gap is typically 8-16 mEq/L. An elevated anion gap suggests accumulation of unmeasured anions, which may occur in conditions such as lactic acidosis or ketoacidosis.',
                'references' => 'Kraut JA, Madias NE. Serum anion gap: its uses and limitations in clinical medicine. Clin J Am Soc Nephrol. 2007.',
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        $category = Category::query()
            ->where('is_active', true)
            ->where('slug', 'like', '%clinical-chem%')
            ->first();

        if ($category) {
            $calculator->categories()->syncWithoutDetaching([$category->id]);
        }
    }
}
