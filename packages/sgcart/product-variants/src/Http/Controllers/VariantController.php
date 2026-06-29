<?php

namespace SGCart\ProductVariants\Http\Controllers;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Color;
use SGCart\ProductVariants\Models\Size;
use SGCart\ProductVariants\Models\ProductVariant;
use SGCart\ProductVariants\Models\ProductVariantImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VariantController extends Controller
{
    /**
     * Storefront JSON lookup for variants.
     */
    public function lookup(Request $request)
    {
        $productId = $request->query('product_id');
        $colorId = $request->query('color_id');
        $sizeId = $request->query('size_id');

        $colorEnabled = config('product-variants.features.color', true);
        $sizeEnabled = config('product-variants.features.size', true);

        $query = ProductVariant::with(['images'])
            ->where('product_id', $productId)
            ->where('is_active', true);

        if ($colorEnabled) {
            if ($colorId) {
                $query->where('color_id', $colorId);
            } else {
                $query->whereNull('color_id');
            }
        }

        if ($sizeEnabled) {
            if ($sizeId) {
                $query->where('size_id', $sizeId);
            } else {
                $query->whereNull('size_id');
            }
        }

        $variant = $query->first();

        if (!$variant) {
            // Fallback: try to find a variant matching the color only or size only if exact fails
            $variant = ProductVariant::with(['images'])
                ->where('product_id', $productId)
                ->where('is_active', true)
                ->when($colorEnabled && $colorId, fn($q) => $q->where('color_id', $colorId))
                ->when($sizeEnabled && $sizeId, fn($q) => $q->where('size_id', $sizeId))
                ->first();
        }

        if ($variant) {
            $defaultImg = $variant->images->firstWhere('is_default', true) ?: $variant->images->first();
            $imgUrl = $defaultImg ? Storage::url($delImgPath = $defaultImg->image_path) : null;

            $sellingPrice = $variant->sale_price ?: $variant->price;
            $regularPrice = $variant->sale_price ? $variant->price : null;

            return response()->json([
                'found' => true,
                'sku' => $variant->sku,
                'price' => $sellingPrice ? number_format($sellingPrice, 2, '.', '') : null,
                'old' => $regularPrice ? number_format($regularPrice, 2, '.', '') : null,
                'stock' => (int) $variant->stock,
                'img' => $imgUrl,
            ]);
        }

        return response()->json([
            'found' => false,
        ]);
    }

    /**
     * Renders the AJAX variant management grid or redirects to standard view.
     */
    public function getGrid(Request $request, $productId)
    {
        $product = \App\Models\Product::where('id', $productId)->orWhere('ulid', $productId)->firstOrFail();
        $variants = ProductVariant::with(['color', 'size', 'images'])
            ->where('product_id', $product->id)
            ->get();
            
        $colors = config('product-variants.features.color', true) ? Color::all() : collect();
        $sizes = config('product-variants.features.size', true) ? Size::all() : collect();

        if ($request->ajax()) {
            return view('product-variants::admin-product-variants-partial', compact('product', 'variants', 'colors', 'sizes'));
        }

        return redirect()->route('admin.products.edit', [$product->ulid, 'tab' => 'variants']);
    }

    /**
     * Persists variants grid configurations.
     */
    public function saveGrid(Request $request, $productId)
    {
        $product = \App\Models\Product::where('id', $productId)->orWhere('ulid', $productId)->firstOrFail();
        
        $rules = [
            'variants' => 'nullable|array',
            'variants.*.price' => 'nullable|numeric|gt:0',
            'variants.*.sale_price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ];

        if (config('product-variants.features.color', true)) {
            $rules['variants.*.color_id'] = 'nullable|exists:colors,id';
        }
        if (config('product-variants.features.size', true)) {
            $rules['variants.*.size_id'] = 'nullable|exists:sizes,id';
        }

        $request->validate($rules);

        $submittedVariants = $request->input('variants', []);

        // Custom validation to ensure variant sale_price is equal to or less than regular price (or base product price fallback)
        foreach ($submittedVariants as $index => $varData) {
            $price = !empty($varData['price']) ? (float)$varData['price'] : (float)$product->price;
            $salePrice = !empty($varData['sale_price']) ? (float)$varData['sale_price'] : null;

            if ($salePrice !== null && $salePrice > $price) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(["variants.{$index}.sale_price" => "Invalid Pricing: The variant sale price must be equal to or lower than its price (or base product price of " . number_format($price, 2) . ")."]);
            }
        }

        $submittedIds = collect($submittedVariants)->pluck('id')->filter()->toArray();

        DB::transaction(function () use ($product, $submittedVariants, $submittedIds, $request) {
            // 1. Delete variant records not submitted
            $variantsToDelete = ProductVariant::with('images')
                ->where('product_id', $product->id)
                ->whereNotIn('id', $submittedIds)
                ->get();

            foreach ($variantsToDelete as $vToDelete) {
                foreach ($vToDelete->images as $vImg) {
                    Storage::disk('public')->delete($vImg->image_path);
                }
                $vToDelete->images()->delete();
                $vToDelete->delete();
            }

            $colorEnabled = config('product-variants.features.color', true);
            $sizeEnabled = config('product-variants.features.size', true);

            // 2. Create / Update rows
            foreach ($submittedVariants as $index => $varData) {
                $variant = null;
                
                $data = [
                    'sku'        => $varData['sku'] ?: null,
                    'price'      => $varData['price'] ?: null,
                    'sale_price' => $varData['sale_price'] ?: null,
                    'stock'      => (int) ($varData['stock'] ?? 0),
                    'is_active'  => isset($varData['is_active']) ? (bool)$varData['is_active'] : false,
                ];

                if ($colorEnabled) {
                    $data['color_id'] = $varData['color_id'] ?: null;
                }
                if ($sizeEnabled) {
                    $data['size_id'] = $varData['size_id'] ?: null;
                }

                if (!empty($varData['id'])) {
                    $variant = ProductVariant::findOrFail($varData['id']);
                    $variant->update($data);
                } else {
                    $data['product_id'] = $product->id;
                    $variant = ProductVariant::create($data);
                }

                // Delete variant images submitted for removal
                if (!empty($varData['deleted_images'])) {
                    $delImages = ProductVariantImage::whereIn('id', $varData['deleted_images'])
                        ->where('product_variant_id', $variant->id)
                        ->get();
                    foreach ($delImages as $delImg) {
                        Storage::disk('public')->delete($delImg->image_path);
                        $delImg->delete();
                    }
                }

                $defaultImageValue = $varData['default_image'] ?? null;

                // Save new uploads
                if ($request->hasFile("variants.{$index}.images")) {
                    $files = $request->file("variants.{$index}.images");
                    
                    $defaultKey = null;
                    if ($defaultImageValue && str_starts_with($defaultImageValue, 'new_')) {
                        $defaultKey = substr($defaultImageValue, 4);
                    }

                    foreach ($files as $key => $file) {
                        $path = $file->store('products', 'public');
                        $variant->images()->create([
                            'image_path' => $path,
                            'is_default' => ($key === $defaultKey)
                        ]);
                    }
                }

                // Adjust default image flags
                $variant->load('images');
                $targetDefault = null;
                if ($defaultImageValue) {
                    if (str_starts_with($defaultImageValue, 'existing_')) {
                        $id = (int) substr($defaultImageValue, 9);
                        $targetDefault = $variant->images->firstWhere('id', $id);
                    } elseif (str_starts_with($defaultImageValue, 'new_')) {
                        $targetDefault = $variant->images->firstWhere('is_default', true);
                    }
                }

                if (!$targetDefault) {
                    $targetDefault = $variant->images->first();
                }

                if ($targetDefault) {
                    foreach ($variant->images as $img) {
                        $img->update(['is_default' => ($img->id === $targetDefault->id)]);
                    }
                }
            }

            // 3. Re-calculate total active variant stock
            $totalStock = ProductVariant::where('product_id', $product->id)
                ->where('is_active', true)
                ->sum('stock');
            
            $hasActiveVariants = ProductVariant::where('product_id', $product->id)->where('is_active', true)->exists();
            if ($hasActiveVariants) {
                $product->stock = $totalStock;
                $product->save();
            }
        });

        return redirect()->route('admin.products.edit', [$product->ulid, 'tab' => 'variants'])->with('success', 'Product variants updated successfully.');
    }

    /**
     * Handles quick adding attributes from AJAX request.
     */
    public function quickAddAttribute(Request $request, $productId)
    {
        $type = $request->input('type');
        
        if ($type === 'color') {
            if (!config('product-variants.features.color', true)) {
                return response()->json(['success' => false, 'message' => 'Color feature is uninstalled.'], 400);
            }
            $data = $request->validate([
                'name' => 'required|string|unique:colors,name|max:255',
                'hex_code' => 'required|string|max:10',
            ]);
            $color = Color::create($data);
            return response()->json([
                'success' => true,
                'id' => $color->id,
                'name' => $color->name,
                'extra' => $color->hex_code
            ]);
        } elseif ($type === 'size') {
            if (!config('product-variants.features.size', true)) {
                return response()->json(['success' => false, 'message' => 'Size feature is uninstalled.'], 400);
            }
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|unique:sizes,code|max:10',
            ]);
            $size = Size::create($data);
            return response()->json([
                'success' => true,
                'id' => $size->id,
                'name' => $size->name,
                'extra' => $size->code
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type.'], 400);
    }

    /**
     * Deletes individual variant image via AJAX.
     */
    public function deleteProductVariantImage(ProductVariantImage $productVariantImage)
    {
        $variantId = $productVariantImage->product_variant_id;
        $wasDefault = $productVariantImage->is_default;

        if ($productVariantImage->image_path) {
            Storage::disk('public')->delete($productVariantImage->image_path);
        }
        $productVariantImage->delete();

        if ($wasDefault) {
            $next = ProductVariantImage::where('product_variant_id', $variantId)->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json(['success' => true]);
    }
}
