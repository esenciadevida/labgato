<?php

namespace Database\Seeders;

use App\Models\QuestionCategory;
use Illuminate\Database\Seeder;

class QuestionCategorySeeder extends Seeder
{
    private const SUBJECTS = [
        ['name' => 'Hematology', 'slug' => 'hematology', 'sort_order' => 1],
        ['name' => 'Clinical Chemistry', 'slug' => 'clinical-chemistry', 'sort_order' => 2],
        ['name' => 'Microbiology', 'slug' => 'microbiology', 'sort_order' => 3],
        ['name' => 'Immunology', 'slug' => 'immunology', 'sort_order' => 4],
        ['name' => 'Blood Banking/Transfusion', 'slug' => 'blood-banking-transfusion', 'sort_order' => 5],
        ['name' => 'Molecular Diagnostics', 'slug' => 'molecular-diagnostics', 'sort_order' => 6],
        ['name' => 'Quality Control', 'slug' => 'quality-control', 'sort_order' => 7],
        ['name' => 'Other', 'slug' => 'other', 'sort_order' => 8],
    ];

    public function run(): void
    {
        foreach (self::SUBJECTS as $subject) {
            QuestionCategory::firstOrCreate(
                ['slug' => $subject['slug']],
                [
                    'name' => $subject['name'],
                    'sort_order' => $subject['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
