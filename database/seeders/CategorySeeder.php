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
                'name'        => 'Electronics',
                'description' => 'Electronic gadgets and devices',
                'children'    => [
                    ['name' => 'Mobile Phones',  'description' => 'Smartphones and feature phones'],
                    ['name' => 'Laptops',         'description' => 'Laptops and notebooks'],
                    ['name' => 'Tablets',         'description' => 'Tablets and iPads'],
                    ['name' => 'Accessories',     'description' => 'Cables, cases, chargers'],
                ],
            ],
            [
                'name'        => 'Clothing',
                'description' => 'Apparel and fashion',
                'children'    => [
                    ['name' => 'Men',     'description' => "Men's clothing"],
                    ['name' => 'Women',   'description' => "Women's clothing"],
                    ['name' => 'Kids',    'description' => "Children's clothing"],
                    ['name' => 'Footwear','description' => 'Shoes, sandals, boots'],
                ],
            ],
            [
                'name'        => 'Home & Kitchen',
                'description' => 'Home appliances and kitchenware',
                'children'    => [
                    ['name' => 'Appliances',  'description' => 'Kitchen and home appliances'],
                    ['name' => 'Furniture',   'description' => 'Tables, chairs, beds'],
                    ['name' => 'Cookware',    'description' => 'Pots, pans, utensils'],
                    ['name' => 'Decor',       'description' => 'Home decoration items'],
                ],
            ],
            [
                'name'        => 'Sports & Fitness',
                'description' => 'Sports equipment and fitness gear',
                'children'    => [
                    ['name' => 'Exercise Equipment', 'description' => 'Gym and fitness tools'],
                    ['name' => 'Outdoor Sports',     'description' => 'Cricket, football, etc.'],
                    ['name' => 'Yoga & Meditation',  'description' => 'Mats, blocks, accessories'],
                ],
            ],
            [
                'name'        => 'Books & Stationery',
                'description' => 'Books, notebooks, and office supplies',
                'children'    => [
                    ['name' => 'Books',       'description' => 'Fiction, non-fiction, textbooks'],
                    ['name' => 'Stationery',  'description' => 'Pens, notebooks, art supplies'],
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

        $this->command->info(' Categories and subcategories seeded.');
    }
}