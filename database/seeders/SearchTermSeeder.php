<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class SearchTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productSearchTerms = [
            'RAY-LIN-SH-001' => ['linen', 'shirt', 'raymond', 'classic', 'formal', 'white', 'breathable', 'cotton'],
            'LEV-511-DM-002' => ['denim', 'jeans', 'blue', 'slim', 'levis', 'pants', 'stretch', 'casual'],
            'NKE-AM-RUN-003' => ['red', 'shoes', 'running', 'nike', 'sneakers', 'air max', 'comfort', 'mesh'],
            'ADI-FL-HD-004' => ['grey', 'hoodie', 'adidas', 'fleece', 'training', 'sweatshirt', 'warm', 'athletic'],
            'ZAR-SAT-DR-005' => ['yellow', 'dress', 'zara', 'wrap', 'satin', 'evening', 'party', 'silk'],
            'FAB-SLK-AN-006' => ['silk', 'fabindia', 'anarkali', 'suit', 'ethnic', 'red', 'embroidery', 'festive'],
            'LP-OXF-SH-007' => ['blue', 'shirt', 'louis philippe', 'formal', 'oxford', 'cotton', 'wrinkle-resistant'],
            'PUM-DF-TE-008' => ['black', 'tee', 'puma', 'dry-fit', 'activewear', 'gym', 'training', 'polyester'],
            'LEV-GRP-TE-009' => ['white', 'tee', 'levis', 'graphic', 'logo', 'cotton', 'sportswear', 'jersey'],
            'WFW-COT-KU-010' => ['cotton', 'kurti', 'printed', 'ethnic', 'green', 'straight', 'daily-wear'],
            'ZAR-RTR-SG-011' => ['sunglasses', 'retro', 'zara', 'uv', 'black', 'eyewear', 'acetate', 'square'],
            'ZAR-MN-HB-012' => ['leather', 'handbag', 'zara', 'shopper', 'minimalist', 'black', 'bag', 'zipper'],
            'UCB-CB-SW-013' => ['sweater', 'colorblock', 'united colors of benetton', 'wool', 'pullover', 'knit', 'cozy'],
            'FAB-HND-SR-014' => ['silk', 'saree', 'fabindia', 'handcrafted', 'red', 'ethnic', 'banarasi', 'handwoven'],
            'RAY-PRM-BL-015' => ['blazer', 'wool', 'raymond', 'premium', 'blue', 'formal', 'jacket', 'slim fit'],
        ];

        foreach ($productSearchTerms as $sku => $terms) {
            $product = Product::where('sku', $sku)->first();
            if ($product) {
                foreach ($terms as $term) {
                    if (method_exists($product, 'searchTerms')) {
                        $product->searchTerms()->firstOrCreate([
                            'term' => strtolower(trim($term)),
                        ]);
                    }
                }
            }
        }
    }
}
