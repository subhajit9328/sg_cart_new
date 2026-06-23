<?php

namespace SGCart\ProductVariants\Database\Seeders;

use Illuminate\Database\Seeder;
use SGCart\ProductVariants\Models\Color;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['name' => 'Red',    'hex_code' => '#FF0000'],
            ['name' => 'Green',  'hex_code' => '#008000'],
            ['name' => 'Blue',   'hex_code' => '#0000FF'],
            ['name' => 'Black',  'hex_code' => '#000000'],
            ['name' => 'White',  'hex_code' => '#FFFFFF'],
            ['name' => 'Yellow', 'hex_code' => '#FFFF00'],
            ['name' => 'Orange', 'hex_code' => '#FFA500'],
            ['name' => 'Purple', 'hex_code' => '#800080'],
            ['name' => 'Pink',   'hex_code' => '#FFC0CB'],
            ['name' => 'Brown',  'hex_code' => '#A52A2A'],
            ['name' => 'Gray',   'hex_code' => '#808080'],
            ['name' => 'Navy',   'hex_code' => '#000080'],
            ['name' => 'Teal',   'hex_code' => '#008080'],
            ['name' => 'Maroon', 'hex_code' => '#800000'],
            ['name' => 'Beige',  'hex_code' => '#F5F5DC'],
        ];

        foreach ($colors as $color) {
            Color::firstOrCreate(['name' => $color['name']], ['hex_code' => $color['hex_code']]);
        }
    }
}
