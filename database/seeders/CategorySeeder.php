<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'name'        => 'Men\'s Clothing',
                'description' => 'Apparel and fashion for men',
                'image_url'   => 'https://images.unsplash.com/photo-1490367532201-b9bc1dc483f6?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'T-Shirts & Polos',   'description' => 'Casual tees, polo shirts and graphic tops', 'image_url' => 'https://images.unsplash.com/photo-1625910513399-c9fcba54338c?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'],
                    ['name' => 'Shirts',              'description' => 'Formal, casual and linen shirts', 'image_url' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Jeans & Trousers',   'description' => 'Denim jeans, chinos and formal trousers', 'image_url' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Kurta & Ethnic',      'description' => 'Kurtas, sherwanis and ethnic wear', 'image_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Jackets & Coats',     'description' => 'Bomber jackets, blazers and winter coats', 'image_url' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Activewear',          'description' => 'Gym wear, tracksuits and sports clothing', 'image_url' => 'https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Innerwear & Socks',   'description' => 'Briefs, boxers, vests and socks', 'image_url' => 'https://images.unsplash.com/photo-1582966772680-860e372bb558?q=80&w=600&auto=format&fit=crop'],
                ],
            ],
            [
                'name'        => 'Women\'s Clothing',
                'description' => 'Apparel and fashion for women',
                'image_url'   => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'Tops & Blouses',      'description' => 'Casual tops, blouses and shirts', 'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Dresses',             'description' => 'Casual, party and maxi dresses', 'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Sarees & Lehengas',   'description' => 'Traditional sarees, lehengas and ethnic wear', 'image_url' => 'https://images.unsplash.com/photo-1690617641065-34cd4abeca46?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8N3x8bGFoZW5nYXxlbnwwfHwwfHx8MA%3D%3D'],
                    ['name' => 'Salwar Kameez',       'description' => 'Salwar suits, churidars and anarkalis', 'image_url' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Jeans & Trousers',    'description' => 'Denim jeans, palazzos and formal pants', 'image_url' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Jackets & Coats',     'description' => 'Shrugs, blazers and winter jackets', 'image_url' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Activewear',          'description' => 'Sports bras, leggings and gym wear', 'image_url' => 'https://images.unsplash.com/photo-1518310383802-640c2de311b2?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Innerwear & Lingerie','description' => 'Bras, panties and sleepwear', 'image_url' => 'https://images.unsplash.com/photo-1562572159-4ebcd318f4dd?q=80&w=600&auto=format&fit=crop'],
                ],
            ],
            [
                'name'        => 'Kids\' Clothing',
                'description' => 'Clothing for boys, girls and infants',
                'image_url'   => 'https://images.unsplash.com/photo-1503919545889-aef636e10ad4?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'Boys\' Clothing',    'description' => 'T-shirts, jeans and ethnic wear for boys', 'image_url' => 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Girls\' Clothing',   'description' => 'Dresses, tops and ethnic wear for girls', 'image_url' => 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Infant & Toddler',   'description' => 'Onesies, rompers and baby clothing', 'image_url' => 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8SW5mYW50JTIwJTI2JTIwVG9kZGxlciUyMGNsb3Roc3xlbnwwfHwwfHx8MA%3D%3D'],
                    ['name' => 'School Uniforms',    'description' => 'Uniforms, ties and accessories', 'image_url' => 'https://images.unsplash.com/photo-1508847154043-be12a62861c1?q=80&w=600&auto=format&fit=crop'],
                ],
            ],
            [
                'name'        => 'Footwear',
                'description' => 'Shoes, sandals and boots for all',
                'image_url'   => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'Men\'s Footwear',    'description' => 'Sneakers, loafers, formal and sports shoes for men', 'image_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Women\'s Footwear',  'description' => 'Heels, flats, sandals and boots for women', 'image_url' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Kids\' Footwear',    'description' => 'School shoes, sandals and sneakers for kids', 'image_url' => 'https://images.unsplash.com/photo-1514989940723-e8e5163ccbe8?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Ethnic Footwear',    'description' => 'Juttis, kolhapuris and ethnic sandals', 'image_url' => 'https://images.unsplash.com/photo-1678192568478-9488ee55def6?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8S2lkcyclMjBGb290d2VhcnxlbnwwfHwwfHx8MA%3D%3D'],
                ],
            ],
            [
                'name'        => 'Accessories',
                'description' => 'Fashion accessories to complete your look',
                'image_url'   => 'https://images.unsplash.com/photo-1523293182086-7651a899d37f?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'Bags & Handbags',    'description' => 'Tote bags, backpacks, clutches and sling bags', 'image_url' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Belts',              'description' => 'Leather, fabric and casual belts', 'image_url' => 'https://images.unsplash.com/photo-1664285612706-b32633c95820?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8QmVsdHN8ZW58MHx8MHx8fDA%3D'],
                    ['name' => 'Scarves & Stoles',   'description' => 'Dupattas, scarves and wraps', 'image_url' => 'https://images.unsplash.com/photo-1584030373081-f37b7bb4fa8e?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Sunglasses',         'description' => 'UV-protected fashion sunglasses', 'image_url' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Watches',            'description' => 'Analog, digital and smart fashion watches', 'image_url' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Caps & Hats',        'description' => 'Baseball caps, beanies and sun hats', 'image_url' => 'https://images.unsplash.com/photo-1534215754734-18e55d13e346?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Jewellery',          'description' => 'Earrings, necklaces, bracelets and rings', 'image_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?q=80&w=600&auto=format&fit=crop'],
                ],
            ],
            [
                'name'        => 'Sportswear',
                'description' => 'Performance and athletic clothing',
                'image_url'   => 'https://images.unsplash.com/photo-1483721310020-03333e577078?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'Running & Training', 'description' => 'Running tees, shorts and compression wear', 'image_url' => 'https://images.unsplash.com/photo-1597892657493-6847b9640bac?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTF8fFJ1bm5pbmclMjAlMjYlMjBUcmFpbmluZ3xlbnwwfHwwfHx8MA%3D%3D'],
                    ['name' => 'Yoga & Pilates',     'description' => 'Yoga pants, sports bras and tanks', 'image_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Cricket & Football', 'description' => 'Sports jerseys, shorts and kits', 'image_url' => 'https://plus.unsplash.com/premium_photo-1722086350831-3cc30b7d68a7?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8Q3JpY2tldCUyMCUyNiUyMEZvb3RiYWxsJTIwY2xvdGhzfGVufDB8fDB8fHww'],
                    ['name' => 'Winter Sports',      'description' => 'Thermal layers, snow jackets and ski gear', 'image_url' => 'https://images.unsplash.com/photo-1502680390469-be75c86b636f?q=80&w=600&auto=format&fit=crop'],
                ],
            ],
            [
                'name'        => 'Winter Wear',
                'description' => 'Warm clothing for cold seasons',
                'image_url'   => 'https://images.unsplash.com/photo-1544923246-77307dd654cb?q=80&w=600&auto=format&fit=crop',
                'children'    => [
                    ['name' => 'Sweaters & Pullovers','description' => 'Woollen and fleece sweaters', 'image_url' => 'https://images.unsplash.com/photo-1574201635302-388dd92a4c3f?q=80&w=765&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'],
                    ['name' => 'Hoodies & Sweatshirts','description' => 'Casual hoodies and zip-up sweatshirts', 'image_url' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Thermals',           'description' => 'Thermal innerwear and base layers', 'image_url' => 'https://images.unsplash.com/photo-1508427181148-530fc3e99137?q=80&w=600&auto=format&fit=crop'],
                    ['name' => 'Shawls & Mufflers',  'description' => 'Warm shawls, mufflers and gloves', 'image_url' => 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?q=80&w=600&auto=format&fit=crop'],
                ],
            ],
        ];

        foreach ($tree as $order => $parentData) {
            $children = $parentData['children'] ?? [];
            unset($parentData['children']);

            $imageUrl = $parentData['image_url'] ?? null;
            unset($parentData['image_url']);

            $imagePath = null;
            if (! empty($imageUrl)) {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 5,
                            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        ],
                    ]);
                    $imageContent = @file_get_contents($imageUrl, false, $context);
                    if ($imageContent !== false) {
                        $filename = 'categories/' . Str::slug($parentData['name']) . '.jpg';
                        Storage::disk('public')->put($filename, $imageContent);
                        $imagePath = $filename;
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Could not download image for category {$parentData['name']}: " . $e->getMessage());
                }
            }

            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($parentData['name'])],
                [
                    'name'        => $parentData['name'],
                    'description' => $parentData['description'],
                    'image'       => $imagePath,
                    'is_active'   => true,
                    'sort_order'  => $order,
                ]
            );

            foreach ($children as $childOrder => $childData) {
                $childImageUrl = $childData['image_url'] ?? null;
                unset($childData['image_url']);

                $childImagePath = null;
                if (! empty($childImageUrl)) {
                    try {
                        $context = stream_context_create([
                            'http' => [
                                'timeout' => 5,
                                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                            ],
                        ]);
                        $imageContent = @file_get_contents($childImageUrl, false, $context);
                        if ($imageContent !== false) {
                            $filename = 'categories/' . Str::slug($childData['name']) . '.jpg';
                            Storage::disk('public')->put($filename, $imageContent);
                            $childImagePath = $filename;
                        }
                    } catch (\Exception $e) {
                        $this->command->warn("Could not download image for category {$childData['name']}: " . $e->getMessage());
                    }
                }

                Category::query()->updateOrCreate(
                    [
                        'name' => $childData['name'],
                        'parent_id' => $parent->id,
                    ],
                    [
                        'slug'        => Str::slug($parentData['name']) . '-' . Str::slug($childData['name']),
                        'description' => $childData['description'],
                        'image'       => $childImagePath,
                        'is_active'   => true,
                        'sort_order'  => $childOrder,
                    ]
                );
            }
        }

        $this->command->info('✅ Clothing categories and subcategories seeded with images.');
    }
}
