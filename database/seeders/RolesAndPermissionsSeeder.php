<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view tasks',
            'assign tasks',
            'complete tasks',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => trim($permission)]);
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $admin      = Role::firstOrCreate(['name' => 'Admin']);
        $manager    = Role::firstOrCreate(['name' => 'Manager']);
        $tl         = Role::firstOrCreate(['name' => 'TL']);
        $employee   = Role::firstOrCreate(['name' => 'Employee']);

        // Assign all permissions to Super Admin and Admin
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);

        // Assign selected permissions to Manager
        $manager->syncPermissions([
            'view users',
            'view roles',
            'view tasks',
            'assign tasks',
        ]);

        // Assign TL permissions
        $tl->syncPermissions([
            'view tasks',
            'assign tasks',
        ]);

        // Assign basic permission to Employee
        $employee->syncPermissions([
            'view tasks',
            'complete tasks',
        ]);
    }
}
