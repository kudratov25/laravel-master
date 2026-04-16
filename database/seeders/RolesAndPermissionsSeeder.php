<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions per model
        $permissions = [
            'example-post.viewAny',
            'example-post.view',
            'example-post.create',
            'example-post.update',
            'example-post.delete',
            // Add more as you create new models:
            // 'article.viewAny', 'article.view', ...
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin role — gets all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Editor role — can view and manage posts but not delete
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'example-post.viewAny',
            'example-post.view',
            'example-post.create',
            'example-post.update',
        ]);

        // Viewer role — read only
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'example-post.viewAny',
            'example-post.view',
        ]);
    }
}
