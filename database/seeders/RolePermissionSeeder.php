<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view-users' => 'View Users',
            'create-users' => 'Create Users',
            'edit-users' => 'Edit Users',
            'delete-users' => 'Delete Users',
            'view-roles' => 'View Roles',
            'manage-roles' => 'Manage Roles',
            'view-settings' => 'View Settings',
            'manage-settings' => 'Manage Settings',
            'view-audit-logs' => 'View Audit Logs',
            'view-categories' => 'View Categories',
            'manage-categories' => 'Manage Categories',
            'view-tags' => 'View Tags',
            'manage-tags' => 'Manage Tags',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::create(['name' => $name, 'display_name' => $displayName]);
        }

        $superAdmin = Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin']);
        $superAdmin->sync(Permission::all());

        $admin = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $admin->sync(Permission::where('name', '!=', 'manage-roles')->get());
    }
}
