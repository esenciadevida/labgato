<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            LaunchSettingSeeder::class,
            QuestionCategorySeeder::class,
            AssessmentSeeder::class,
        ]);

        if (app()->environment('local') && config('labgoto.seed.admin.enabled')) {
            $this->call([
                AdminUserSeeder::class,
            ]);
        }

        if (config('labgoto.seed.categories.enabled')) {
            $this->call([
                LaboratoryCategorySeeder::class,
            ]);
        }

        if (config('labgoto.seed.master_calculators.enabled')) {
            $this->call([
                MasterCalculatorSeeder::class,
            ]);
        }

        if (config('labgoto.seed.master_tools.enabled')) {
            $this->call([
                MasterToolSeeder::class,
            ]);
        }

        if (config('labgoto.seed.reference_ranges.enabled')) {
            $this->call([
                ReferenceRangeSeeder::class,
                ReferenceRangeDataSeeder::class,
            ]);
        }

        if (config('labgoto.seed.interpretation.enabled')) {
            $this->call([
                InterpretationResourceSeeder::class,
            ]);
        }

        if (config('labgoto.seed.knowledge.enabled')) {
            $this->call([
                MasterKnowledgeSeeder::class,
            ]);
        }

        if (config('labgoto.seed.exam_engine.enabled')) {
            $this->call([
                ExamEngineSeeder::class,
            ]);
        }
    }
}
