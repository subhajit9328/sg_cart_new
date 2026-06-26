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
            // H&M Relaxed Fit Linen-blend shirt (Light Blue)
            '1307417003' => ['light blue', 'shirt', 'H&M', 'linen', 'classic', 'formal', 'casual', 'cotton'],

            // Men's 511 Navy Blue Slim Fit Mid Rise Jeans
            'LEV-511-DM-002' => ['denim', 'jeans', 'navy blue', 'dark blue', 'slim fit', 'levis', 'pants', 'stretch', 'casual'],

            // Nike Air Max 95 Big Bubble SE
            'NKE-AM-RUN-003' => ['blue', 'black', 'shoes', 'running', 'nike', 'sneakers', 'air max', 'comfort', 'footwear'],

            // Adidas Essentials Fleece Hoodie (Grey)
            'ADI-FL-HD-004' => ['grey', 'hoodie', 'adidas', 'fleece', 'training', 'sweatshirt', 'warm', 'athletic'],

            // ZARA FLOWING WRAP DRESS (Green)
            'ZAR-SAT-DR-005' => ['green', 'dress', 'zara', 'wrap', 'satin', 'evening', 'party', 'flowing'],

            // Fabindia Teal Cotton Hand Block Printed Long Kurta (Teal/Light Blue)
            'FAB-SLK-AN-006' => ['teal', 'light blue', 'kurta', 'fabindia', 'cotton', 'ethnic', 'printed', 'daily-wear'],

            // Men White Slim Fit Solid Full Sleeves Casual Shirt (White)
            'LP-OXF-SH-007' => ['white', 'shirt', 'louis philippe', 'formal', 'solid', 'cotton', 'slim fit'],

            // PUMA Train All Day Men's Breathable Training Tee (Black)
            'PUM-DF-TE-008' => ['black', 't-shirt', 'tee', 'puma', 'dry-fit', 'activewear', 'gym', 'training'],

            // Men's Graphic Print Regular Fit Overdyed T-Shirt (Pink/Peach)
            'LEV-GRP-TE-009' => ['pink', 'peach', 't-shirt', 'tee', 'levis', 'graphic', 'logo', 'cotton', 'casual'],

            // W for Woman Navy Blue Embroidered Straight Kurta (Navy Blue)
            'WFW-COT-KU-010' => ['navy blue', 'kurti', 'kurta', 'embroidered', 'ethnic', 'w for woman', 'straight', 'daily-wear'],

            // ZARA RETRO SQUARE SUNGLASSES (Black)
            'ZAR-RTR-SG-011' => ['sunglasses', 'retro', 'zara', 'uv', 'black', 'eyewear', 'acetate', 'square'],

            // ZARA CITY CROSSBODY BAG (Grey/Taupe)
            'ZAR-MN-HB-012' => ['grey', 'taupe', 'crossbody', 'handbag', 'zara', 'shopper', 'minimalist', 'bag', 'leather'],

            // Benetton Women Round Neck Colorblock Sweater (Multicolor)
            'UCB-CB-SW-013' => ['sweater', 'multicolor', 'rainbow', 'colorblock', 'united colors of benetton', 'wool', 'pullover', 'knit'],

            // FABINDIA Red Cotton Hand Block Printed Sari (Red)
            'FAB-HND-SR-014' => ['red', 'saree', 'sari', 'fabindia', 'handcrafted', 'ethnic', 'cotton', 'handwoven'],

            // Raymond Men Black Regular Fit Solid Formal Blazer (Black)
            'RAY-PRM-BL-015' => ['black', 'blazer', 'wool', 'raymond', 'premium', 'formal', 'jacket', 'regular fit'],
        ];

        foreach ($productSearchTerms as $sku => $terms) {
            $product = Product::where('sku', $sku)->first();

            if ($product) {
                foreach ($terms as $term) {
                    if (method_exists($product, 'searchTerms')) {
                        $product->searchTerms()->updateOrCreate([
                            'term' => strtolower(trim($term)),
                        ]);
                    }
                }
            }
        }
    }
}
