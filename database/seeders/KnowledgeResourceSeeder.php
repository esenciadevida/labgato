<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\KnowledgeResource;
use Illuminate\Database\Seeder;

class KnowledgeResourceSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $resources = [
            [
                'slug' => 'introduction-to-hematology',
                'name' => 'Introduction to Hematology',
                'short_description' => 'Foundational concepts in hematology, from blood cell lineages to the complete blood count.',
                'content' => "# Introduction to Hematology\n\nHematology is the study of blood, its components, and the disorders that affect them.\n\n## Key concepts\n\n- Blood is composed of plasma, red cells, white cells, and platelets.\n- The complete blood count is the cornerstone of hematologic testing.\n- Reference ranges vary by age, sex, and population.\n\n## Getting started\n\nBegin with the CBC parameters and work toward the red cell indices and white cell differential.",
                'topic_category' => 'Hematology',
                'difficulty_level' => 'Beginner',
                'learning_objectives' => "By the end of this resource you should be able to:\n\n- Describe the major components of blood.\n- Identify the primary parameters reported in a complete blood count.\n- Explain why reference ranges vary by demographic group.",
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 8,
            ],
            [
                'slug' => 'clinical-chemistry-overview',
                'name' => 'Clinical Chemistry Overview',
                'short_description' => 'An overview of the major analyte groups measured in the clinical chemistry laboratory.',
                'content' => "# Clinical Chemistry Overview\n\nClinical chemistry measures the concentration of molecules in biological fluids.\n\n## Major analyte groups\n\n- Electrolytes and minerals\n- Metabolites such as glucose and creatinine\n- Enzymes and proteins\n- Lipids and hormones\n\n## Analytical principles\n\nMost chemistry tests use spectrophotometric, enzymatic, or immunochemical methods.",
                'topic_category' => 'Clinical Chemistry',
                'difficulty_level' => 'Intermediate',
                'learning_objectives' => "By the end of this resource you should be able to:\n\n- List the major groups of analytes in clinical chemistry.\n- Describe common analytical principles used in chemistry testing.",
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 10,
            ],
            [
                'slug' => 'understanding-quality-control',
                'name' => 'Understanding Quality Control in the Laboratory',
                'short_description' => 'How Levey-Jennings charts and Westgard rules help ensure reliable laboratory results.',
                'content' => "# Understanding Quality Control in the Laboratory\n\nQuality control protects the reliability of every result the laboratory produces.\n\n## Control materials\n\nControl materials with known target values are measured alongside patient samples.\n\n## Monitoring rules\n\nLevey-Jennings charts visualize results over time, and Westgard rules flag systematic or random errors.\n\n## Responding to failures\n\nInvestigate the cause, document the action, and repeat controls before releasing patient results.",
                'topic_category' => 'Laboratory Practice',
                'difficulty_level' => 'Intermediate',
                'learning_objectives' => "By the end of this resource you should be able to:\n\n- Explain the purpose of quality control materials.\n- Read a Levey-Jennings chart.\n- Recognize common Westgard rule violations.",
                'author_name' => 'Labgoto Team',
                'reading_time_minutes' => 9,
            ],
        ];

        foreach ($resources as $data) {
            KnowledgeResource::firstOrCreate(
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
            KnowledgeResource::query()->where('slug', 'introduction-to-hematology')->get()
                ->each(fn (KnowledgeResource $resource) => $resource->categories()->syncWithoutDetaching([$hematology->id]));
        }

        if ($clinicalChemistry) {
            KnowledgeResource::query()->where('slug', 'clinical-chemistry-overview')->get()
                ->each(fn (KnowledgeResource $resource) => $resource->categories()->syncWithoutDetaching([$clinicalChemistry->id]));
        }
    }
}
