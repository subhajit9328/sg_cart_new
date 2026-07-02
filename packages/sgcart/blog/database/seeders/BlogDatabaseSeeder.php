<?php

namespace SGCart\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use SGCart\Blog\Models\BlogCategory;
use SGCart\Blog\Models\BlogPost;
use App\Models\User;

class BlogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch default admin user
        $admin = User::where('email', 'admin@sgcart.com')->first() ?? User::first();
        $adminId = $admin ? $admin->id : null;

        // 1. Create E-commerce Focused Categories
        $categories = [
            [
                'name' => 'Guides & Styling',
                'slug' => 'guides-styling',
                'description' => 'Helpful capsule wardrobe tips, seasonal outfit inspirations, and style advice from our team.',
                'is_active' => true
            ],
            [
                'name' => 'Collections Spotlight',
                'slug' => 'collections-spotlight',
                'description' => 'Deep dives into our fabrics, eco-friendly certifications, and premium brand partnerships.',
                'is_active' => true
            ],
            [
                'name' => 'Smart Shopping Hacks',
                'slug' => 'smart-shopping-hacks',
                'description' => 'Insiders secrets to coupon matching, checkout shortcuts, wishlist tracking, and saving.',
                'is_active' => true
            ]
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = BlogCategory::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => $cat['is_active']
                ]
            );
        }

        // 2. Create 6 Rich E-commerce friendly blog posts with gorgeous Unsplash images
        $posts = [
            [
                'title' => 'Top 10 E-Commerce Shopping Tips for 2026',
                'slug' => 'top-10-ecommerce-shopping-tips-for-2026',
                'summary' => 'Smart shopping strategies to help you secure the best deals, verify authentic products, and save money online.',
                'content' => "<p>Online shopping is faster and more convenient than ever, but getting the absolute best value requires strategy. In this guide, we break down our top ten insider tips to elevate your shopping experience.</p>

<h2>1. Plan Around Major Sales Cycles</h2>
<p>Most online retail platforms have predictable seasonal campaigns. Look out for Mid-Year sales, Black Friday, and annual clearance events. Placing items in your wishlist ahead of time helps you track real price drops.</p>

<h2>2. Check Product Verification Badges</h2>
<p>Ensure the seller or product is certified. Authentic marketplaces use trust badges to indicate approved vendors.</p>

<h2>3. Compare Shipping Costs and Thresholds</h2>
<p>Sometimes a slightly higher-priced item with free shipping is cheaper overall than a low-priced item with high shipping fees. Always check if you qualify for a free shipping subtotal threshold (e.g., free shipping on orders over ₹999).</p>

<h2>4. Utilize Available Coupon Codes</h2>
<p>Before checkout, always review active coupons. SGCart lets you apply discount codes directly in your cart drawer to save instantly on your purchases.</p>

<h2>5. Read Customer Reviews Carefully</h2>
<p>Filter reviews by rating and look for customer-submitted photos. These give you a realistic idea of size, color, and build quality.</p>",
                'featured_image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
                'category_index' => 2, // Smart Shopping Hacks
                'status' => 'published',
                'published_at' => now()->subDays(10)
            ],
            [
                'title' => 'SGCart Summer Sale 2026: What to Expect',
                'slug' => 'sgcart-summer-sale-2026-what-to-expect',
                'summary' => 'Get ready for our biggest sale of the season! Check out early deals, discounts, and exclusive offers.',
                'content' => "<p>The temperature is rising, and so are the savings! We are thrilled to announce the SGCart Summer Sale 2026. This year, we are offering unprecedented site-wide discounts, limited-edition bundles, and flash deals that you won't want to miss.</p>

<h2>Key Dates</h2>
<ul>
    <li><strong>Early Access (VIPs &amp; Subscribers)</strong>: July 10, 2026</li>
    <li><strong>General Public Sale</strong>: July 12 – July 18, 2026</li>
</ul>

<h2>What's on Sale?</h2>
<p>We are slashing prices across all our top categories:</p>
<ol>
    <li><strong>Fashion &amp; Apparel</strong>: Up to 50% off on summer wear.</li>
    <li><strong>Electronics &amp; Accessories</strong>: Up to 30% off on top brands.</li>
    <li><strong>Home &amp; Living</strong>: Flat 40% off on seasonal decor.</li>
</ol>

<h2>Flash Deals</h2>
<p>Every day at 12:00 PM and 6:00 PM, we will release high-demand items at up to 70% off for exactly one hour. Set your timers, add items to your cart, and check out quickly to secure yours!</p>",
                'featured_image' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=1200&q=80',
                'category_index' => 2, // Smart Shopping Hacks
                'status' => 'published',
                'published_at' => now()->subDays(8)
            ],
            [
                'title' => 'Spotlight: Behind the Scenes of Our Eco-Friendly Products',
                'slug' => 'spotlight-behind-the-scenes-of-our-eco-friendly-products',
                'summary' => 'Learn about our commitment to sustainability and how we source biodegradable and recycled materials.',
                'content' => "<p>At SGCart, we believe that style and convenience should not come at the expense of our planet. That's why we've partnered with local manufacturers to deliver a curated collection of eco-friendly and sustainably sourced products.</p>

<h2>Sourcing Organic Cotton &amp; Hemp</h2>
<p>Traditional apparel production utilizes vast amounts of water and synthetic chemicals. Our sustainable line is crafted from 100% organic cotton and hemp fibers, which require no pesticide treatments and consume 70% less water during farming.</p>

<h2>Recycled Packaging Materials</h2>
<p>Sustainability doesn't stop at the product itself. Every order from our eco-friendly collection is packaged using:</p>
<ul>
    <li>100% biodegradable mailers.</li>
    <li>Recycled cardboard boxes.</li>
    <li>Soy-based inks for printing.</li>
</ul>

<p>By making small choices in how we purchase and package, we can collectively make a massive impact. Browse our Sustainable Living collection today and join the movement!</p>",
                'featured_image' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?auto=format&fit=crop&w=1200&q=80',
                'category_index' => 1, // Collections Spotlight
                'status' => 'published',
                'published_at' => now()->subDays(6)
            ],
            [
                'title' => 'The Ultimate Wardrobe Capsule for 2026',
                'slug' => 'the-ultimate-wardrobe-capsule-for-2026',
                'summary' => 'Simplify your life and elevate your style with our curated 12-piece wardrobe layout designed for year-round styling.',
                'content' => "<p>Cluttered closets lead to decision fatigue. The capsule wardrobe is the perfect remedy, giving you infinite matching outfit possibilities from a small, high-quality, select number of essential garments.</p>

<h2>The Golden Rules of Wardrobe Capsuling</h2>
<p>Before investing in new pieces, look at your existing collection. Stick to neutral base layers, choose versatile cuts, and prioritize premium fabrics that stand the test of time.</p>

<h2>The 12 Core Essentials</h2>
<ul>
    <li><strong>3 Classic Tops</strong>: A crisp white button-down, a premium cotton black tee, and a neutral stripe shirt.</li>
    <li><strong>3 Versatile Bottoms</strong>: Straight-leg dark indigo denim, custom tailored chinos, and a flowing linen skirt.</li>
    <li><strong>3 Outerwear Layers</strong>: A light trench coat, an oversized wool cardigan, and a structured blazer.</li>
    <li><strong>3 Smart Footwear Styles</strong>: Clean white sneakers, leather loafers, and neutral ankle boots.</li>
</ul>

<p>By buying less but investing in quality, you save money, decrease textile waste, and look effortlessly stylish. SGCart provides custom matching suggestions on every product page to help you assemble your perfect capsule!</p>",
                'featured_image' => 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1200&q=80',
                'category_index' => 0, // Guides & Styling
                'status' => 'published',
                'published_at' => now()->subDays(4)
            ],
            [
                'title' => '5 Smart Hacks to Save Big on SGCart Checkout',
                'slug' => '5-smart-hacks-to-save-big-on-checkout',
                'summary' => 'Maximize your value with these expert cart strategies, loyalty incentives, and seasonal combinations.',
                'content' => "<p>We want you to secure the absolute best prices on SGCart. Beyond simple coupons, here are five insider secrets to unlock additional discounts on your next checkout.</p>

<h2>1. Leverage the Cart Drawer Bundle Bonus</h2>
<p>Adding matching sets from our 'Frequently Bought Together' widget at the bottom of products unlocks a hidden 10% bundle discount during sales seasons.</p>

<h2>2. Leave Items in Your Cart for Account Incentives</h2>
<p>If you are signed in, leaving items in your shopping cart for 24 hours often triggers a follow-up cart retention coupon sent directly to your registered email!</p>

<h2>3. Maximize Points at the SGCart Loyalty Center</h2>
<p>Every purchase, review, and social share awards you loyalty coins. Redeem these coins directly at checkout to offset your order subtotal.</p>

<h2>4. Combine Clearance Categories with Store Coupon Codes</h2>
<p>Clearance items are already discounted up to 60%. Unlike many platforms, SGCart allows users to stack valid discount coupons on top of clearance products, saving you double!</p>",
                'featured_image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=80',
                'category_index' => 2, // Smart Shopping Hacks
                'status' => 'published',
                'published_at' => now()->subDays(2)
            ],
            [
                'title' => 'Why Slow Fashion is the Future of Sustainable Apparel',
                'slug' => 'why-slow-fashion-is-the-future-of-sustainable-apparel',
                'summary' => 'An in-depth look at how slow-made garments protect the planet, respect artisans, and give you better outfits.',
                'content' => "<p>Fast fashion has exacted a steep cost on our environment and ethical labor standards. Slow fashion advocates for quality manufacturing, sustainable materials, and conscious, long-term apparel consumption.</p>

<h2>The Ecological Impact of Fast Retail</h2>
<p>Millions of tons of cheap synthetic clothing end up in landfills every year. By switching to natural fibers, organic sourcing, and timeless designs, slow fashion ensures your clothes last for years instead of weeks.</p>

<h2>Our Ethical Production Promise</h2>
<p>At SGCart, we choose to design timeless lines and partner with fair-wage artisan workshops. Each piece is crafted carefully, reducing raw textile waste and supporting local manufacturing families.</p>

<p>Sustainable fashion is a journey. Start by taking care of the clothes you own, repairing small tears, and choosing conscious retailers like SGCart for your wardrobe upgrades.</p>",
                'featured_image' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=1200&q=80',
                'category_index' => 1, // Collections Spotlight
                'status' => 'published',
                'published_at' => now()->subDay()
            ]
        ];

        $storeProducts = \App\Models\Product::where('status', 'active')->get();

        foreach ($posts as $post) {
            $createdPost = BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'summary' => $post['summary'],
                    'content' => $post['content'],
                    'featured_image' => $post['featured_image'],
                    'blog_category_id' => $createdCategories[$post['category_index']]->id,
                    'user_id' => $adminId,
                    'status' => $post['status'],
                    'published_at' => $post['published_at']
                ]
            );

            if ($storeProducts->isNotEmpty()) {
                $createdPost->products()->sync(
                    $storeProducts->random(min(3, $storeProducts->count()))->pluck('id')->toArray()
                );
            }
        }
    }
}
