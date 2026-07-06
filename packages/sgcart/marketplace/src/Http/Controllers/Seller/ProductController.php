<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the seller's products.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Products automatically scoped to current seller
        $products = Product::with(['category', 'manufacturer'])
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('marketplace::seller.products.index', compact('products'));
    }

    /**
     * Show form to add a product.
     */
    public function create()
    {
        $categories = Category::all();
        $manufacturers = Manufacturer::all();
        return view('marketplace::seller.products.create', compact('categories', 'manufacturers'));
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:products,slug',
            'sku'               => 'required|string|max:100|unique:products,sku',
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|gt:0',
            'sale_price'        => 'nullable|numeric|min:0|lte:price',
            'stock'             => 'required|integer|min:0',
            'min_stock'         => 'nullable|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'product_images'    => 'nullable|array',
            'product_images.*'  => 'image|max:4096',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:1000',
            'meta_keywords'     => 'nullable|string|max:1000',
        ], [
            'sale_price.lte'    => 'Invalid Pricing: The sale price must be equal to or lower than the regular price.',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Sellers force the status to pending_approval on save
        $originalStatus = $data['status'];
        
        $product = DB::transaction(function () use ($data, $request) {
            // seller_id is automatically assigned by the saving model observer
            $product = Product::create($data);

            $defaultImageValue = $request->input('default_image');

            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');
                
                $defaultKey = null;
                if ($defaultImageValue && str_starts_with($defaultImageValue, 'new_')) {
                    $defaultKey = substr($defaultImageValue, 4);
                }
                if (!$defaultKey || !isset($files[$defaultKey])) {
                    $defaultKey = array_key_first($files);
                }

                foreach ($files as $key => $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_default' => ($key === $defaultKey)
                    ]);
                }
            }

            return $product;
        });

        $seller = auth('seller')->user();
        if ($seller && $product->status === \App\Enums\ProductStatus::PENDING_APPROVAL) {
            try {
                \App\Helpers\NotificationHelper::sendToAdmin(
                    'New Product Submitted',
                    "Seller '{$seller->shop_name}' has submitted a new product '{$product->name}' for review.",
                    route('admin.products.approvals'),
                    'product',
                    'fa-box-open'
                );
            } catch (\Exception $e) {
                \Log::error('Failed to notify admin of new product: ' . $e->getMessage());
            }
        }

        if (\Route::has('seller.products.variants.grid')) {
            return redirect()->route('seller.products.edit', [$product->ulid, 'tab' => 'variants'])
                ->with('success', 'Product has been created successfully. Now configure variants.');
        }

        if ($product->status === \App\Enums\ProductStatus::PENDING_APPROVAL) {
            $msg = 'Product has been submitted for review. It will show up on storefront once approved by admin.';
        } else {
            $msg = 'Product has been created successfully.';
        }

        // Redirect back to seller catalog
        return redirect()->route('seller.products.index')->with('success', $msg);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        if (is_numeric(request()->route('product'))) {
            return redirect()->route('seller.products.show', array_merge(
                request()->query(),
                ['product' => $product->ulid]
            ));
        }
        return view('marketplace::seller.products.show', compact('product'));
    }

    /**
     * Show product edit form.
     */
    public function edit(Product $product)
    {
        if (is_numeric(request()->route('product'))) {
            return redirect()->route('seller.products.edit', array_merge(
                request()->query(),
                ['product' => $product->ulid]
            ));
        }
        $categories = Category::all();
        $manufacturers = Manufacturer::all();
        return view('marketplace::seller.products.edit', compact('product', 'categories', 'manufacturers'));
    }

    /**
     * Update product details.
     */
    public function update(Request $request, Product $product)
    {
        $wasPending = $product->status === \App\Enums\ProductStatus::PENDING_APPROVAL;
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'sku'               => 'required|string|max:100|unique:products,sku,' . $product->id,
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|gt:0',
            'sale_price'        => 'nullable|numeric|min:0|lte:price',
            'stock'             => 'required|integer|min:0',
            'min_stock'         => 'nullable|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'product_images'    => 'nullable|array',
            'product_images.*'  => 'image|max:4096',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:1000',
            'meta_keywords'     => 'nullable|string|max:1000',
        ], [
            'sale_price.lte'    => 'Invalid Pricing: The sale price must be equal to or lower than the regular price.',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $hasVariants = class_exists(\SGCart\ProductVariants\Models\ProductVariant::class) && $product->variants()->where('is_active', true)->exists();
        if ($hasVariants) {
            unset($data['stock']);
        }

        DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);

            // Handle deletions first
            if ($request->filled('deleted_images')) {
                $deletedIds = $request->input('deleted_images');
                $imagesToDelete = ProductImage::whereIn('id', $deletedIds)
                    ->where('product_id', $product->id)
                    ->get();
                foreach ($imagesToDelete as $imgToDelete) {
                    Storage::disk('public')->delete($imgToDelete->image_path);
                    $imgToDelete->delete();
                }
            }

            $defaultImageValue = $request->input('default_image');

            // Handle new uploads
            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');
                
                $defaultKey = null;
                if ($defaultImageValue && str_starts_with($defaultImageValue, 'new_')) {
                    $defaultKey = substr($defaultImageValue, 4);
                }

                foreach ($files as $key => $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_default' => ($key === $defaultKey)
                    ]);
                }
            }

            // Sync/re-evaluate the default image flag
            $product->load('images');
            
            $targetDefaultImage = null;
            if ($defaultImageValue) {
                if (str_starts_with($defaultImageValue, 'existing_')) {
                    $id = (int) substr($defaultImageValue, 9);
                    $targetDefaultImage = $product->images->firstWhere('id', $id);
                }
            }

            if (!$targetDefaultImage) {
                $targetDefaultImage = $product->images->first();
            }

            if ($targetDefaultImage) {
                foreach ($product->images as $img) {
                    $img->update(['is_default' => ($img->id === $targetDefaultImage->id)]);
                }
            }
        });

        $seller = auth('seller')->user();
        if ($seller && !$wasPending && $product->status === \App\Enums\ProductStatus::PENDING_APPROVAL) {
            try {
                \App\Helpers\NotificationHelper::sendToAdmin(
                    'New Product Submitted',
                    "Seller '{$seller->shop_name}' has submitted product '{$product->name}' for review.",
                    route('admin.products.approvals'),
                    'product',
                    'fa-box-open'
                );
            } catch (\Exception $e) {
                \Log::error('Failed to notify admin of product update review: ' . $e->getMessage());
            }
        }

        if ($product->status === \App\Enums\ProductStatus::PENDING_APPROVAL) {
            $msg = 'Product has been updated successfully and submitted for admin review.';
        } else {
            $msg = 'Product has been updated successfully.';
        }

        return redirect()->route('seller.products.index')->with('success', $msg);
    }

    /**
     * Remove product.
     */
    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        $product->images()->delete();
        $product->delete();
        
        return redirect()->route('seller.products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Delete product image.
     */
    public function deleteImage(ProductImage $productImage)
    {
        // Scope check: Make sure product belongs to seller!
        if ($productImage->product->seller_id !== auth('seller')->id()) {
            abort(403);
        }

        // Delete file
        Storage::disk('public')->delete($productImage->image_path);
        $productImage->delete();

        return redirect()->back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Resubmit a rejected product for approval.
     */
    public function resubmit(Request $request, Product $product)
    {
        // Scope check
        if ($product->seller_id !== auth('seller')->id()) {
            abort(403);
        }

        // Only allow resubmitting inactive, draft or rejected products
        if ($product->status->value === 'active' || $product->status->value === 'pending_approval') {
            return redirect()->back()->with('error', 'Only inactive, draft or rejected products can be resubmitted.');
        }

        $request->validate([
            'seller_note' => 'required|string|max:1000',
        ]);

        $product->update([
            'status' => \App\Enums\ProductStatus::PENDING_APPROVAL,
            'rejection_reason' => null,
            'seller_note' => $request->seller_note,
        ]);

        return redirect()->route('seller.products.show', $product->ulid)
            ->with('success', 'Product has been submitted for admin review.');
    }
}
