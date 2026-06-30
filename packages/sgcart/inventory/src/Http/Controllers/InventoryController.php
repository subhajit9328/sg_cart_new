<?php

namespace SGCart\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use SGCart\Inventory\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /**
     * Display the Stock Levels sheet with analytics stats.
     */
    public function index(Request $request)
    {
        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        $search = $request->input('search');
        $query = Product::query();

        if ($search) {
            $query->where(fn($sq) => $sq->where('name', 'like', "%{$search}%")
                                          ->orWhere('sku', 'like', "%{$search}%"));
        }

        if ($hasVariants) {
            $query->with(['variants.color', 'variants.size']);
        }

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order') ?? $request->input('sort_dir') ?? 'desc';
        $allowedSortFields = ['name', 'sku', 'price', 'stock', 'status'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(10)->withQueryString();

        // Calculate Overview Summary Stats (Base products without active variants + Active variant items)
        $baseQuery = Product::query();
        if ($hasVariants) {
            $baseQuery->whereDoesntHave('variants', function ($q) {
                $q->where('is_active', true);
            });
        }
        
        $baseTotal = $baseQuery->count();
        $baseInStock = (clone $baseQuery)->whereColumn('stock', '>', 'min_stock')->count();
        $baseLowStock = (clone $baseQuery)->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count();
        $baseOutOfStock = (clone $baseQuery)->where('stock', '<=', 0)->count();

        $variantTotal = 0;
        $variantInStock = 0;
        $variantLowStock = 0;
        $variantOutOfStock = 0;

        if ($hasVariants) {
            $variantQuery = \SGCart\ProductVariants\Models\ProductVariant::where('is_active', true);
            $variantTotal = $variantQuery->count();
            $variantInStock = (clone $variantQuery)->whereColumn('stock', '>', 'min_stock')->count();
            $variantLowStock = (clone $variantQuery)->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count();
            $variantOutOfStock = (clone $variantQuery)->where('stock', '<=', 0)->count();
        }

        $totalItems = $baseTotal + $variantTotal;
        $inStockItems = $baseInStock + $variantInStock;
        $lowStockItems = $baseLowStock + $variantLowStock;
        $outOfStockItems = $baseOutOfStock + $variantOutOfStock;

        return view('inventory::index', compact(
            'products', 
            'hasVariants', 
            'totalItems', 
            'inStockItems', 
            'lowStockItems', 
            'outOfStockItems'
        ));
    }

    /**
     * Display a listing of inventory history log movements.
     */
    public function logs(Request $request)
    {
        $search = $request->input('search');
        $query = InventoryLog::with(['product', 'user']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pQuery) use ($search) {
                      $pQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('sku', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($uQuery) use ($search) {
                      $uQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order') ?? $request->input('sort_dir') ?? 'desc';
        $allowedSortFields = ['created_at', 'quantity', 'before_stock', 'after_stock', 'action'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest('id');
        }

        $logs = $query->paginate(10)->withQueryString();

        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        if ($hasVariants && $logs->count() > 0) {
            $logs->load('variant.color', 'variant.size');
        }

        return view('inventory::logs', compact('logs', 'hasVariants', 'search'));
    }

    /**
     * Show the manual adjustment section form.
     */
    public function adjustForm()
    {
        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        return view('inventory::adjust', compact('hasVariants'));
    }

    /**
     * Scalable API to search active products and variants.
     */
    public function searchProducts(Request $request)
    {
        $q = $request->input('q');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        
        $relations = ['category'];
        if ($hasVariants) {
            $relations[] = 'variants.color';
            $relations[] = 'variants.size';
        }

        $products = Product::where('status', 'active')
            ->where(function($query) use ($q, $hasVariants) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");
                if ($hasVariants) {
                    $query->orWhereHas('variants', function($subQuery) use ($q) {
                        $subQuery->where('is_active', true)
                                 ->where('sku', 'like', "%{$q}%");
                    });
                }
            })
            ->with($relations)
            ->take(15)
            ->get();

        $results = [];

        foreach ($products as $p) {
            $hasActiveVariants = $hasVariants && $p->variants->where('is_active', true)->count() > 0;
            
            $variantsArray = [];
            if ($hasActiveVariants) {
                foreach ($p->variants->where('is_active', true) as $v) {
                    $attrs = [];
                    if ($v->color) $attrs[] = $v->color->name;
                    if ($v->size) $attrs[] = $v->size->code;
                    $variantsArray[] = [
                        'id' => $v->id,
                        'name' => implode(' / ', $attrs) ?: 'Default Variant',
                        'sku' => $v->sku ?: ($p->sku ?: '—'),
                        'stock' => $v->stock
                    ];
                }
            }

            $results[] = [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku ?: '—',
                'image' => $p->image ? Storage::url($p->image) : null,
                'stock' => $p->stock,
                'category' => $p->category->name ?? '—',
                'has_variants' => $hasActiveVariants,
                'variants' => $variantsArray
            ];
        }

        return response()->json($results);
    }

    /**
     * JSON endpoint to fetch variants for a selected product.
     */
    public function getVariants($productId)
    {
        if (!class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            return response()->json([]);
        }

        $variants = \SGCart\ProductVariants\Models\ProductVariant::with(['color', 'size', 'product'])
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->get()
            ->map(function ($v) {
                $colorName = $v->color->name ?? '';
                $sizeCode = $v->size->code ?? '';
                $label = "SKU: " . ($v->sku ?: ($v->product->sku ?? 'N/A'));
                if ($colorName || $sizeCode) {
                    $label .= " (";
                    if ($colorName) $label .= "Color: " . $colorName;
                    if ($colorName && $sizeCode) $label .= ", ";
                    if ($sizeCode) $label .= "Size: " . $sizeCode;
                    $label .= ")";
                }
                return [
                    'id' => $v->id,
                    'label' => $label,
                    'stock' => $v->stock
                ];
            });

        return response()->json($variants);
    }

    /**
     * Process manual stock adjustments.
     */
    public function adjust(Request $request)
    {
        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class);
        $userId = Auth::id();

        // 1. Handle Bulk Adjustment
        if ($request->has('adjustments') && is_array($request->input('adjustments'))) {
            $request->validate([
                'adjustments' => 'required|array',
                'adjustments.*.product_id' => 'required|exists:products,id',
                'adjustments.*.variant_id' => 'nullable',
                'adjustments.*.qty' => 'required|integer',
                'adjustments.*.reason' => 'nullable|string|max:255',
                'reason' => 'nullable|string|max:255',
            ]);

            $globalReason = $request->input('reason');
            $adjustments = $request->input('adjustments');
            $appliedCount = 0;

            try {
                DB::transaction(function () use ($adjustments, $globalReason, $userId, $hasVariants, &$appliedCount) {
                    foreach ($adjustments as $adj) {
                        $qty = (int) $adj['qty'];
                        if ($qty === 0) {
                            continue; // Skip items with no changes
                        }

                        $productId = $adj['product_id'];
                        $variantId = !empty($adj['variant_id']) ? $adj['variant_id'] : null;
                        
                        // Select row reason or fallback to global reason
                        $rowReason = !empty($adj['reason']) ? $adj['reason'] : $globalReason;

                        if (empty($rowReason)) {
                            $product = Product::find($productId);
                            $name = $product ? $product->name : 'Unknown Item';
                            throw new \InvalidArgumentException("A reason is required for '{$name}' (specify either a product-specific reason or a global reason).");
                        }

                        $product = Product::findOrFail($productId);

                        if ($hasVariants && $variantId) {
                            $variant = \SGCart\ProductVariants\Models\ProductVariant::findOrFail($variantId);
                            $before = $variant->stock;
                            $after = $before + $qty;

                            if ($after < 0) {
                                throw new \InvalidArgumentException("Adjustment results in negative stock for variant of product '{$product->name}' (Current: {$before}, Change: {$qty}).");
                            }

                            $variant->stock = $after;
                            $variant->save();

                            // Log entry
                            InventoryLog::create([
                                'product_id' => $productId,
                                'product_variant_id' => $variantId,
                                'quantity' => $qty,
                                'action' => 'manual_adjustment',
                                'reason' => $rowReason,
                                'user_id' => $userId,
                                'before_stock' => $before,
                                'after_stock' => $after,
                            ]);

                            // Sync base product
                            $totalStock = \SGCart\ProductVariants\Models\ProductVariant::where('product_id', $productId)
                                ->where('is_active', true)
                                ->sum('stock');
                            $product->stock = $totalStock;
                            $product->save();
                        } else {
                            $before = $product->stock;
                            $after = $before + $qty;

                            if ($after < 0) {
                                throw new \InvalidArgumentException("Adjustment results in negative stock for product '{$product->name}' (Current: {$before}, Change: {$qty}).");
                            }

                            $product->stock = $after;
                            $product->save();

                            // Log entry
                            InventoryLog::create([
                                'product_id' => $productId,
                                'product_variant_id' => null,
                                'quantity' => $qty,
                                'action' => 'manual_adjustment',
                                'reason' => $rowReason,
                                'user_id' => $userId,
                                'before_stock' => $before,
                                'after_stock' => $after,
                            ]);
                        }
                        $appliedCount++;
                    }
                });
            } catch (\InvalidArgumentException $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }

            if ($appliedCount === 0) {
                return redirect()->route('admin.inventory.index')->with('info', 'No stock changes were submitted.');
            }

            return redirect()->route('admin.inventory.index')->with('success', "Successfully adjusted stock levels for {$appliedCount} items.");
        }

        // 2. Handle Single Row Modal Adjustment
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable',
            'qty' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id') ?: null;
        $qty = (int) $request->input('qty');
        $reason = $request->input('reason');

        try {
            DB::transaction(function () use ($productId, $variantId, $qty, $reason, $userId, $hasVariants) {
                $product = Product::findOrFail($productId);

                if ($hasVariants && $variantId) {
                    $variant = \SGCart\ProductVariants\Models\ProductVariant::findOrFail($variantId);
                    $before = $variant->stock;
                    $after = $before + $qty;

                    if ($after < 0) {
                        throw new \InvalidArgumentException("Adjustment results in negative stock for this variant (Current: {$before}, Change: {$qty}).");
                    }

                    $variant->stock = $after;
                    $variant->save();

                    // Log entry
                    InventoryLog::create([
                        'product_id' => $productId,
                        'product_variant_id' => $variantId,
                        'quantity' => $qty,
                        'action' => 'manual_adjustment',
                        'reason' => $reason,
                        'user_id' => $userId,
                        'before_stock' => $before,
                        'after_stock' => $after,
                    ]);

                    // Sync base product
                    $totalStock = \SGCart\ProductVariants\Models\ProductVariant::where('product_id', $productId)
                        ->where('is_active', true)
                        ->sum('stock');
                    $product->stock = $totalStock;
                    $product->save();
                } else {
                    $before = $product->stock;
                    $after = $before + $qty;

                    if ($after < 0) {
                        throw new \InvalidArgumentException("Adjustment results in negative stock for this product (Current: {$before}, Change: {$qty}).");
                    }

                    $product->stock = $after;
                    $product->save();

                    // Log entry
                    InventoryLog::create([
                        'product_id' => $productId,
                        'product_variant_id' => null,
                        'quantity' => $qty,
                        'action' => 'manual_adjustment',
                        'reason' => $reason,
                        'user_id' => $userId,
                        'before_stock' => $before,
                        'after_stock' => $after,
                    ]);
                }
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.inventory.index')->with('success', 'Stock level adjusted successfully.');
    }
}
