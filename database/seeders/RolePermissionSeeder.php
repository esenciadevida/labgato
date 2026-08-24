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
            'dashboard.view' => ['name' => 'View Dashboard', 'group' => 'Dashboard'],
            'users.view' => ['name' => 'View Users', 'group' => 'Users'],
            'users.create' => ['name' => 'Create Users', 'group' => 'Users'],
            'users.edit' => ['name' => 'Edit Users', 'group' => 'Users'],
            'users.delete' => ['name' => 'Delete Users', 'group' => 'Users'],
            'users.assign-roles' => ['name' => 'Assign Roles', 'group' => 'Users'],
            'roles.view' => ['name' => 'View Roles', 'group' => 'Roles'],
            'roles.manage' => ['name' => 'Manage Roles', 'group' => 'Roles'],
            'taxonomy.view' => ['name' => 'View Taxonomy', 'group' => 'Taxonomy'],
            'taxonomy.manage' => ['name' => 'Manage Taxonomy', 'group' => 'Taxonomy'],
            'audit-logs.view' => ['name' => 'View Audit Logs', 'group' => 'Audit Logs'],
            'analytics.view' => ['name' => 'View Analytics', 'group' => 'Analytics'],
            'settings.view' => ['name' => 'View Settings', 'group' => 'Settings'],
            'settings.manage' => ['name' => 'Manage Settings', 'group' => 'Settings'],
            'tools.view' => ['name' => 'View Tools', 'group' => 'Tools'],
            'tools.create' => ['name' => 'Create Tools', 'group' => 'Tools'],
            'tools.edit' => ['name' => 'Edit Tools', 'group' => 'Tools'],
            'tools.publish' => ['name' => 'Publish Tools', 'group' => 'Tools'],
            'tools.delete' => ['name' => 'Delete Tools', 'group' => 'Tools'],
            'calculators.view' => ['name' => 'View Calculators', 'group' => 'Calculators'],
            'calculators.create' => ['name' => 'Create Calculators', 'group' => 'Calculators'],
            'calculators.edit' => ['name' => 'Edit Calculators', 'group' => 'Calculators'],
            'calculators.publish' => ['name' => 'Publish Calculators', 'group' => 'Calculators'],
            'calculators.delete' => ['name' => 'Delete Calculators', 'group' => 'Calculators'],
            'reference-ranges.view' => ['name' => 'View Reference Ranges', 'group' => 'Reference Ranges'],
            'reference-ranges.create' => ['name' => 'Create Reference Ranges', 'group' => 'Reference Ranges'],
            'reference-ranges.edit' => ['name' => 'Edit Reference Ranges', 'group' => 'Reference Ranges'],
            'reference-ranges.publish' => ['name' => 'Publish Reference Ranges', 'group' => 'Reference Ranges'],
            'reference-ranges.delete' => ['name' => 'Delete Reference Ranges', 'group' => 'Reference Ranges'],
            'sops.view' => ['name' => 'View SOPs', 'group' => 'SOPs'],
            'sops.create' => ['name' => 'Create SOPs', 'group' => 'SOPs'],
            'sops.edit' => ['name' => 'Edit SOPs', 'group' => 'SOPs'],
            'sops.publish' => ['name' => 'Publish SOPs', 'group' => 'SOPs'],
            'sops.delete' => ['name' => 'Delete SOPs', 'group' => 'SOPs'],
            'interpretation.view' => ['name' => 'View Interpretation', 'group' => 'Interpretation'],
            'interpretation.create' => ['name' => 'Create Interpretation', 'group' => 'Interpretation'],
            'interpretation.edit' => ['name' => 'Edit Interpretation', 'group' => 'Interpretation'],
            'interpretation.publish' => ['name' => 'Publish Interpretation', 'group' => 'Interpretation'],
            'interpretation.delete' => ['name' => 'Delete Interpretation', 'group' => 'Interpretation'],
            'knowledge.view' => ['name' => 'View Knowledge', 'group' => 'Knowledge'],
            'knowledge.create' => ['name' => 'Create Knowledge', 'group' => 'Knowledge'],
            'knowledge.edit' => ['name' => 'Edit Knowledge', 'group' => 'Knowledge'],
            'knowledge.publish' => ['name' => 'Publish Knowledge', 'group' => 'Knowledge'],
            'knowledge.delete' => ['name' => 'Delete Knowledge', 'group' => 'Knowledge'],
            'reference-ranges.import' => ['name' => 'Import Reference Ranges CSV', 'group' => 'Reference Ranges'],
            'questions.view' => ['name' => 'View Questions', 'group' => 'Questions'],
            'questions.manage' => ['name' => 'Manage Questions', 'group' => 'Questions'],
            'questions.import' => ['name' => 'Import Questions CSV', 'group' => 'Questions'],
            'categories.manage' => ['name' => 'Manage Question Subjects', 'group' => 'Questions'],
            'integrity.view' => ['name' => 'View Exam Integrity', 'group' => 'Questions'],
            'contributions.review' => ['name' => 'Review Contributions', 'group' => 'Contributions'],
            'contact-messages.view' => ['name' => 'View Contact Messages', 'group' => 'Contact Messages'],
            'contact-messages.reply' => ['name' => 'Reply to Contact Messages', 'group' => 'Contact Messages'],
            'ai.use' => ['name' => 'Use AI', 'group' => 'AI'],
            'ai.manage' => ['name' => 'Manage AI', 'group' => 'AI'],
            'popups.manage' => ['name' => 'Manage Pop-ups', 'group' => 'Pop-ups'],
            'announcements.manage' => ['name' => 'Manage Announcements', 'group' => 'Announcements'],
            'admin.full-access' => ['name' => 'Full Admin Access', 'group' => 'Admin'],
        ];

        $permissionModels = [];
        foreach ($permissions as $slug => $data) {
            $permissionModels[$slug] = Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => $data['name'], 'group' => $data['group']]
            );
        }

        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-administrator'],
            ['name' => 'Super Administrator', 'is_system_role' => true]
        );
        $superAdmin->permissions()->sync($permissionModels);

        $contentManager = Role::firstOrCreate(
            ['slug' => 'content-manager'],
            ['name' => 'Content Manager', 'is_system_role' => false]
        );
        $contentManager->permissions()->sync([
            $permissionModels['dashboard.view'],
            $permissionModels['tools.view'],
            $permissionModels['tools.create'],
            $permissionModels['tools.edit'],
            $permissionModels['tools.publish'],
            $permissionModels['calculators.view'],
            $permissionModels['calculators.create'],
            $permissionModels['calculators.edit'],
            $permissionModels['calculators.publish'],
            $permissionModels['reference-ranges.view'],
            $permissionModels['reference-ranges.create'],
            $permissionModels['reference-ranges.edit'],
            $permissionModels['reference-ranges.publish'],
            $permissionModels['reference-ranges.import'],
            $permissionModels['sops.view'],
            $permissionModels['sops.create'],
            $permissionModels['sops.edit'],
            $permissionModels['sops.publish'],
            $permissionModels['interpretation.view'],
            $permissionModels['interpretation.create'],
            $permissionModels['interpretation.edit'],
            $permissionModels['interpretation.publish'],
            $permissionModels['knowledge.view'],
            $permissionModels['knowledge.create'],
            $permissionModels['knowledge.edit'],
            $permissionModels['knowledge.publish'],
            $permissionModels['contributions.review'],
            $permissionModels['taxonomy.view'],
            $permissionModels['taxonomy.manage'],
            $permissionModels['questions.view'],
            $permissionModels['questions.manage'],
            $permissionModels['questions.import'],
            $permissionModels['categories.manage'],
            $permissionModels['analytics.view'],
        ]);

        $viewerAnalyst = Role::firstOrCreate(
            ['slug' => 'viewer-analyst'],
            ['name' => 'Viewer / Analyst', 'is_system_role' => false]
        );
        $viewerAnalyst->permissions()->sync([
            $permissionModels['dashboard.view'],
            $permissionModels['tools.view'],
            $permissionModels['calculators.view'],
            $permissionModels['reference-ranges.view'],
            $permissionModels['sops.view'],
            $permissionModels['interpretation.view'],
            $permissionModels['knowledge.view'],
            $permissionModels['taxonomy.view'],
            $permissionModels['questions.view'],
            $permissionModels['audit-logs.view'],
            $permissionModels['analytics.view'],
        ]);
    }
}
