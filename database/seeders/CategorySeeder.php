<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name'        => 'Men\'s Clothing',
                'description' => 'Apparel and fashion for men',
                'children'    => [
                    ['name' => 'T-Shirts & Polos',   'description' => 'Casual tees, polo shirts and graphic tops'],
                    ['name' => 'Shirts',              'description' => 'Formal, casual and linen shirts'],
                    ['name' => 'Jeans & Trousers',   'description' => 'Denim jeans, chinos and formal trousers'],
                    ['name' => 'Kurta & Ethnic',      'description' => 'Kurtas, sherwanis and ethnic wear'],
                    ['name' => 'Jackets & Coats',     'description' => 'Bomber jackets, blazers and winter coats'],
                    ['name' => 'Activewear',          'description' => 'Gym wear, tracksuits and sports clothing'],
                    ['name' => 'Innerwear & Socks',   'description' => 'Briefs, boxers, vests and socks'],
                ],
            ],
            [
                'name'        => 'Women\'s Clothing',
                'description' => 'Apparel and fashion for women',
                'children'    => [
                    ['name' => 'Tops & Blouses',      'description' => 'Casual tops, blouses and shirts'],
                    ['name' => 'Dresses',             'description' => 'Casual, party and maxi dresses'],
                    ['name' => 'Sarees & Lehengas',   'description' => 'Traditional sarees, lehengas and ethnic wear'],
                    ['name' => 'Salwar Kameez',       'description' => 'Salwar suits, churidars and anarkalis'],
                    ['name' => 'Jeans & Trousers',    'description' => 'Denim jeans, palazzos and formal pants'],
                    ['name' => 'Jackets & Coats',     'description' => 'Shrugs, blazers and winter jackets'],
                    ['name' => 'Activewear',          'description' => 'Sports bras, leggings and gym wear'],
                    ['name' => 'Innerwear & Lingerie','description' => 'Bras, panties and sleepwear'],
                ],
            ],
            [
                'name'        => 'Kids\' Clothing',
                'description' => 'Clothing for boys, girls and infants',
                'children'    => [
                    ['name' => 'Boys\' Clothing',    'description' => 'T-shirts, jeans and ethnic wear for boys'],
                    ['name' => 'Girls\' Clothing',   'description' => 'Dresses, tops and ethnic wear for girls'],
                    ['name' => 'Infant & Toddler',   'description' => 'Onesies, rompers and baby clothing'],
                    ['name' => 'School Uniforms',    'description' => 'Uniforms, ties and accessories'],
                ],
            ],
            [
                'name'        => 'Footwear',
                'description' => 'Shoes, sandals and boots for all',
                'children'    => [
                    ['name' => 'Men\'s Footwear',    'description' => 'Sneakers, loafers, formal and sports shoes for men'],
                    ['name' => 'Women\'s Footwear',  'description' => 'Heels, flats, sandals and boots for women'],
                    ['name' => 'Kids\' Footwear',    'description' => 'School shoes, sandals and sneakers for kids'],
                    ['name' => 'Ethnic Footwear',    'description' => 'Juttis, kolhapuris and ethnic sandals'],
                ],
            ],
            [
                'name'        => 'Accessories',
                'description' => 'Fashion accessories to complete your look',
                'children'    => [
                    ['name' => 'Bags & Handbags',    'description' => 'Tote bags, backpacks, clutches and sling bags'],
                    ['name' => 'Belts',              'description' => 'Leather, fabric and casual belts'],
                    ['name' => 'Scarves & Stoles',   'description' => 'Dupattas, scarves and wraps'],
                    ['name' => 'Sunglasses',         'description' => 'UV-protected fashion sunglasses'],
                    ['name' => 'Watches',            'description' => 'Analog, digital and smart fashion watches'],
                    ['name' => 'Caps & Hats',        'description' => 'Baseball caps, beanies and sun hats'],
                    ['name' => 'Jewellery',          'description' => 'Earrings, necklaces, bracelets and rings'],
                ],
            ],
            [
                'name'        => 'Sportswear',
                'description' => 'Performance and athletic clothing',
                'children'    => [
                    ['name' => 'Running & Training', 'description' => 'Running tees, shorts and compression wear'],
                    ['name' => 'Yoga & Pilates',     'description' => 'Yoga pants, sports bras and tanks'],
                    ['name' => 'Cricket & Football', 'description' => 'Sports jerseys, shorts and kits'],
                    ['name' => 'Winter Sports',      'description' => 'Thermal layers, snow jackets and ski gear'],
                ],
            ],
            [
                'name'        => 'Winter Wear',
                'description' => 'Warm clothing for cold seasons',
                'children'    => [
                    ['name' => 'Sweaters & Pullovers','description' => 'Woollen and fleece sweaters'],
                    ['name' => 'Hoodies & Sweatshirts','description' => 'Casual hoodies and zip-up sweatshirts'],
                    ['name' => 'Thermals',           'description' => 'Thermal innerwear and base layers'],
                    ['name' => 'Shawls & Mufflers',  'description' => 'Warm shawls, mufflers and gloves'],
                ],
            ],
        ];

        foreach ($tree as $order => $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($parentData['name'])],
                [
                    ...$parentData,
                    'is_active'  => true,
                    'sort_order' => $order,
                ]
            );

            foreach ($children as $childOrder => $childData) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childData['name'])],
                    [
                        ...$childData,
                        'parent_id'  => $parent->id,
                        'is_active'  => true,
                        'sort_order' => $childOrder,
                    ]
                );
            }
        }

        $this->command->info('✅ Clothing categories and subcategories seeded.');
    }
}