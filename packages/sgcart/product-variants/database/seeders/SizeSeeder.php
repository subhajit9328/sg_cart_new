<?php

namespace SGCart\ProductVariants\Database\Seeders;

use Illuminate\Database\Seeder;
use SGCart\ProductVariants\Models\Size;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = [
            // Apparel sizes
            ['name' => 'Extra Small',   'code' => 'XS'],
            ['name' => 'Small',         'code' => 'S'],
            ['name' => 'Medium',        'code' => 'M'],
            ['name' => 'Large',         'code' => 'L'],
            ['name' => 'Extra Large',   'code' => 'XL'],
            ['name' => 'Double Extra Large',  'code' => 'XXL'],
            ['name' => 'Triple Extra Large',  'code' => 'XXXL'],

            // Numeric sizes (shoes / bottoms)
            ['name' => 'Size 28',  'code' => '28'],
            ['name' => 'Size 30',  'code' => '30'],
            ['name' => 'Size 32',  'code' => '32'],
            ['name' => 'Size 34',  'code' => '34'],
            ['name' => 'Size 36',  'code' => '36'],
            ['name' => 'Size 38',  'code' => '38'],
            ['name' => 'Size 40',  'code' => '40'],
            ['name' => 'Size 42',  'code' => '42'],

            // Generic
            ['name' => 'Free Size', 'code' => 'FREE'],
        ];

        foreach ($sizes as $size) {
            Size::firstOrCreate(['code' => $size['code']], ['name' => $size['name']]);
        }
    }
}
