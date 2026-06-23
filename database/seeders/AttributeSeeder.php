<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use SGCart\ProductVariants\Models\Color;
use SGCart\ProductVariants\Models\Size;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(\SGCart\ProductVariants\Models\Color::class) || !class_exists(\SGCart\ProductVariants\Models\Size::class)) {
            $this->command->warn('⚠️ Product variants package is not installed/enabled. Skipping AttributeSeeder.');
            return;
        }

        // ── Seed Standard Colors ──────────────────────────────────────────
        $colors = [
            ['name' => 'Midnight Black', 'hex_code' => '#000000'],
            ['name' => 'Pure White',     'hex_code' => '#ffffff'],
            ['name' => 'Crimson Red',    'hex_code' => '#ef4444'],
            ['name' => 'Royal Blue',     'hex_code' => '#3b82f6'],
            ['name' => 'Forest Green',   'hex_code' => '#22c55e'],
            ['name' => 'Lemon Yellow',   'hex_code' => '#eab308'],
            ['name' => 'Coral Pink',     'hex_code' => '#f43f5e'],
            ['name' => 'Heather Grey',   'hex_code' => '#6b7280'],
            ['name' => 'Navy Blue',      'hex_code' => '#1e3a8a'],
            ['name' => 'Olive Green',    'hex_code' => '#3f6212'],
        ];

        foreach ($colors as $color) {
            Color::firstOrCreate(
                ['name' => $color['name']],
                ['hex_code' => $color['hex_code']]
            );
        }

        // ── Seed Standard Sizes ───────────────────────────────────────────
        $sizes = [
            ['name' => 'Extra Small',        'code' => 'XS'],
            ['name' => 'Small',              'code' => 'S'],
            ['name' => 'Medium',             'code' => 'M'],
            ['name' => 'Large',              'code' => 'L'],
            ['name' => 'Extra Large',        'code' => 'XL'],
            ['name' => 'Double Extra Large', 'code' => 'XXL'],
            ['name' => 'Triple Extra Large', 'code' => '3XL'],
        ];

        foreach ($sizes as $size) {
            Size::firstOrCreate(
                ['code' => $size['code']],
                ['name' => $size['name']]
            );
        }

        $this->command->info('✅ Color swatches and size labels seeded.');
    }
}
