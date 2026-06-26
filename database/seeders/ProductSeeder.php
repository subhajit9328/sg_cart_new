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
                'name' => 'H&M MEN Relaxed Fit Linen-blend shirt',
                'sku' => '1307417003',
                'category_name' => 'Shirts',
                'manufacturer_name' => 'H&M',
                'short_description' => 'Premium linen shirt for formal and semi-formal wear.',
                'description' => <<<'DESC'
                    H&M Premium Selection
                    Shirt in an airy cotton and linen weave with a turn-down collar, French front, a yoke at the back and an open chest pocket. Long sleeves with adjustable buttoning at the cuffs and a sleeve placket with a link button. Rounded hem. Relaxed fit for a casual but not oversized silhouette. Cotton and linen blends combine the softness of cotton with the sturdiness of linen, creating a beautiful, textured fabric that is breathable and drapes perfectly.

                    Art. No.: 1307417003

                    Net Quantity:
                    1 N
                    Common generic name:
                    Shirt
                    Description:
                    Light blue, Solid colour
                    Size:
                    Sleeve: Length: 63.5 cm (size L/L(EUR L))
                    Shoulder: Width: 56.6 cm (size L/L(EUR L))
                    Back: Length: 77.5 cm (size L/L(EUR L))
                    XS: Width: 1.09 m, Length: 71 cm
                    S: Width: 1.17 m, Length: 73 cm
                    M: Width: 1.25 m, Length: 74 cm
                    L: Width: 1.33 m, Length: 76 cm
                    XL: Width: 1.41 m, Length: 77 cm
                    XXL: Width: 1.49 m, Length: 78 cm
                    Length:
                    Regular length
                    Sleeve Length:
                    Long sleeve
                    Fit:
                    Relaxed fit
                    Collar:
                    Classic collar
                    Nice to know:
                    H&M Premium Selection
                    DESC,
                'price' => 2299.00,
                'stock' => 50,
                'status' => 'active',
                'weight' => '230g',
                'dimensions' => '23x22x1.8 cm',
                'image_url' => 'https://image.hm.com/assets/hm/2e/75/2e75fbaa5033831c41e10a7caab3ed2215f899c6.jpg?imwidth=2160',
                'extra_image_urls' => [
                    'https://image.hm.com/assets/hm/c3/88/c388a6e9f9f24578ba7093436e25c5e17ce5cec5.jpg?imwidth=2160',
                    'https://image.hm.com/assets/hm/d5/ae/d5ae7eb2d1739b6afa37b5cf6aecb86ade5eea56.jpg?imwidth=2160',
                    'https://image.hm.com/assets/hm/18/dd/18dd390294f1808b0b6c822bc2591f2a03920099.jpg?imwidth=2160',
                ],
            ],
            [
                'name' => "Men's 511 Navy Blue Slim Fit Mid Rise Jeans",
                'sku' => 'LEV-511-DM-002',
                'category_name' => 'Jeans & Trousers',
                'manufacturer_name' => 'Levi\'s',
                'short_description' => 'Classic Levi\'s slim fit denim jeans. 99%Cotton, 1%Elastane',
                'description' => <<<'DESC'
                    A modern slim with room to move, the 511 slim-fit Stretch Jeans are a classic, you can wear with anything. Complete your casual wardrobe with a splash of style with these 511 slim-fit jeans from Levi's. Cut in a cotton fabric with the right amount of stretch, these navy jeans in solid pattern can be paired with most things to create a super cool outfit.

                    1. These jeans sit low waist with a slim-fit from hip to ankle.
                    2. It is woven, with a hint of stretch, to deliver maximum comfort.
                    3. Cut close to the body, the 511 Slim is a great alternative to the skinny jeans.
                    DESC,
                'price' => 3299.00,
                'stock' => 80,
                'status' => 'active',
                'weight' => '600g',
                'dimensions' => '35x25x4 cm',
                'image_url' => 'https://levi.in/cdn/shop/files/182981623_01_Styleshot.jpg?v=1767769907',
                'extra_image_urls' => [
                    'https://levi.in/cdn/shop/files/182981623_02_Front.jpg?v=1767769907',
                    'https://levi.in/cdn/shop/files/182981623_04_Detail.jpg?v=1767769907',
                ],
            ],
            [
                'name' => 'Nike Air Max 95 Big Bubble SE',
                'sku' => 'NKE-AM-RUN-003',
                'category_name' => 'Men\'s Footwear',
                'manufacturer_name' => 'Nike',
                'short_description' => <<<'DESC'
                    The Air Max 95 originally takes inspiration from the human anatomy and '90s athletics aesthetics, leading to its distinctive wavy design. This version pairs mixed materials and visible cushioning for a layered look with unbelievable comfort.
                    Colour Shown: Multi-Colour|White|Black
                    Style: IU2254-900
                    DESC,
                'description' => <<<'DESC'
                    The Air Max 95 originally takes inspiration from the human anatomy and '90s athletics aesthetics, leading to its distinctive wavy design. This version pairs mixed materials and visible cushioning for a layered look with unbelievable comfort.
                    Benefits
                    Textile and leather create a layered look built to last.
                    The visible Max Air cushioning provides lightweight cushioning.
                    Flex grooves in the midsole/outsole let you move freely.
                    Net Quantity: 1 Pair of Shoes
                    DESC,
                'price' => 15995.00,
                'stock' => 35,
                'status' => 'active',
                'weight' => '800g',
                'dimensions' => '40x30x12 cm',
                'image_url' => 'https://adn-static1.nykaa.com/nykdesignstudio-images/pub/media/catalog/product/a/d/ad22b92Nike-IU2254-900_1.jpg?rnd=20200526195200&tr=w-1080',
                'extra_image_urls' => [
                    'https://adn-static1.nykaa.com/nykdesignstudio-images/pub/media/catalog/product/a/d/ad22b92Nike-IU2254-900_2.jpg?rnd=20200526195200&tr=w-1080',
                    'https://adn-static1.nykaa.com/nykdesignstudio-images/pub/media/catalog/product/a/d/ad22b92Nike-IU2254-900_4.jpg?rnd=20200526195200&tr=w-1080',
                    'https://adn-static1.nykaa.com/nykdesignstudio-images/pub/media/catalog/product/a/d/ad22b92Nike-IU2254-900_5.jpg?rnd=20200526195200&tr=w-1080o',
                    'https://adn-static1.nykaa.com/nykdesignstudio-images/pub/media/catalog/product/a/d/ad22b92Nike-IU2254-900_7.jpg?rnd=20200526195200&tr=w-1080',
                    'https://adn-static1.nykaa.com/nykdesignstudio-images/pub/media/catalog/product/a/d/ad22b92Nike-IU2254-900_8.jpg?rnd=20200526195200&tr=w-1080',
                ],
            ],
            [
                'name' => 'Adidas Essentials Fleece Hoodie',
                'sku' => 'ADI-FL-HD-004',
                'category_name' => 'Hoodies & Sweatshirts',
                'manufacturer_name' => 'Adidas',
                'short_description' => <<<'DESC'
                    An adidas hoodie with a drawcord-adjustable hood.
                    Welcome stormy weather. Slip on this hoodie for fleecy warmth that keeps you comfortable when the sun slides behind the clouds. Tuck your hands into the kangaroo pocket for a quick warmup.
                    DESC,
                'description' => <<<'DESC'
                    An adidas hoodie with a drawcord-adjustable hood.
                    Welcome stormy weather. Slip on this hoodie for fleecy warmth that keeps you comfortable when the sun slides behind the clouds. Tuck your hands into the kangaroo pocket for a quick warmup.
                    Regular fit
                    Drawcord-adjustable hood
                    70% cotton, 30% polyester (recycled)
                    Kangaroo pocket
                    Ribbed cuffs and hem
                    Fleece
                    Colour: Medium Grey Heather / Black
                    Product code: H12213
                    DESC,
                'price' => 1999.00,
                'stock' => 45,
                'status' => 'active',
                'weight' => '450g',
                'dimensions' => '32x22x5 cm',
                'image_url' => 'https://assets.adidas.com/images/h_2000,f_auto,q_auto,fl_lossy,c_fill,g_auto/203e1e324f674294b274ad1900cada16_9366/Essentials_Fleece_Hoodie_Grey_H12213_01_laydown.jpg',
                'extra_image_urls' => [
                    'https://assets.adidas.com/images/c_crop,f_auto,fl_lossy,g_north,h_840,q_auto,y_40/h_2000/203e1e324f674294b274ad1900cada16_9366/Essentials_Fleece_Hoodie_Grey_H12213_01_laydown.jpg',
                ],
            ],
            [
                'name' => 'ZARA FLOWING WRAP DRESS',
                'sku' => 'ZAR-SAT-DR-005',
                'category_name' => 'Dresses',
                'manufacturer_name' => 'Zara',
                'short_description' => 'Zara elegant evening wrap dress.',
                'description' => <<<'DESC'
                    Long dress in a flowing fabric. Features a v-neckline with wide crossover straps at the front and a flared hem.
                    Composition: 96% polyester, 4% elastane
                    DESC,
                'price' => 4599.00,
                'stock' => 25,
                'status' => 'active',
                'weight' => '350g',
                'dimensions' => '30x20x3 cm',
                'image_url' => 'https://static.zara.net/assets/public/35b1/eba2/6dc441438a6a/1a7671354666/05039343500-p/05039343500-p.jpg?ts=1776852594010&w=1024',
                'extra_image_urls' => [
                    'https://static.zara.net/assets/public/701c/295e/95c2450688e4/c2505112da2a/05039343500-a1/05039343500-a1.jpg?ts=1776852593829&w=1126',
                    'https://static.zara.net/assets/public/cd2c/d841/f1624e2c8c2a/f57c494aa0bb/05039343500-a2/05039343500-a2.jpg?ts=1776852593884&w=750',
                ],
            ],
            [
                'name' => 'Fabindia Teal Cotton Hand Block Printed Long Kurta',
                'sku' => 'FAB-SLK-AN-006',
                'category_name' => 'Salwar Kameez',
                'manufacturer_name' => 'Fabindia',
                'short_description' => 'Fabindia Teal Cotton Hand Block Printed Long Kurta',
                'description' => <<<'DESC'
                    Material compositionCotton
                    Sleeve type3Q Sleeves
                    LengthCalf Length
                    Neck styleRound Neck
                    PatternPrinted
                    StyleKurta
                    Country of OriginIndia
                    About this item
                    Elegant teal cotton kurta with vibrant printed design for a stylish everyday look
                    Comfortable round neck and 3/4 sleeves perfect for versatile occasions
                    Regular fit ensures ease of movement and all-day comfort
                    Crafted from breathable cotton fabric for a cool, fresh feel
                    Ideal for casual outings, workwear, or festive gatherings
                    Durable stitching and High-quality print for long-lasting wear
                    DESC,
                'price' => 2599.00,
                'stock' => 20,
                'status' => 'active',
                'weight' => '500g',
                'dimensions' => '38x28x5 cm',
                'image_url' => 'https://m.media-amazon.com/images/I/61e9DJdLFcL._SX569_.jpg',
                'extra_image_urls' => [
                    'https://m.media-amazon.com/images/I/61RvAPCNdBL._SX569_.jpg',
                    'https://m.media-amazon.com/images/I/71X5NsZdvhL._SX569_.jpg',
                    'https://m.media-amazon.com/images/I/71mn22v7XiL._SX569_.jpg',
                ],
            ],
            [
                'name' => 'Men White Slim Fit Solid Full Sleeves Casual Shirt',
                'sku' => 'LP-OXF-SH-007',
                'category_name' => 'Shirts',
                'manufacturer_name' => 'Louis Philippe',
                'short_description' => 'Radiating effortless style, this white slim-fit shirt by Louis Philippe Sport is a wardrobe essential for the modern man. Skillfully made from premium cotton, it promises all-day comfort and breathability, making it ideal for casual outings. The solid pattern adds a touch of sophistication, effortlessly transitioning from day to evening wear. Infuse confidence in your look, perfectly suited for both leisurely weekends and social gatherings. A smart choice for the discerning gentleman.',
                'description' => <<<'DESC'
                    Radiating effortless style, this white slim-fit shirt by Louis Philippe Sport is a wardrobe essential for the modern man. Skillfully made from premium cotton, it promises all-day comfort and breathability, making it ideal for casual outings. The solid pattern adds a touch of sophistication, effortlessly transitioning from day to evening wear. Infuse confidence in your look, perfectly suited for both leisurely weekends and social gatherings. A smart choice for the discerning gentleman.
                    Material:
                    100% Cotton
                    Fit:
                    Slim Fit
                    StyleCode:
                    LYSFCSLBL79245
                    Brand:
                    Louis Philippe
                    Collar:
                    Button-down Collar
                    Color:
                    White
                    Cuffs:
                    Regular Cuff
                    Occasion:
                    Casual
                    Pattern:
                    Solid
                    Sleeves:
                    Full Sleeves
                    Subbrand:
                    Louis Philippe Sport
                    ProductType:
                    Shirt
                    Collection:
                    LY True Casuals
                    DESC,
                'price' => 2299.00,
                'stock' => 60,
                'status' => 'active',
                'weight' => '300g',
                'dimensions' => '30x20x2 cm',
                'image_url' => 'https://imagescdn.louisphilippe.com/img/app/product/9/948805-12207888.jpg?auto=format&w=390',
                'extra_image_urls' => [
                    'https://imagescdn.louisphilippe.com/img/app/product/9/948805-12207888.jpg?auto=format&w=390',
                ],
            ],
            [
                'name' => "PUMA Train All Day Men's Breathable Training Tee",
                'sku' => 'PUM-DF-TE-008',
                'category_name' => 'Activewear',
                'manufacturer_name' => 'Puma',
                'short_description' => 'Designed for active comfort, this training tee is built with breathable fabric and moisture-wicking technology to keep you dry and focused. Its casual yet performance-ready design makes it perfect for both workouts and everyday wear.',
                'description' => <<<'DESC'
                    Designed for active comfort, this training tee is built with breathable fabric and moisture-wicking technology to keep you dry and focused. Its casual yet performance-ready design makes it perfect for both workouts and everyday wear.

                    FEATURES & BENEFITS
                    Breathable Comfort: Enhances airflow to keep you cool and dry
                    Comfort Fit: Regular fit for ease of movement and lasting comfort
                    DETAILS
                    Fabrics Type: Knitted
                    Fit: Regular
                    Fastener: Pull-On
                    Main Material: Recycled Polyester
                    MATERIAL INFORMATION
                    Shell: 100% Polyester
                    CARE INSTRUCTIONS
                    Use detergent for colours
                    Exclusive of Decoration
                    Wash with similar colours
                    Wash and iron inside out
                    Do not iron print
                    DESC,
                'price' => 1168.00,
                'stock' => 100,
                'status' => 'active',
                'weight' => '150g',
                'dimensions' => '25x18x1 cm',
                'image_url' => "https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_750,h_750/global/528856/01/mod01/fnd/IND/fmt/png/Train-All-Day-Men's-Breathable-Training-Tee",
                'extra_image_urls' => [
                    "https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_750,h_750/global/528856/01/mod03/fnd/IND/fmt/png/Train-All-Day-Men's-Breathable-Training-Tee",
                    "https://images.puma.com/image/upload/f_auto,q_auto,b_rgb:fafafa,w_750,h_750/global/528856/01/mod04/fnd/IND/fmt/png/Train-All-Day-Men's-Breathable-Training-Tee",
                ],
            ],
            [
                'name' => "Men's Graphic Print Regular Fit Overdyed T-Shirt",
                'sku' => 'LEV-GRP-TE-009',
                'category_name' => 'T-Shirts & Polos',
                'manufacturer_name' => 'Levi\'s',
                'short_description' => 'Classic cotton tee with signature graphic.',
                'description' => <<<'DESC'
                    Made from good quality cotton, this easygoing Levis pink t-shirt is perfect to add to your casual collection. Features a crew neck and a graphic pattern.

                    1. The regular fit provides comfort all day long
                    2. Layer it with our shirts or wear it with our pair of 511 jeans
                    DESC,
                'price' => 999.00,
                'stock' => 120,
                'status' => 'active',
                'weight' => '180g',
                'dimensions' => '25x18x1 cm',
                'image_url' => 'https://levi.in/cdn/shop/files/A79730274_01_Styleshot.jpg?v=1772784398',
                'extra_image_urls' => [
                    'https://levi.in/cdn/shop/files/A79730274_02_Front.jpg?v=1772784415',
                    'https://levi.in/cdn/shop/files/A79730274_04_Detail.jpg?v=1772784453',
                ],
            ],
            [
                'name' => 'W for Woman Navy Blue Embroidered Straight Kurta',
                'sku' => 'WFW-COT-KU-010',
                'category_name' => 'Salwar Kameez',
                'manufacturer_name' => 'W for Woman',
                'short_description' => 'Printed daily-wear straight cotton kurti.',
                'description' => <<<'DESC'
                    Colour
                    Navy Blue
                    Fabric Content
                    80% Viscose 20% Man Made Fiber
                    Fabric Detail
                    Viscose
                    Neck Type
                    Keyhole Neck
                    Closure
                    Slip on
                    Sleeve
                    Three-Quarter Sleeves
                    Sleeve Styling
                    Regular Sleeves
                    Fit
                    Regular Fit
                    Ethnicity
                    Ethnic
                    Ornamentation
                    Sequins
                    Colour Details
                    Navy Blue
                    Shape
                    A Line
                    Hemline
                    Straight
                    Occasion
                    Casual
                    Length
                    Calf Length
                    Pattern
                    Embroidered
                    Fabric Family
                    Viscose
                    Care
                    Hand Wash
                    DESC,
                'price' => 1499.00,
                'stock' => 70,
                'status' => 'active',
                'weight' => '200g',
                'dimensions' => '30x22x1 cm',
                'image_url' => 'https://www.wforwoman.com/cdn/shop/files/W11970-222877_1_bfa0f9dc-2ac2-4f0b-98ff-274ff96fbc51.jpg?v=1769506857&width=1100',
                'extra_image_urls' => [
                    'https://www.wforwoman.com/cdn/shop/files/W11970-222877_3_0ef8c9bc-401d-4ac3-b868-03254b70c622.jpg?v=1769506873&width=1100',
                    'https://www.wforwoman.com/cdn/shop/files/W11970-222877_6_96dd5152-743c-4100-916e-05d8fd539bf4.jpg?v=1769506900&width=1100'
                ],
            ],
            [
                'name' => 'ZARA RETRO SQUARE SUNGLASSES',
                'sku' => 'ZAR-RTR-SG-011',
                'category_name' => 'Sunglasses',
                'manufacturer_name' => 'Zara',
                'short_description' => 'Square sunglasses with an acetate frame. Polarised lenses. Case included.',
                'description' => <<<'DESC'
                    Square sunglasses with an acetate frame. Polarised lenses. Case included.

                    UV/UVA 400 Protection. Category 3.
                    OUTER SHELL
                    FRAME
                    100% acetate
                    LENSES
                    100% polymethyl methacrylate
                    DESC,
                'price' => 2999.00,
                'stock' => 40,
                'status' => 'active',
                'weight' => '100g',
                'dimensions' => '18x8x6 cm',
                'image_url' => 'https://static.zara.net/assets/public/49ac/d4a1/75ec4cf5a9d4/79fe0d85cdca/03147001700-p/03147001700-p.jpg?ts=1776699493975&w=1024',
                'extra_image_urls' => [
                    'https://static.zara.net/assets/public/d7f2/203f/1d7843f0a879/aeb28f9ea0b6/03147001700-e1/03147001700-e1.jpg?ts=1776766381609&w=1126',
                    'https://static.zara.net/assets/public/577b/df64/85094a9b8af7/57830b14d802/03147001700-e2/03147001700-e2.jpg?ts=1776766381317&w=750'
                ],
            ],
            [
                'name' => 'ZARA CITY CROSSBODY BAG',
                'sku' => 'ZAR-MN-HB-012',
                'category_name' => 'Bags & Handbags',
                'manufacturer_name' => 'Zara',
                'short_description' => 'Minimalist leather shopper bag.',
                'description' => 'City crossbody bag. Topstitching detail on the top. Side zips with pull tabs. Double handle and a crossbody strap. Removable inner compartment with zip. Magnetic clasp closure.',
                'price' => 4999.00,
                'stock' => 15,
                'status' => 'active',
                'weight' => '700g',
                'dimensions' => '40x35x15 cm',
                'image_url' => 'https://static.zara.net/assets/public/3aa8/cea4/d52c4196b9c0/749dc497e793/16411710719-022-p/16411710719-022-p.jpg?ts=1781713672259&w=1024',
                'extra_image_urls' => [
                    'https://static.zara.net/assets/public/d1de/96ea/232f4bb49265/063692ecc503/16411710719-022-a1/16411710719-022-a1.jpg?ts=1781713666407&w=1126',
                    'https://static.zara.net/assets/public/56bb/1223/507241d3bbd1/e6650d65d972/16411710719-e1/16411710719-e1.jpg?ts=1770224343530&w=750',
                    'https://static.zara.net/assets/public/ac46/d258/228c4a9a8a64/ff1a9d295faa/16411710719-e2/16411710719-e2.jpg?ts=1770224343042&w=750'
                ],
            ],
            [
                'name' => 'Benetton Women Round Neck Colorblock Sweater',
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
                'image_url' => 'https://in.benetton.com/cdn/shop/files/Benetton_26P_26P1094E1Z19G_901_5CBenetton_26P_26P1094E1Z19G_901_F_960x_crop_center.jpg?v=1768138851',
                'extra_image_urls' => [
                    'https://in.benetton.com/cdn/shop/files/Benetton_26P_26P1094E1Z19G_901_5CBenetton_26P_26P1094E1Z19G_901_FS.jpg?v=1768138851',
                    'https://in.benetton.com/cdn/shop/files/Benetton_26P_26P1094E1Z19G_901_5CBenetton_26P_26P1094E1Z19G_901_S_640x_crop_center.jpg?v=1768138851'
                ],
            ],
            [
                'name' => 'FABINDIA Red Cotton Hand Block Printed Sari',
                'sku' => 'FAB-HND-SR-014',
                'category_name' => 'Sarees & Lehengas',
                'manufacturer_name' => 'Fabindia',
                'short_description' => 'Red Cotton Hand Block Printed Sari',
                'description' => <<<'DESC'
                    Breathable ease shapes this cotton sari, designed for everyday wear with a natural drape that allows steady movement and lasting comfort. Floral hand-block printed motifs create a soft rhythm across the surface without crowding the design. The border provides subtle structure. It is well suited to extended use. Pair it with a relaxed blouse and minimal accessories for a look that feels grounded and effortless.
                    Craft Details

                    One of the most widespread techniques used for fabric decoration, hand block printing has been practiced by extremely skilful artisans for centuries in India. This remarkable traditional craft of fabric printing involves the use of engraved wooden blocks, with separate blocks for each colour pressed against fabric with great precision to create striking patterns. Examples of this very fine block printing go back centuries.
                    DESC,
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
                'name' => 'Raymond Men Black Regular Fit Solid Formal Blazer',
                'sku' => 'RAY-PRM-BL-015',
                'category_name' => 'Jackets & Coats',
                'manufacturer_name' => 'Raymond',
                'short_description' => 'Premium wool-blend slim fit blazer.',
                'description' => <<<'DESC'
                    Fit: Regular Fit

                    Color: Black

                    Fabric: Polyestery 65%/Rayon 35%

                    Occasion: Formal Wear

                    Sleeve:
                    DESC,
                'price' => 6999.00,
                'stock' => 18,
                'status' => 'active',
                'weight' => '1100g',
                'dimensions' => '45x38x8 cm',
                'image_url' => 'https://myraymond.com/cdn/shop/files/RIJI00122-K8_20_1.jpg?v=1762242606',
                'extra_image_urls' => [
                    'https://myraymond.com/cdn/shop/files/RIJI00122-K8_20_2.jpg?v=1762242605',
                    'https://myraymond.com/cdn/shop/files/RIJI00122-K8_20_3.jpg?v=1762242606'
                ],
            ],
        ];

        // Ensure target directory exists in public disk
        if (! Storage::disk('public')->exists('products')) {
            Storage::disk('public')->makeDirectory('products');
        }

        foreach ($products as $p) {
            $catId = Category::where('name', $p['category_name'])->value('id');
            $manId = Manufacturer::where('name', $p['manufacturer_name'])->value('id');

            // Download default image file from URL and save to products folder
            $imagePath = null;
            if (! empty($p['image_url'])) {
                try {
                    $context = stream_context_create([
                        'http' => [
                            'timeout' => 5, // 5 seconds timeout
                            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                        ],
                    ]);
                    $imageContent = @file_get_contents($p['image_url'], false, $context);
                    if ($imageContent !== false) {
                        $filename = 'products/'.$p['sku'].'.jpg';
                        Storage::disk('public')->put($filename, $imageContent);
                        $imagePath = $filename;
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Could not download main image for {$p['sku']}: ".$e->getMessage());
                }
            }

            $extraImageUrls = $p['extra_image_urls'] ?? [];

            unset($p['category_name'], $p['manufacturer_name'], $p['image_url'], $p['extra_image_urls']);

            $productModel = Product::query()->updateOrCreate(
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
                        ],
                    ]);
                    $imageContent = @file_get_contents($url, false, $context);
                    if ($imageContent !== false) {
                        $filename = 'products/'.$p['sku'].'_extra_'.($index + 1).'.jpg';
                        Storage::disk('public')->put($filename, $imageContent);

                        $productModel->images()->firstOrCreate([
                            'image_path' => $filename,
                            'is_default' => false,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->command->warn("Could not download extra image for {$p['sku']}: ".$e->getMessage());
                }
            }
        }

        $this->command->info('✅ 15 Products successfully seeded with default and extra mock images.');
    }
}
