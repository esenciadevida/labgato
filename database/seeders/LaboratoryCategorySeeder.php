<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LaboratoryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Hematology', 'sort_order' => 10],
            ['name' => 'Clinical Chemistry', 'sort_order' => 20],
            ['name' => 'Microbiology', 'sort_order' => 30],
            ['name' => 'Immunology', 'sort_order' => 40],
            ['name' => 'Blood Bank / Transfusion Medicine', 'sort_order' => 50],
            ['name' => 'Urinalysis and Body Fluids', 'sort_order' => 60],
            ['name' => 'Laboratory Quality Assurance', 'sort_order' => 70],
            ['name' => 'Phlebotomy / Specimen Collection', 'sort_order' => 80],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
