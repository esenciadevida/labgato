<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('labgato.seed.admin.enabled')) {
            return;
        }

        if (app()->environment() !== 'local') {
            return;
        }

        $email = config('labgato.seed.admin.email');
        $password = config('labgato.seed.admin.password');

        if (! $email || ! $password) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );

        $superAdminRole = Role::where('slug', 'super-administrator')->first();
        if ($superAdminRole && ! $admin->roles->contains($superAdminRole)) {
            $admin->roles()->attach($superAdminRole);
        }
    }
}
