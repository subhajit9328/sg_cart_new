<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Define all permissions ─────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view dashboard',

            // User management
            'manage users',

            // Role management
            'manage roles',

            // Product & catalogue management
            'manage products',
            'manage categories',
            'manage manufacturers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // ── Define roles and assign permissions ────────────────────────────

        // Super Admin — all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // Admin — all except role management
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view dashboard',
            'manage users',
            'manage products',
            'manage categories',
            'manage manufacturers',
        ]);

        // Product Manager — only catalogue
        $productManager = Role::firstOrCreate(['name' => 'product-manager', 'guard_name' => 'web']);
        $productManager->syncPermissions([
            'view dashboard',
            'manage products',
            'manage categories',
            'manage manufacturers',
        ]);

        // Viewer — read-only dashboard access
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'view dashboard',
        ]);

        $this->command->info('✅ Permissions and roles seeded.');
    }
}