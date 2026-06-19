<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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
            'manage products',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions (with ULID for URL-safe admin routes)
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['guard_name' => 'web', 'ulid' => (string) Str::ulid()]
        );
        $superAdminRole->syncPermissions(Permission::all());

        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager'],
            ['guard_name' => 'web', 'ulid' => (string) Str::ulid()]
        );
        $managerRole->syncPermissions(['view dashboard', 'manage users', 'manage products']);

        $userRole = Role::firstOrCreate(
            ['name' => 'User'],
            ['guard_name' => 'web', 'ulid' => (string) Str::ulid()]
        );
        $userRole->syncPermissions(['view dashboard']);

        // Back-fill ULID for any role that was created without one
        Role::whereNull('ulid')->each(function (Role $role) {
            $role->update(['ulid' => (string) Str::ulid()]);
        });

        // Create default users and assign roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@sgcart.com'],
            [
                'name'     => 'Admin Sgcart',
                'password' => bcrypt('password'),
            ]
        );
        if (!$admin->hasRole($superAdminRole)) {
            $admin->assignRole($superAdminRole);
        }

        $manager = User::firstOrCreate(
            ['email' => 'manager@sgcart.com'],
            [
                'name'     => 'Manager Sgcart',
                'password' => bcrypt('password'),
            ]
        );
        if (!$manager->hasRole($managerRole)) {
            $manager->assignRole($managerRole);
        }

        $regularUser = User::firstOrCreate(
            ['email' => 'user@sgcart.com'],
            [
                'name'     => 'User Sgcart',
                'password' => bcrypt('password'),
            ]
        );
        if (!$regularUser->hasRole($userRole)) {
            $regularUser->assignRole($userRole);
        }

        // Call child seeders
        $this->call([
            CategorySeeder::class,
            ManufacturerSeeder::class,
        ]);
    }
}
