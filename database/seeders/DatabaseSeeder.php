<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        if (app()->environment('local') && config('labgato.seed.admin.enabled')) {
            $this->call([
                AdminUserSeeder::class,
            ]);
        }

        if (config('labgato.seed.categories.enabled')) {
            $this->call([
                LaboratoryCategorySeeder::class,
            ]);
        }
    }
}
