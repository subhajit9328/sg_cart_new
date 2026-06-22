<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Raymond Classic Linen Shirt',
                'sku' => 'RAY-LIN-SH-001',
                'category_name' => 'Shirts',
                'manufacturer_name' => 'Raymond',
                'short_description' => 'Premium linen shirt for formal and semi-formal wear.',
                'description' => 'Raymond classic fit linen shirt crafted from premium flax fibers. Highly breathable, lightweight, and perfect for hot summer days.',
                'price' => 1899.00,
                'stock' => 50,
                'status' => 'active',
                'weight' => '250g',
                'dimensions' => '30x20x2 cm',
                'image_url' => 'https://images.unsplash.com/photo-1598032895397-b9472444bf93?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Levis 511 Slim Fit Jeans',
                'sku' => 'LEV-511-DM-002',
                'category_name' => 'Jeans & Trousers',
                'manufacturer_name' => 'Levi\'s',
                'short_description' => 'Classic Levi\'s slim fit denim jeans.',
                'description' => 'The original Levi\'s 511 slim fit jeans. Stretch denim with five-pocket styling and signature leather patch at back waistband.',
                'price' => 3299.00,
                'stock' => 80,
                'status' => 'active',
                'weight' => '600g',
                'dimensions' => '35x25x4 cm',
                'image_url' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1582562124811-c09040d0a901?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Nike Air Max Running Shoes',
                'sku' => 'NKE-AM-RUN-003',
                'category_name' => 'Men\'s Footwear',
                'manufacturer_name' => 'Nike',
                'short_description' => 'Nike high-performance running sneakers.',
                'description' => 'Nike Air Max sneakers with engineered mesh upper for breathability and visible Max Air cushioning unit for premium comfort.',
                'price' => 7499.00,
                'stock' => 35,
                'status' => 'active',
                'weight' => '800g',
                'dimensions' => '40x30x12 cm',
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Adidas Training Fleece Hoodie',
                'sku' => 'ADI-FL-HD-004',
                'category_name' => 'Hoodies & Sweatshirts',
                'manufacturer_name' => 'Adidas',
                'short_description' => 'Warm and comfortable athletic fleece hoodie.',
                'description' => 'Keep warm during outdoor workouts with this Adidas training hoodie. Made with primegreen recycled polyester and soft brushed fleece.',
                'price' => 2999.00,
                'stock' => 45,
                'status' => 'active',
                'weight' => '450g',
                'dimensions' => '32x22x5 cm',
                'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Zara Satin Wrap Dress',
                'sku' => 'ZAR-SAT-DR-005',
                'category_name' => 'Dresses',
                'manufacturer_name' => 'Zara',
                'short_description' => 'Zara elegant evening wrap dress.',
                'description' => 'Fluid Zara wrap dress with v-neckline, long sleeves, self-tie belt, and draped asymmetric hem. Perfect for dinners and parties.',
                'price' => 4599.00,
                'stock' => 25,
                'status' => 'active',
                'weight' => '350g',
                'dimensions' => '30x20x3 cm',
                'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Fabindia Silk Anarkali Suit',
                'sku' => 'FAB-SLK-AN-006',
                'category_name' => 'Salwar Kameez',
                'manufacturer_name' => 'Fabindia',
                'short_description' => 'Chanderi silk festive anarkali suit set.',
                'description' => 'Fabindia elegant Anarkali suit set crafted from a premium blend of silk and cotton with intricate golden Zari embroidery.',
                'price' => 5999.00,
                'stock' => 20,
                'status' => 'active',
                'weight' => '500g',
                'dimensions' => '38x28x5 cm',
                'image_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1610030469668-93535c17b6b3?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Louis Philippe Formal Oxford Shirt',
                'sku' => 'LP-OXF-SH-007',
                'category_name' => 'Shirts',
                'manufacturer_name' => 'Louis Philippe',
                'short_description' => 'Premium formal Oxford cotton shirt.',
                'description' => 'Dress sharply with this Louis Philippe Oxford cotton shirt. Features a classic button-down collar, structured cuffs, and wrinkle-resistant finish.',
                'price' => 2299.00,
                'stock' => 60,
                'status' => 'active',
                'weight' => '300g',
                'dimensions' => '30x20x2 cm',
                'image_url' => 'https://images.unsplash.com/photo-1621072156002-e2fcc10d9714?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1598032895397-b9472444bf93?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Puma Active Dry-Fit Tee',
                'sku' => 'PUM-DF-TE-008',
                'category_name' => 'Activewear',
                'manufacturer_name' => 'Puma',
                'short_description' => 'Breathable dry-fit polyester gym t-shirt.',
                'description' => 'Designed for intense training, this Puma activewear tee utilizes dryCELL sweat-wicking technology to keep you cool and dry.',
                'price' => 1299.00,
                'stock' => 100,
                'status' => 'active',
                'weight' => '150g',
                'dimensions' => '25x18x1 cm',
                'image_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Levis Graphic Logo Tee',
                'sku' => 'LEV-GRP-TE-009',
                'category_name' => 'T-Shirts & Polos',
                'manufacturer_name' => 'Levi\'s',
                'short_description' => 'Classic cotton tee with signature graphic.',
                'description' => 'Premium cotton jersey t-shirt from Levi\'s featuring the iconic sportswear logo printed across the chest.',
                'price' => 999.00,
                'stock' => 120,
                'status' => 'active',
                'weight' => '180g',
                'dimensions' => '25x18x1 cm',
                'image_url' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'W for Woman Cotton Straight Kurti',
                'sku' => 'WFW-COT-KU-010',
                'category_name' => 'Salwar Kameez',
                'manufacturer_name' => 'W for Woman',
                'short_description' => 'Printed daily-wear straight cotton kurti.',
                'description' => 'W for Woman straight silhouette daily wear kurti in comfortable pure cotton, printed with geometric ethnic patterns.',
                'price' => 1499.00,
                'stock' => 70,
                'status' => 'active',
                'weight' => '200g',
                'dimensions' => '30x22x1 cm',
                'image_url' => 'https://images.unsplash.com/photo-1609357605129-26f69add5d6e?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Zara Retro UV Sunglasses',
                'sku' => 'ZAR-RTR-SG-011',
                'category_name' => 'Sunglasses',
                'manufacturer_name' => 'Zara',
                'short_description' => 'Retro inspired acetate sunglasses.',
                'description' => 'Add retro charm with Zara acetate sunglasses. Featuring thick square frames and Category 3 UV400 lenses.',
                'price' => 1999.00,
                'stock' => 40,
                'status' => 'active',
                'weight' => '100g',
                'dimensions' => '18x8x6 cm',
                'image_url' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Zara Minimalist Leather Handbag',
                'sku' => 'ZAR-MN-HB-012',
                'category_name' => 'Bags & Handbags',
                'manufacturer_name' => 'Zara',
                'short_description' => 'Minimalist leather shopper bag.',
                'description' => 'Spacious shopper bag from Zara crafted in split leather. Unlined raw interior with gold-toned metal hardware and zipper pouch.',
                'price' => 4999.00,
                'stock' => 15,
                'status' => 'active',
                'weight' => '700g',
                'dimensions' => '40x35x15 cm',
                'image_url' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Benetton Colorblock Sweater',
                'sku' => 'UCB-CB-SW-013',
                'category_name' => 'Sweaters & Pullovers',
                'manufacturer_name' => 'United Colors of Benetton',
                'short_description' => 'Wool-blend crewneck colorblock knit sweater.',
                'description' => 'Warm and cozy crewneck sweater by United Colors of Benetton. Spun in a fine wool-blend knit with classic vibrant colorblock design.',
                'price' => 3499.00,
                'stock' => 30,
                'status' => 'active',
                'weight' => '400g',
                'dimensions' => '35x25x4 cm',
                'image_url' => 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Fabindia Handcrafted Silk Saree',
                'sku' => 'FAB-HND-SR-014',
                'category_name' => 'Sarees & Lehengas',
                'manufacturer_name' => 'Fabindia',
                'short_description' => 'Handwoven Banarasi silk saree with border.',
                'description' => 'Exquisite Fabindia handwoven Banarasi pure silk saree. Features all-over floral motifs and an ornate golden border. Includes unstitched blouse piece.',
                'price' => 8999.00,
                'stock' => 12,
                'status' => 'active',
                'weight' => '900g',
                'dimensions' => '45x35x6 cm',
                'image_url' => 'https://images.unsplash.com/photo-1610030470206-613d7d4b4a1b?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600&auto=format&fit=crop&q=80',
                ],
            ],
            [
                'name' => 'Raymond Premium Slim Blazer',
                'sku' => 'RAY-PRM-BL-015',
                'category_name' => 'Jackets & Coats',
                'manufacturer_name' => 'Raymond',
                'short_description' => 'Premium wool-blend slim fit blazer.',
                'description' => 'Raymond structured slim-fit blazer tailored in premium wool-blend fabric. Features notch lapels, flap pockets, and twin rear vents.',
                'price' => 6999.00,
                'stock' => 18,
                'status' => 'active',
                'weight' => '1100g',
                'dimensions' => '45x38x8 cm',
                'image_url' => 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?w=600&auto=format&fit=crop&q=80',
                'extra_image_urls' => [
                    'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=600&auto=format&fit=crop&q=80',
                ],
            ],
        ];

        // Ensure target directory exists in public disk
        if (!Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        foreach ($products as $p) {
            $catId = Category::where('name', $p['category_name'])->value('id');
            $manId = Manufacturer::where('name', $p['manufacturer_name'])->value('id');

            // Download default image file from URL and save to products folder
            $imagePath = null;
            if (!empty($p['image_url'])) {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 5, // 5 seconds timeout
                            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        ]
                    ]);
                    $imageContent = @file_get_contents($p['image_url'], false, $context);
                    if ($imageContent !== false) {
                        $filename = 'products/' . $p['sku'] . '.jpg';
                        Storage::disk('public')->put($filename, $imageContent);
                        $imagePath = $filename;
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Could not download main image for {$p['sku']}: " . $e->getMessage());
                }
            }

            $extraImageUrls = $p['extra_image_urls'] ?? [];

            unset($p['category_name'], $p['manufacturer_name'], $p['image_url'], $p['extra_image_urls']);

            $productModel = Product::firstOrCreate(
                ['sku' => $p['sku']],
                [
                    ...$p,
                    'category_id' => $catId,
                    'manufacturer_id' => $manId,
                ]
            );

            // Save default image to product_images table
            if ($imagePath) {
                $productModel->images()->firstOrCreate([
                    'image_path' => $imagePath,
                    'is_default' => true,
                ]);
            }

            // Seed extra images to product_images table
            foreach ($extraImageUrls as $index => $url) {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 5, // 5 seconds timeout
                            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        ]
                    ]);
                    $imageContent = @file_get_contents($url, false, $context);
                    if ($imageContent !== false) {
                        $filename = 'products/' . $p['sku'] . '_extra_' . ($index + 1) . '.jpg';
                        Storage::disk('public')->put($filename, $imageContent);
                        
                        $productModel->images()->firstOrCreate([
                            'image_path' => $filename,
                            'is_default' => false,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Could not download extra image for {$p['sku']}: " . $e->getMessage());
                }
            }
        }

        $this->command->info('✅ 15 Products successfully seeded with default and extra mock images.');
    }
}
