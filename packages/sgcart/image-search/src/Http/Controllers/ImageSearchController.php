<?php

namespace SGCart\ImageSearch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreController;
use App\Models\Category;
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
            $analysis = $analyzer->analyze($image);

            $objectType = $analysis['object_type'] ?? null;
            if (is_string($objectType)) {
                $objectType = trim($objectType);
                if (in_array(strtolower($objectType), ['null', 'none', 'undefined', ''])) {
                    $objectType = null;
                }
            } else {
                $objectType = null;
            }

            $gender = $analysis['gender'] ?? null;
            if (is_string($gender)) {
                $gender = strtolower(trim($gender));
                if (in_array($gender, ['null', 'none', 'undefined', ''])) {
                    $gender = null;
                }
            } else {
                $gender = null;
            }

            $color = $analysis['color'] ?? null;
            if (is_string($color)) {
                $color = trim($color);
                if (in_array(strtolower($color), ['null', 'none', 'undefined', ''])) {
                    $color = null;
                }
            } else {
                $color = null;
            }

            $keywords = $analysis['keywords'] ?? [];

            Log::info('Image analysis result: '.json_encode($analysis));

            // If keywords is empty and object_type is null then show no products found
            if (empty($keywords) && is_null($objectType)) {
                $allCategories = collect(StoreController::getProducts())->pluck('cat')->unique()->values()->toArray();
                if (empty($allCategories)) {
                    $allCategories = ['Women', 'Men', 'Accessories', 'Footwear', 'Beauty'];
                }
                return view('store.shop', [
                    'products' => collect(),
                    'allCategories' => $allCategories,
                    'selectedCategories' => (array) $request->input('category', []),
                    'selectedPriceMax' => $request->input('price_max', 10000),
                    'selectedSort' => $request->input('sort', 'default'),
                    'searchQuery' => '',
                ]);
            }

            // Start the query builder for Product
            $productQuery = Product::query();

            // 1. Gather all gender categories if gender is specified, and filter the products by them
            if ($gender) {
                $genderTerms = [];
                if ($gender === 'male') {
                    $genderTerms = ['men', 'male', 'boy', 'gent'];
                } elseif ($gender === 'female') {
                    $genderTerms = ['women', 'woman', 'girl', 'lady', 'ladies', 'female'];
                } elseif ($gender === 'kid') {
                    $genderTerms = ['kid', 'boy', 'girl', 'child', 'infant', 'toddler', 'baby'];
                }

                $genderCategoryIds = Category::where(function ($q) use ($genderTerms) {
                    $q->where(function ($sq) use ($genderTerms) {
                        foreach ($genderTerms as $term) {
                            $sq->orWhere('name', 'like', '%'.$term.'%');
                        }
                    })->orWhereHas('parent', function ($pq) use ($genderTerms) {
                        $pq->where(function ($sq) use ($genderTerms) {
                            foreach ($genderTerms as $term) {
                                $sq->orWhere('name', 'like', '%'.$term.'%');
                            }
                        });
                    });
                })->pluck('id');

                if ($genderCategoryIds->isNotEmpty()) {
                    $productQuery->whereIn('category_id', $genderCategoryIds);
                } else {
                    $productQuery->whereRaw('1 = 0');
                }
            }

            // 2. Object type search: must match product title, search terms, or categories
            if ($objectType) {
                // Find categories matching objectType
                $categoryIds = Category::query()->where('name', 'like', '%'.$objectType.'%')->pluck('id');

                // Find products matching objectType in search terms
                $cleanObjectType = strtolower(trim($objectType));
                $objectTypeProductIds = SearchTerm::where(function ($q) use ($cleanObjectType) {
                    $q->where('term', $cleanObjectType)
                        ->orWhere('term', 'like', '%'.$cleanObjectType.'%');
                })
                    ->where('searchable_type', Product::class)
                    ->pluck('searchable_id');

                $productQuery->where(function ($q) use ($objectType, $categoryIds, $objectTypeProductIds) {
                    $q->where('name', 'like', '%'.$objectType.'%')
                        ->orWhereIn('category_id', $categoryIds)
                        ->orWhereIn('id', $objectTypeProductIds);
                });
            }

            // 3. Color search (mandatory): must match product title or search terms
            if ($color) {
                $cleanColor = strtolower(trim($color));
                $colorProductIds = SearchTerm::where(function ($q) use ($cleanColor) {
                    $q->where('term', $cleanColor)
                        ->orWhere('term', 'like', '%'.$cleanColor.'%');
                })
                    ->where('searchable_type', Product::class)
                    ->pluck('searchable_id');

                $productQuery->where(function ($q) use ($cleanColor, $colorProductIds) {
                    $q->where('name', 'like', '%'.$cleanColor.'%')
                        ->orWhereIn('id', $colorProductIds);
                });
            }

            // 4. Other keywords just make the search accurate (min match 1, optional)
            $cleanColorVal = $color ? strtolower(trim($color)) : null;
            $cleanObjectTypeVal = $objectType ? strtolower(trim($objectType)) : null;

            $otherKeywords = [];
            foreach ($keywords as $kw) {
                $kwLower = strtolower(trim($kw));
                // Split multi-word keywords into individual words to increase matches
                $words = preg_split('/\s+/', preg_replace('/[^\w\s]/u', ' ', $kwLower), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($words as $word) {
                    if ($cleanColorVal && ($word === $cleanColorVal || str_contains($cleanColorVal, $word) || str_contains($word, $cleanColorVal))) {
                        continue;
                    }
                    if ($cleanObjectTypeVal && ($word === $cleanObjectTypeVal || str_contains($cleanObjectTypeVal, $word) || str_contains($word, $cleanObjectTypeVal))) {
                        continue;
                    }
                    $otherKeywords[] = $word;
                }
            }
            $otherKeywords = array_values(array_unique(array_filter($otherKeywords)));

            $minMatches = 1;
            if (count($otherKeywords) >= $minMatches) {
                $keywordProductIds = SearchTerm::whereIn('term', $otherKeywords)
                    ->where('searchable_type', Product::class)
                    ->select('searchable_id')
                    ->groupBy('searchable_id')
                    ->havingRaw('COUNT(DISTINCT term) >= ?', [$minMatches])
                    ->pluck('searchable_id');

                if ($keywordProductIds->isNotEmpty()) {
                    $productQuery->whereIn('id', $keywordProductIds);
                }
            }

            // Fetch matched product IDs from the query builder
            $matchedIds = $productQuery->pluck('id');

            // Get all products using the existing StoreController logic
            $products = collect(StoreController::getProducts());

            // Filter products to only include those matched by our query builder
            $products = $products->filter(fn ($p) => $matchedIds->contains($p['id']));

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
            Log::error('Image search error: '.$e->getMessage(), ['error' => $e]);

            return redirect()->route('store.shop')->with('error', 'Image search failed');
        }
    }
}
