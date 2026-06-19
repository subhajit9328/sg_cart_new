<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ────────────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        // ── Admin ──────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin User',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // ── Product Manager ────────────────────────────────────────────────
        $productManager = User::firstOrCreate(
            ['email' => 'products@example.com'],
            [
                'name'              => 'Product Manager',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $productManager->syncRoles(['product-manager']);

        // ── Viewer ─────────────────────────────────────────────────────────
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name'              => 'Viewer User',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $viewer->syncRoles(['viewer']);

        $this->command->info('✅ Admin users seeded.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super-admin',     'superadmin@example.com', 'password'],
                ['admin',           'admin@example.com',      'password'],
                ['product-manager', 'products@example.com',   'password'],
                ['viewer',          'viewer@example.com',     'password'],
            ]
        );
    }
}