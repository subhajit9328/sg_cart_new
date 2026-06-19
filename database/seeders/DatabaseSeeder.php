<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        $managerRole = Role::create(['name' => 'Manager']);
        $managerRole->givePermissionTo(['view dashboard', 'manage users']);

        $userRole = Role::create(['name' => 'User']);
        $userRole->givePermissionTo(['view dashboard']);

        // Create default users and assign roles
        $admin = User::create([
            'name' => 'Admin Sgcart',
            'email' => 'admin@sgcart.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($superAdminRole);

        $manager = User::create([
            'name' => 'Manager Sgcart',
            'email' => 'manager@sgcart.com',
            'password' => bcrypt('password'),
        ]);
        $manager->assignRole($managerRole);

        $regularUser = User::create([
            'name' => 'User Sgcart',
            'email' => 'user@sgcart.com',
            'password' => bcrypt('password'),
        ]);
        $regularUser->assignRole($userRole);
    }
}
