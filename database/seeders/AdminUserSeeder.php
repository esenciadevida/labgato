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
        if (! config('labgoto.seed.admin.enabled')) {
            return;
        }

        if (app()->environment() !== 'local') {
            return;
        }

        $email = config('labgoto.seed.admin.email');
        $password = config('labgoto.seed.admin.password');
        $name = config('labgoto.seed.admin.name', 'Admin');

        if (! $email || ! $password) {
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_active' => true,
                'user_type' => 'admin',
            ]
        );

        if ($admin->user_type !== 'admin') {
            $admin->update(['user_type' => 'admin']);
        }

        $superAdminRole = Role::where('slug', 'super-administrator')->first();
        if ($superAdminRole && ! $admin->roles->contains($superAdminRole)) {
            $admin->roles()->attach($superAdminRole);
        }
    }
}
