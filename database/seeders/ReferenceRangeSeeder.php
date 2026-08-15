<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ReferenceRange;
use Illuminate\Database\Seeder;

class ReferenceRangeSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $ranges = [
            [
                'slug' => 'hemoglobin-adult-male',
                'name' => 'Hemoglobin - Adult Male',
                'analyte_name' => 'Hemoglobin',
                'parameter_key' => 'hemoglobin',
                'short_description' => 'Reference range for hemoglobin in adult males.',
                'specimen_type' => 'Whole Blood',
                'population_group' => 'Adult Male',
                'sex' => 'male',
                'unit' => 'g/dL',
                'lower_limit' => 13.0,
                'upper_limit' => 17.0,
                'critical_low' => 7.0,
                'critical_high' => 20.0,
                'source_citation' => 'Joint consensus of international hematology reference standards.',
            ],
            [
                'slug' => 'hemoglobin-adult-female',
                'name' => 'Hemoglobin - Adult Female',
                'analyte_name' => 'Hemoglobin',
                'parameter_key' => 'hemoglobin',
                'short_description' => 'Reference range for hemoglobin in adult females.',
                'specimen_type' => 'Whole Blood',
                'population_group' => 'Adult Female',
                'sex' => 'female',
                'unit' => 'g/dL',
                'lower_limit' => 12.0,
                'upper_limit' => 15.0,
                'critical_low' => 6.5,
                'critical_high' => 18.0,
                'source_citation' => 'Joint consensus of international hematology reference standards.',
            ],
            [
                'slug' => 'glucose-fasting',
                'name' => 'Glucose - Fasting',
                'analyte_name' => 'Glucose',
                'parameter_key' => 'glucose_fasting',
                'short_description' => 'Reference range for fasting plasma glucose in adults.',
                'specimen_type' => 'Plasma',
                'methodology' => 'Hexokinase',
                'population_group' => 'Adult',
                'sex' => 'any',
                'unit' => 'mg/dL',
                'lower_limit' => 70.0,
                'upper_limit' => 99.0,
                'critical_low' => 40.0,
                'critical_high' => 400.0,
                'source_citation' => 'Consensus guidelines on diabetes diagnostic criteria.',
            ],
            [
                'slug' => 'glucose-random',
                'name' => 'Glucose - Random',
                'analyte_name' => 'Glucose',
                'parameter_key' => 'glucose_random',
                'short_description' => 'Reference range for random plasma glucose in adults.',
                'specimen_type' => 'Plasma',
                'methodology' => 'Hexokinase',
                'population_group' => 'Adult',
                'sex' => 'any',
                'unit' => 'mg/dL',
                'lower_limit' => 70.0,
                'upper_limit' => 140.0,
                'critical_low' => 40.0,
                'critical_high' => 400.0,
                'source_citation' => 'Consensus guidelines on diabetes diagnostic criteria.',
            ],
        ];

        foreach ($ranges as $data) {
            ReferenceRange::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'status' => 'published',
                    'published_at' => now(),
                ]
            );
        }

        $hematology = Category::query()->where('slug', 'hematology')->where('is_active', true)->first();
        $clinicalChemistry = Category::query()->where('slug', 'clinical-chemistry')->where('is_active', true)->first();

        if ($hematology) {
            ReferenceRange::query()->forAnalyte('Hemoglobin')->get()
                ->each(fn (ReferenceRange $range) => $range->categories()->syncWithoutDetaching([$hematology->id]));
        }

        if ($clinicalChemistry) {
            ReferenceRange::query()->forAnalyte('Glucose')->get()
                ->each(fn (ReferenceRange $range) => $range->categories()->syncWithoutDetaching([$clinicalChemistry->id]));
        }
    }
}
