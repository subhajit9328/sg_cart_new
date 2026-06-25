<?php

namespace SGCart\ImageSearch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SGCart\ImageSearch\ImageAnalyzer;
use SGCart\ImageSearch\Models\SearchTerm;
use SGCart\ImageSearch\MultiObjectDetector;

class ImageSearchController extends Controller
{
    /**
     * Check if the uploaded image contains multiple products/objects.
     */
    public function detectMultiple(Request $request)
    {
        $request->validate([
            'image' => 'required',
        ]);

        $image = $request->file('image') ?: $request->input('image');

        try {
            /** @var MultiObjectDetector $detector */
            $detector = app('multi-object-detector');
            $isMultiple = $detector->detect($image);

            return response()->json([
                'success' => true,
                'multiple' => $isMultiple,
            ]);
        } catch (\Exception $e) {
            Log::error('Image search error: '.$e->getMessage(), ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'multiple' => false,
            ], 500);
        }
    }

    /**
     * Search products using an uploaded image.
     */
    public function search(Request $request)
    {
        $image = $request->file('image') ?: $request->input('image');

        if (! $image) {
            return redirect()->route('store.shop')->with('error', 'Please provide an image to search.');
        }

        try {
            /** @var ImageAnalyzer $analyzer */
            $analyzer = app('image-analyzer');
            $terms = $analyzer->analyze($image);
//            Log::info('Image search terms: '.implode(', ', $terms));
            // Find product IDs matching the analyzed terms (must match at least 2 terms if available)
            $minMatches = min(2, count($terms));
            $productIds = collect();

            if ($minMatches > 0) {
                $productIds = SearchTerm::whereIn('term', $terms)
                    ->where('searchable_type', Product::class)
                    ->select('searchable_id')
                    ->groupBy('searchable_id')
                    ->havingRaw('COUNT(DISTINCT term) >= ?', [$minMatches])
                    ->pluck('searchable_id');
            }
            $productIds = $productIds->unique();

            // Get all products using the existing StoreController logic
            $products = collect(StoreController::getProducts());

            // Filter products that match the search terms
            $products = $products->filter(fn ($p) => in_array($p['id'], $productIds->toArray()));

            // Apply categories and price filters if present in request (for sidebar filters on search results)
            if ($request->filled('category')) {
                $categories = (array) $request->input('category');
                $products = $products->filter(fn ($p) => in_array($p['cat'], $categories));
            }

            if ($request->filled('price_max')) {
                $maxPrice = (float) $request->input('price_max');
                $products = $products->filter(fn ($p) => $p['price'] <= $maxPrice);
            }

            $sort = $request->input('sort', 'default');
            if ($sort === 'price_asc') {
                $products = $products->sortBy('price');
            } elseif ($sort === 'price_desc') {
                $products = $products->sortByDesc('price');
            }

            $allCategories = collect(StoreController::getProducts())->pluck('cat')->unique()->values()->toArray();
            if (empty($allCategories)) {
                $allCategories = ['Women', 'Men', 'Accessories', 'Footwear', 'Beauty'];
            }

            // Return storefront shop view. Set searchQuery to empty string so search input field remains empty.
            return view('store.shop', [
                'products' => $products,
                'allCategories' => $allCategories,
                'selectedCategories' => (array) $request->input('category', []),
                'selectedPriceMax' => $request->input('price_max', 10000),
                'selectedSort' => $sort,
                'searchQuery' => '',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('store.shop')->with('error', 'Image search failed: '.$e->getMessage());
        }
    }
}
