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
                $parents = Category::parents()->active()->take(5)->orderBy('sort_order')->get();
                $sidebarCategoriesMap = [];
                foreach ($parents as $parent) {
                    $children = $parent->children()->active()->orderBy('sort_order')->get();
                    $sidebarCategoriesMap[$parent->name] = [
                        'name' => $parent->name,
                        'id' => $parent->id,
                        'children' => $children->map(fn($c) => ['name' => $c->name, 'id' => $c->id])->toArray()
                    ];
                }
                $sidebarCategories = array_values($sidebarCategoriesMap);

                $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
                    collect(),
                    0,
                    12,
                    1,
                    [
                        'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                        'query' => $request->query()
                    ]
                );

                return view('store.shop', [
                    'products' => $paginatedProducts,
                    'sidebarCategories' => $sidebarCategories,
                    'categoryCounts' => [],
                    'selectedCategories' => (array) $request->input('category', []),
                    'selectedSubCategories' => (array) $request->input('sub_category', []),
                    'selectedPriceMax' => $request->input('price_max', 100000),
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
            $baseProducts = collect(StoreController::getProducts());

            // Filter products to only include those matched by our query builder
            $baseProducts = $baseProducts->filter(fn ($p) => $matchedIds->contains($p['id']));

            // Apply Price filter next
            if ($request->filled('price_max')) {
                $maxPrice = (float) $request->input('price_max');
                $baseProducts = $baseProducts->filter(fn ($p) => $p['price'] <= $maxPrice);
            }

            // Build the hierarchical sidebar categories list based on search results
            $matchedCategoryIds = $baseProducts->pluck('category_id')->unique()->filter()->toArray();

            $categories = Category::with('parent')->whereIn('id', $matchedCategoryIds)->get();

            $sidebarCategoriesMap = [];
            foreach ($categories as $cat) {
                $topParent = $cat;
                while ($topParent->parent_id && $topParent->parent) {
                    $topParent = $topParent->parent;
                }

                $parentName = $topParent->name;
                $parentId = $topParent->id;

                if (!isset($sidebarCategoriesMap[$parentName])) {
                    $sidebarCategoriesMap[$parentName] = [
                        'name' => $parentName,
                        'id' => $parentId,
                        'children' => []
                    ];
                }

                if ($cat->id !== $parentId) {
                    $sidebarCategoriesMap[$parentName]['children'][$cat->name] = [
                        'name' => $cat->name,
                        'id' => $cat->id
                    ];
                }
            }

            foreach ($sidebarCategoriesMap as &$pCat) {
                $pCat['children'] = array_values($pCat['children']);
            }
            $sidebarCategories = array_values($sidebarCategoriesMap);

            // Calculate counts based on search and price filters (before category filter is applied)
            $categoryCounts = [];
            foreach ($baseProducts as $p) {
                $cat = $p['cat'];
                $subcat = $p['subcat'];

                if ($cat) {
                    $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;
                }
                if ($subcat) {
                    $categoryCounts[$subcat] = ($categoryCounts[$subcat] ?? 0) + 1;
                }
            }

            // Now apply Category filter for actual product listing
            $products = $baseProducts;
            if ($request->filled('category') || $request->filled('sub_category')) {
                $selectedCategories = (array) $request->input('category', []);
                $selectedSubCategories = (array) $request->input('sub_category', []);

                $allCategories = Category::all();

                $getDescendantNames = function ($catName) use (&$getDescendantNames, $allCategories) {
                    $names = [$catName];
                    $category = $allCategories->firstWhere('name', $catName);
                    if ($category) {
                        $children = $allCategories->where('parent_id', $category->id);
                        foreach ($children as $child) {
                            $names = array_merge($names, $getDescendantNames($child->name));
                        }
                    }
                    return array_unique($names);
                };

                $matchedProductIds = [];

                foreach ($selectedCategories as $catName) {
                    $mapCat = $sidebarCategoriesMap[$catName] ?? null;
                    if (!$mapCat) {
                        $categoryModel = $allCategories->firstWhere('name', $catName);
                        if ($categoryModel) {
                            $mapCat = [
                                'name' => $categoryModel->name,
                                'id' => $categoryModel->id,
                                'children' => $allCategories->where('parent_id', $categoryModel->id)
                                    ->map(fn($c) => ['name' => $c->name, 'id' => $c->id])->toArray()
                            ];
                        }
                    }

                    if ($mapCat) {
                        $descendants = $getDescendantNames($catName);
                        $allSubcatNames = array_diff($descendants, [$catName]);
                        $activeSubcats = array_intersect($selectedSubCategories, $allSubcatNames);

                        if (!empty($activeSubcats)) {
                            // Filter products to only those belonging to active subcategories and their descendants recursively
                            $allowedNames = [];
                            foreach ($activeSubcats as $subName) {
                                $allowedNames = array_merge($allowedNames, $getDescendantNames($subName));
                            }
                            $allowedNames = array_unique($allowedNames);

                            foreach ($baseProducts as $p) {
                                $prodCat = $allCategories->firstWhere('id', $p['category_id']);
                                $prodCatName = $prodCat ? $prodCat->name : null;
                                if ($prodCatName && in_array($prodCatName, $allowedNames)) {
                                    $matchedProductIds[] = $p['id'];
                                }
                            }
                        } else {
                            // Show all products of that category and its descendants recursively
                            foreach ($baseProducts as $p) {
                                $prodCat = $allCategories->firstWhere('id', $p['category_id']);
                                $prodCatName = $prodCat ? $prodCat->name : null;
                                if ($prodCatName && in_array($prodCatName, $descendants)) {
                                    $matchedProductIds[] = $p['id'];
                                }
                            }
                        }
                    }
                }

                // Handle any selected subcategories whose parents are not in selected categories
                $unparentedSubcats = [];
                foreach ($selectedSubCategories as $subName) {
                    $isParentSelected = false;
                    $subcatModel = $allCategories->firstWhere('name', $subName);
                    {
                        $curr = $subcatModel;
                        while ($curr && $curr->parent_id) {
                            $parentModel = $allCategories->firstWhere('id', $curr->parent_id);
                            if ($parentModel && in_array($parentModel->name, $selectedCategories)) {
                                $isParentSelected = true;
                                break;
                            }
                            $curr = $parentModel;
                        }
                    }
                    if (!$isParentSelected) {
                        $unparentedSubcats[] = $subName;
                    }
                }

                if (!empty($unparentedSubcats)) {
                    $allowedNames = [];
                    foreach ($unparentedSubcats as $subName) {
                        $allowedNames = array_merge($allowedNames, $getDescendantNames($subName));
                    }
                    $allowedNames = array_unique($allowedNames);

                    foreach ($baseProducts as $p) {
                        $prodCat = $allCategories->firstWhere('id', $p['category_id']);
                        $prodCatName = $prodCat ? $prodCat->name : null;
                        if ($prodCatName && in_array($prodCatName, $allowedNames)) {
                            $matchedProductIds[] = $p['id'];
                        }
                    }
                }

                $matchedProductIds = array_unique($matchedProductIds);
                $products = $baseProducts->filter(function ($p) use ($matchedProductIds) {
                    return in_array($p['id'], $matchedProductIds);
                });
            }

            $sort = $request->input('sort', 'default');
            if ($sort === 'price_asc') {
                $products = $products->sortBy('price');
            } elseif ($sort === 'price_desc') {
                $products = $products->sortByDesc('price');
            }

            // Pagination: 12 products per page
            $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $perPage = 12;
            $currentPageResults = $products->slice(($page - 1) * $perPage, $perPage)->values();
            $paginatedProducts = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageResults,
                $products->count(),
                $perPage,
                $page,
                [
                    'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                    'query' => $request->query()
                ]
            );

            // Return storefront shop view. Set searchQuery to empty string so search input field remains empty.
            return view('store.shop', [
                'products' => $paginatedProducts,
                'sidebarCategories' => $sidebarCategories,
                'categoryCounts' => $categoryCounts,
                'selectedCategories' => (array) $request->input('category', []),
                'selectedSubCategories' => (array) $request->input('sub_category', []),
                'selectedPriceMax' => $request->input('price_max', 100000),
                'selectedSort' => $sort,
                'searchQuery' => '',
            ]);
        } catch (\Exception $e) {
            Log::error('Image search error: '.$e->getMessage(), ['error' => $e]);

            return redirect()->route('store.shop')->with('error', 'Image search failed');
        }
    }
}
